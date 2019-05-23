<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Mlog;
use App\User;

class MarkAll extends Command
{
    protected $signature = 'mark {filelist} {since} {newstatus}';
    protected $description = 'Per modificare gli stati massivamente';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filelist = $this->argument('filelist');
        if (file_exists($filelist) == false) {
            echo "Il file $filelist non esiste\n";
            exit();
        }

        $since = $this->argument('since');
        $newstatus = $this->argument('newstatus');

        $emails = file($filelist, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $users = User::whereIn('email', $emails)->orWhereIn('email2', $emails)->orWhereIn('email3', $emails)->get()->pluck('id')->toArray();
        Mlog::whereIn('user_id', $users)->where('status', 'try')->where('created_at', '>', $since)->update(['status' => $newstatus]);
    }
}
