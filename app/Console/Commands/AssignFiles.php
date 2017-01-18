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
        $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
        $files = $disk->files('/');
        $rules = Rule::get();

        $sent_counter = 0;
        $notfound_counter = 0;
        $overwrite_counter = 0;
        $errors_counter = 0;

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
                        $filepath = $storagePath . $file;

                        $test = Cloud::testExistance($folder . '/' . $filename);
                        if ($test !== false) {
                            Tlog::write('files', 'File ' . $test . ' già caricato, sovrascrivo');

                            if ($this->dry_run == false) {
                                Cloud::deleteFile($folder, basename($test));
                                Cloud::loadFile($filepath, $folder, $filename);
                                $overwrite_counter++;
                            }

                            if(env('SEND_MAIL', false) == true) {
                                Mail::send('emails.notify', ['text' => $user->group->updatemailtext], function ($m) use ($user, $filepath, $filename) {
                                    $m->to($user->email, $user->name . ' ' . $user->surname)->subject('nuovo documento disponibile: ' . $filename);
                                    if (empty($user->email2) == false)
                                        $m->cc($user->email2);
                                    if (empty($user->email3) == false)
                                        $m->cc($user->email3);
                                    $m->attach($filepath);
                                });
                            }
                        }
                        else {
                            if ($this->dry_run == false)
                                Cloud::loadFile($filepath, $folder, $filename);

                            if(env('SEND_MAIL', false) == true) {
                                $user = User::where('username', '=', $folder)->first();

                                if ($user != null) {
                                    if ($this->dry_run == false) {
                                        $filesize = filesize($filepath);

                                        /*
                                            Attenzione: SES ha un limite di 10MB
                                            per gli allegati. In tal caso si
                                            manda una mail di notifica senza il
                                            file allegato
                                        */
                                        if ($filesize > 1024 * 1024 * 10) {
                                            Mail::send('emails.notify', ['text' => $user->group->lightmailtext], function ($m) use ($user, $filepath, $filename) {
                                                $m->to($user->email, $user->name . ' ' . $user->surname)->subject('nuovo documento disponibile: ' . $filename);
                                                if (empty($user->email2) == false)
                                                    $m->cc($user->email2);
                                                if (empty($user->email3) == false)
                                                    $m->cc($user->email3);
                                            });
                                        }
                                        else {
                                            Mail::send('emails.notify', ['text' => $user->group->mailtext], function ($m) use ($user, $filepath, $filename) {
                                                $m->to($user->email, $user->name . ' ' . $user->surname)->subject('nuovo documento disponibile: ' . $filename);
                                                if (empty($user->email2) == false)
                                                    $m->cc($user->email2);
                                                if (empty($user->email3) == false)
                                                    $m->cc($user->email3);
                                                $m->attach($filepath);
                                            });
                                        }

                                        $sent_counter++;
                                    }

                                    Tlog::write('files', 'Mail inviata a ' . join(', ', $user->emails));
                                }
                                else {
                                    if ($this->dry_run == false) {
                                        $user = new User();
                                        $user->name = '???';
                                        $user->surname = '???';
                                        $user->username = $folder;
                                        $user->save();

                                        $notfound_counter++;
                                    }

                                    Tlog::write('files', 'Creato nuovo utente ' . $user->username . ', necessario popolare l\'anagrafica e notificare account');
                                }
                            }
                        }

                        if ($this->dry_run == false)
                            $disk->delete($file);

                        Tlog::write('files', 'Caricato ' . $file . ' in ' . $folder);

                        break;
                    }
                }

                if ($disk->exists($file))
                    Tlog::write('files', 'File ' . $file . ' non gestito');
            }
            catch(\Exception $e) {
                Tlog::write('files', 'Errore nella manipolazione del file ' . $file . ': ' . $e->getMessage());
                $errors_counter++;
            }

            if ($this->dry_run == false)
                usleep(1000000);
        }

        if (!empty(env('ADMIN_NOTIFY_MAIL', '')) && $this->dry_run == false) {
            if ($sent_counter != 0 || $notfound_counter != 0 || $overwrite_counter != 0 || $errors_counter != 0) {
                Mail::send('emails.admin_files_notify', ['sent' => $sent_counter, 'notfound' => $notfound_counter, 'overwrite' => $overwrite_counter, 'errors' => $errors_counter], function ($m) {
                    $m->to(env('ADMIN_NOTIFY_MAIL'))->subject('aggiornamento assegnazione files');
                });
            }
        }
    }
}
