<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoreCustomTexts extends Migration
{
    public function up()
    {
        Schema::table('groups', function(Blueprint $table)
        {
            $table->text('lightmailtext');
            $table->text('updatedmailtext');
        });
    }

    public function down()
    {
        Schema::table('groups', function(Blueprint $table)
        {
            $table->dropColumn(['lightmailtext']);
            $table->dropColumn(['updatedmailtext']);
        });
    }
}
