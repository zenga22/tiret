<?php

namespace App\Console\Commands;

use Log;
use Illuminate\Console\Command;
use Storage;
use App\Rule;

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

                    if (Cloud::testExistance($folder)) {
                        Cloud::loadFile($folder, $filename);
                        $disk->delete($file);
                        Log::info('Caricato in ' . $folder);
                    }
                }
            }
        }
    }
}
