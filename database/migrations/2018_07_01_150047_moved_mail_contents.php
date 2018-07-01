<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MovedMailContents extends Migration
{
    public function up()
    {
        Schema::table('groups', function(Blueprint $table)
        {
            $table->dropColumn(['mailtext']);
            $table->dropColumn(['lightmailtext']);
            $table->dropColumn(['updatedmailtext']);
            $table->text('signature');
        });
    }

    public function down()
    {
        //
    }
}
