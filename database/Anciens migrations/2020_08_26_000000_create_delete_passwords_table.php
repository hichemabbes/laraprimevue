<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeletePasswordsTable extends Migration
{
    public function up()
    {
        Schema::create('delete_passwords', function (Blueprint $table) {
            $table->id();
            $table->string('password'); // Stocke le mot de passe haché
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delete_passwords');
    }
}
