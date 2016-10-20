<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Cloud;
use App\Group;

class MassiveRemove extends Command
{
    protected $signature = 'remove {groupname} {pattern}';
    protected $description = 'Rimuove tutti i files dato un dato pattern';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $groupname = $this->argument('groupname');
        if ($groupname == '*')
            $groups = Group::all();
        else
            $groups = Group::where('name', '=', $groupname)->get();

        $pattern = $this->argument('pattern');

        foreach($groups as $group) {
            foreach($group->users as $user) {
                $files = Cloud::getContents($user->username, false);
                foreach($files as $file) {
                    $filename = basename($file);
                    if (preg_match($pattern, $filename)) {
                        Cloud::deleteFile($user->username, $filename);
                        echo "Rimosso $file\n";
                    }
                }
            }
        }
    }
}
