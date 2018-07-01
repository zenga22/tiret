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
    protected $description = "Invia una mail agli utenti desiderati. Prevede l'esistenza di un file con l'elenco delle mail utente coinvolte, ed un altro col testo della mail di accompagnamento";

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
        $managed = [];

        foreach($mails as $m) {
            $m = strtolower(trim($m));
            if (empty($m))
                continue;

            $user = User::where(DB::raw('LOWER(email)'), '=', $m)->first();

            if ($user != null) {
                if (in_array($user->id, $managed)) {
                    continue;
                }

                $managed[] = $user->id;

                try {
                    Mail::send('emails.empty', ['text' => $text], function ($m) use ($user, $e) {
                        $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $m->to($user->email, $user->name . ' ' . $user->surname);
                        $m->subject('nuovo documento disponibile');

                        if (empty($user->email2) == false)
                            $m->cc($user->email2);
                        if (empty($user->email3) == false)
                            $m->cc($user->email3);
                    });

                    echo $m . " - " . $e . ": OK\n";
                }
                catch (\Exception $ex) {
                    echo $m . " - " . $e . ": FAILED\n";
                }
            }
            else {
                echo "Utente non trovato per mail " . $m . "\n";
            }

            usleep(500000);
        }
    }
}
