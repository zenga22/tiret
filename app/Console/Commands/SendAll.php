<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Mail;
use Log;
use DB;

use App\User;
use App\Cloud;

class SendAll extends Command
{
    protected $signature = 'send {users} {message}';
    protected $description = "Invia una mail agli utenti desiderati con tutti i loro files in allegato. Prevede l'esistenza di un file con l'elenco delle mail utente coinvolte, ed un altro col testo della mail di accompagnamento";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $users_file = $this->argument('users');
        $text_file = $this->argument('message');

        if (file_exists($users_file) == false) {
            echo "Impossibile aprire il file con le mail degli utenti coinvolti.\n";
            exit();
        }

        if (file_exists($text_file) == false) {
            echo "Impossibile aprire il file con il testo della mail di accompagnamento.\n";
            exit();
        }

        $text = file_get_contents($text_file);
        $mails = file($users_file);

        foreach($mails as $m) {
            $m = strtolower(trim($m));
            if (empty($m))
                continue;

            $user = User::where(DB::raw('LOWER(email)'), '=', $m)->first();

            if ($user != null) {
                $files = Cloud::getContents($user->username);
                $local_files = [];

                foreach($files as $f) {
                    $filename = basename($f);
                    $path = sprintf('/tmp/%s', $filename);
                    $data = Cloud::readFile($user->username, $filename);
                    file_put_contents($path, $data);
                    $local_files[] = $path;
                }

                if (empty($local_files)) {
                    echo "Nessun file da spedire a " . $m . "\n";
                    continue;
                }

                foreach($user->emails as $e) {
                    try {
                        Mail::send('emails.empty', ['text' => $user->group->mailtext], function ($m) use ($user, $local_files, $e) {
                            $m->to($e, $user->name . ' ' . $user->surname)->subject('nuovo documento disponibile');

                            foreach($local_files as $filepath)
                                $m->attach($filepath);
                        });

                        echo $m . " - " . $e . ": OK\n";
                    }
                    catch (\Exception $ex) {
                        echo $m . " - " . $e . ": FAILED\n";
                    }
                }

                foreach($local_files as $f)
                    unlink($f);
            }
            else {
                echo "Utente non trovato per mail " . $m . "\n";
            }

            usleep(500000);
        }
    }
}

