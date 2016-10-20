<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GroupMessage extends Migration
{
    public function up()
    {
        Schema::table('groups', function(Blueprint $table)
        {
            $table->text('message');
        });
    }

    public function down()
    {
        Schema::table('groups', function(Blueprint $table)
        {
            $table->dropColumn(['message']);
        });
    }
}
