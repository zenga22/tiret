<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Storage;
use Mail;
use Log;

use App\Cloud;
use App\Rule;
use App\User;

class AssignFiles extends Command
{
    protected $signature = 'assignfiles';
    protected $description = '';
    protected $keep_duplicates = true;

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

        foreach($files as $file) {
            try {
                if (substr($file, 0, 1) == '.')
                    continue;

                Log::info('Manipolo file ' . $file);

                foreach($rules as $rule) {
                    $target = $rule->apply($file);
                    if ($target != false) {
                        list($folder, $filename) = $target;
                        $filepath = $storagePath . $file;

                        if (Cloud::testExistance($folder . '/' . $filename)) {
                            Log::info('File ' . $file . ' già caricato, salto');

                            if ($this->keep_duplicates)
                                rename($filepath, sys_get_temp_dir() . '/' . $filename);
                            else
                                $disk->delete($file);

                            break;
                        }

                        Cloud::loadFile($filepath, $folder, $filename);
                        Log::info('Caricato ' . $file . ' in ' . $folder);

                        if(env('SEND_MAIL', false) == true) {
                            $user = User::where('username', '=', $folder)->first();
                            if ($user != null) {
                                foreach($user->emails as $e) {
                                    Mail::send('emails.notify', ['text' => $user->group->mailtext], function ($m) use ($user, $filepath, $e) {
                                        $m->to($e, $user->name . ' ' . $user->surname)->subject('nuovo documento disponibile');
                                        $m->attach($filepath);
                                    });

                                    Log::info('Inviata mail a ' . $user->name . ' ' . $user->surname . ' ' . $e);
                                }
                            }
                            else {
                                $user = new User();
                                $user->username = $folder;
                                $user->save();
                                Log::info('Creato nuovo utente ' . $user->username);
                            }
                        }

                        $disk->delete($file);
                        break;
                    }
                }
            }
            catch(\Exception $e) {
                Log::error('Errore con file ' . $file . ': ' . $e->getMessage());
            }

            usleep(500000);
        }
    }
}
