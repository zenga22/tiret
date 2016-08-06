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
            if (substr($file, 0, 1) == '.')
                continue;

            Log::info('Manipolo file ' . $file);

            foreach($rules as $rule) {
                $target = $rule->apply($file);
                if ($target != false) {
                    list($folder, $filename) = $target;
                    $filepath = $storagePath . $file;
                    Cloud::loadFile($filepath, $folder, $filename);

                    if(env('SEND_MAIL', false) == true) {
                        $user = User::where('username', '=', $folder)->first();
                        if ($user != null) {
                            Mail::send('emails.notify', ['text' => $user->group->mailtext], function ($m) use ($user, $filepath) {
                                $m->to($user->email, $user->name . ' ' . $user->surname)->subject('nuovo documento disponibile');
                                $m->attach($filepath);
                            });

                            Log::info('Inviata mail a ' . $user->name . ' ' . $user->surname);
                        }
                    }

                    $disk->delete($file);
                    Log::info('Caricato in ' . $folder);
                }
            }

            usleep(500000);
        }
    }
}
