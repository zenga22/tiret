<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMlogsTable extends Migration
{
    public function up()
    {
        Schema::create('mlogs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('filename');
            $table->enum('status', ['try', 'sent', 'reschedule', 'fail']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('mlogs');
    }
}
