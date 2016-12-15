<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Hash;
use App\User;

class ResetPassword extends Command
{
    protected $signature = 'password {identifier} {password}';
    protected $description = 'Resetta password utente';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $identifier = $this->argument('identifier');
        $password = $this->argument('password');

        $u = User::where('username', '=', $identifier)->first();
        if ($u == null) {
            $u = User::where('email', '=', $identifier)->first();
            if ($u == null) {
                echo "Utente non trovato\n";
                exit();
            }
        }

        $u->password = Hash::make($password);
        $u->save();
        echo 'Modificata password utente '.$u->id."\n";
    }
}
