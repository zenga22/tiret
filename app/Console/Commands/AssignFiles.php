<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Storage;
use Mail;
use Event;
use Log;

use App\Cloud;
use App\Rule;
use App\User;
use App\Tlog;
use App\Events\FileToHandle;

class AssignFiles extends Command
{
    protected $signature = 'assignfiles';
    protected $description = '';

    /*
        Se true, eventuali files duplicati sono spostati in /tmp anziché essere
        rimossi
    */
    protected $keep_duplicates = true;

    /*
        Se true, nessun file viene realmente assegnato e nessuna mail spedita.
        Utile per debuggare il comportamento dello script
    */
    protected $dry_run = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $disk = Storage::disk('local');
        $storagePath = Cloud::mainLocalFolder();
        $files = $disk->files('/');
        $rules = Rule::get();

        $sent_counter = 0;
        $notfound_counter = 0;
        $overwrite_counter = 0;
        $errors_counter = 0;
        $info = [];

        /*
            Raggruppo i files per destinatario...
        */

        foreach($files as $file) {
            try {
                if (substr($file, 0, 1) == '.')
                    continue;

                Log::info('Manipolo file ' . $file);

                Event::fire(new FileToHandle($file));
                if ($disk->exists($file) == false)
                    continue;

                foreach($rules as $rule) {
                    $target = $rule->apply($file);
                    if ($target != false) {
                        list($folder, $filename) = $target;

                        if (!isset($info[$folder]))
                            $info[$folder] = [];

                        $info[$folder][] = (object) [
                            'filename' => $filename,
                            'file' => $file
                        ];
                    }
                }
            }
            catch(\Exception $e) {
                Tlog::write('files', 'Errore nella manipolazione del file ' . $file . ': ' . $e->getMessage());
                $errors_counter++;
            }
        }

        foreach($info as $folder => $files) {
            /*
                ... li salvo su S3...
            */

            $overwriting = false;
            $remove_all = false;
            $filepaths = [];
            $filenames = [];

            try {
                foreach($files as $f) {
                    $filename = $f->filename;
                    $file = $f->file;

                    $filepath = $storagePath . $file;

                    $filepaths[] = $filepath;
                    $filenames[] = $filename;

                    $test = Cloud::testExistance($folder . '/' . $filename);
                    if ($test !== false) {
                        Tlog::write('files', 'File ' . $test . ' già caricato, sovrascrivo');

                        if ($this->dry_run == false) {
                            Cloud::deleteFile($folder, basename($test));
                            $overwrite_counter++;
                        }

                        $overwriting = true;
                    }

                    if ($this->dry_run == false) {
                        Cloud::loadFile($filepath, $folder, $filename);
                    }

                    Tlog::write('files', 'Caricato ' . $file . ' in ' . $folder);
                }
            }
            catch(\Exception $e) {
                Tlog::write('files', 'Errore in caricamento files: ' . $e->getMessage());
            }

            /*
                ... e invio mail di notifica agli utenti
            */

            if(env('SEND_MAIL', false) == true) {
                try {
                    if (!empty($filepaths)) {
                        $user = User::where('username', '=', $folder)->first();

                        if ($user != null) {
                            if ($user->group != null) {
                                if ($this->dry_run == false) {
                                    $user->deliverDocument($filepaths, $filenames, $overwriting);
                                    $remove_all = true;
                                    $sent_counter += count($filepaths);
                                    usleep(1000000);
                                }

                                Tlog::write('files', 'Mail inviata a ' . join(', ', $user->emails));
                            }
                            else {
                                Tlog::write('files', 'Utente ' . $user->username . ' esistente ma anagrafica non popolata');
                            }
                        }
                        else {
                            if ($this->dry_run == false) {
                                $user = new User();
                                $user->name = '???';
                                $user->surname = '???';
                                $user->username = $folder;
                                $user->group_id = 0;
                                $user->save();

                                $notfound_counter++;
                            }

                            Tlog::write('files', 'Creato nuovo utente ' . $user->username . ', necessario popolare l\'anagrafica e notificare account');
                        }
                    }
                }
                catch(\Exception $e) {
                    Tlog::write('files', 'Errore in invio mail di notifica: ' . $e->getMessage());
                }
            }

            if ($remove_all) {
                foreach($files as $f) {
                    $disk->delete($f->file);
                }
            }
        }

        if (!empty(env('ADMIN_NOTIFY_MAIL', '')) && $this->dry_run == false) {
            if ($sent_counter != 0 || $notfound_counter != 0 || $overwrite_counter != 0 || $errors_counter != 0) {
                Mail::send('emails.admin_files_notify', ['sent' => $sent_counter, 'notfound' => $notfound_counter, 'overwrite' => $overwrite_counter, 'errors' => $errors_counter], function ($m) {
                    $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $m->to(env('ADMIN_NOTIFY_MAIL'))->subject('aggiornamento assegnazione files');
                });
            }
        }
    }
}
