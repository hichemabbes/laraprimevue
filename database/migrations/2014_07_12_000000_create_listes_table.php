<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('listes', function (Blueprint $table) {
            $table->id();

            // Noms localisés
            $table->string('nom_fr')->nullable();
            $table->string('nom_ar')->nullable();

            // Contenu JSON des options associées à cette liste
            // Structure suggérée :
            // [
            //   {"valeur": "A", "nom_fr": "Option A", "nom_ar": "الخيار أ"},
            //   {"valeur": "B", "nom_fr": "Option B", "nom_ar": "الخيار ب"}
            // ]
            $table->json('options')->nullable();

            // Apparence visuelle
            $table->string('icone')->nullable();             // Nom d’icône (ex: "fa-user")
            $table->string('couleur', 7)->nullable();         // HEX code ex: "#ffffff"
            $table->string('fond', 7)->nullable();            // HEX code ex: "#000000"

            // Ordre d’affichage et statut
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->enum('statut', ['Actif', 'Inactif'])->default('Actif');

            $table->string('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index utiles
            $table->index('statut');
            $table->index('ordre');
        });
    }

    public function down()
    {
        Schema::dropIfExists('listes');
    }
};
