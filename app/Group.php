<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public function users() {
        return $this->hasMany('App\User')->orderBy('surname', 'asc');
    }

    public function deliverDocument($filepaths, $filenames, $update) {
        foreach($this->users as $user) {
            $user->deliverDocument($filepaths, $filenames, $update);
            usleep(100000);
        }
    }
}
