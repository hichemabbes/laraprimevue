<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStagiairesTable extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('centre_id');
            $table->string('matricule')->nullable();
            $table->string('prenom')->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('cin')->nullable();
            $table->string('formation')->nullable();
            $table->string('niveau_etude')->nullable();
            $table->string('statut')->nullable();
            $table->date('date_inscription')->nullable();
            $table->date('date_fin_formation')->nullable();
            $table->string('email_personnel')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('centre_id')->references('id')->on('centres')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaires');
    }
}
