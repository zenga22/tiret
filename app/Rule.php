<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    public function apply($filename)
    {
        $test = preg_match($this->expression, $filename, $matches);
        if ($test == 0)
            return false;

        $ret = array();
        $ret[] = $matches['folder'];

        if (array_key_exists('filename', $matches))
            $ret[] = $matches['filename'];
        else
            $ret[] = $filename;

        return $ret;
    }
}
