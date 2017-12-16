<?php namespace App\Handlers\Events;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\Mlog;

class AuthLoginEventHandler {

    public function __construct()
    {
        //
    }

    public function handle($event)
    {
        Mlog::registerMessageId($event->message->getId());
    }
}
