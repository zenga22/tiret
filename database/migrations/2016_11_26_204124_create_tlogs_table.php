<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTlogsTable extends Migration
{
    public function up()
    {
        Schema::create('tlogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('section');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('tlogs');
    }
}
