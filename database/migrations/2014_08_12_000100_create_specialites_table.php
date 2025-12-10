<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('specialites', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->nullable();  // ex: GEN-CIV, INFO, RESEAU
            $table->string('nom_fr')->nullable();
            $table->string('nom_ar')->nullable();

            // Niveau d'entrée demandé pour cette spécialité
            $table->string('niveau_fr')->nullable(); // Niveau demandé pour accéder à cette formation Ex: Bac ...
            $table->string('niveau_ar')->nullable();

            $table->string('diplome_fr')->nullable();
            $table->string('diplome_ar')->nullable();

            // un seul chiffre avec un seul décimal après la virgule (ex: 1.5, 2.5, 3.0)
            $table->decimal('duree_formation', 3, 1)->nullable();

            // Volumes horaires globaux de la spécialité (pour maquette officielle)
            $table->integer('heures_et')->nullable();
            $table->integer('heures_eg')->nullable();
            $table->integer('heures_total')->nullable();

            // Homologation
            $table->boolean('est_homologue')->default(false);          // true = homologuée
            $table->string('reference_homologation')->nullable();      // numéro / référence officielle

            // Critères d'admission
            $table->json('criteres_admission_fr')->nullable(); // stocker comme une liste : critère 1, critère 2, etc.
            $table->json('criteres_admission_ar')->nullable();

            $table->longText('description_fr')->nullable();
            $table->longText('description_ar')->nullable();

            $table->enum('statut', ['Actif', 'Inactif'])->default('Actif');
            $table->unsignedSmallInteger('ordre')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('specialites');
    }
};
