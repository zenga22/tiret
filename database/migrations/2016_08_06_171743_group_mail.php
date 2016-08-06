<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GroupMail extends Migration
{
    public function up()
    {
        Schema::table('groups', function(Blueprint $table)
        {
            $table->text('mailtext');
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn(['mailtext']);
        });
    }
}
