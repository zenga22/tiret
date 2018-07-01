<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailTextsTable extends Migration
{
    public function up()
    {
        Schema::create('mail_texts', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('fallback')->default(false);
            $table->string('rule');
            $table->string('subject');
            $table->text('light');
            $table->text('plain');
            $table->text('update');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('mail_texts');
    }
}
