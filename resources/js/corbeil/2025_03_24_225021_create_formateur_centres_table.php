<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Changez le nom de la classe pour correspondre au nom de fichier
class CreateFormateurCentresTable extends Migration
{
    public function up()
    {
        Schema::create('formateur_centre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formateur_id')->constrained()->onDelete('cascade');
            $table->foreignId('centre_id')->constrained()->onDelete('cascade');
            $table->integer('heures_enseignees')->nullable();
            $table->boolean('is_principal')->default(false);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->timestamps();

            // Unicité pour garantir un seul centre principal actif par formateur
            $table->unique(['formateur_id', 'is_principal'], 'formateur_principal_unique')
                ->where('is_principal', true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('formateur_centre');
    }
}
