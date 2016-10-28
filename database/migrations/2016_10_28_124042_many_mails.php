<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManyMails extends Migration
{
        public function up()
        {
                Schema::table('users', function(Blueprint $table)
                {
                        $table->string('email2');
                        $table->string('email3');
                });
        }

        public function down()
        {
                Schema::table('users', function(Blueprint $table)
                {
                        $table->dropColumn(['email2']);
                        $table->dropColumn(['email3']);
                });
        }
}
