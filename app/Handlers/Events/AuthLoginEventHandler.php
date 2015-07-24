<?php namespace App\Handlers\Events;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use App\User;

class AuthLoginEventHandler {

    public function __construct()
    {
        //
    }

    public function handle(User $user, $remember)
    {
        $user->lastlogin = date('Y-m-d');
        $user->save();
    }

}