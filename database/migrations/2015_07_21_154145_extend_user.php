<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendUser extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->string('surname');
            $table->string('username')->unique();
            $table->date('lastlogin');
            $table->boolean('suspended');
            $table->integer('group_id');
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn(['surname', 'username', 'group_id']);
        });
    }
}
