<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Log;

class Tlog extends Model
{
    static public function write($section, $message)
    {
        Log::info($message);

        $t = new Tlog();
        $t->section = $section;
        $t->message = $message;
        $t->save();
    }
}
