<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\AssignFiles::class,
        \App\Console\Commands\MassiveRemove::class
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('assignfiles')
                 ->everyMinute();
    }
}
