<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('folder');
            $table->string('filename');
            $table->boolean('downloaded');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('documents');
    }
}
