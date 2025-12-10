<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('apprentis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('centre_id')->constrained()->onDelete('restrict');
            $table->string('matricule')->unique();
            $table->string('nom_fr')->nullable();
            $table->string('prenom_fr')->nullable();
            $table->string('nom_ar')->nullable();
            $table->string('prenom_ar')->nullable();
            $table->string('cin')->unique()->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance_fr')->nullable();
            $table->string('lieu_naissance_ar')->nullable();
            $table->string('nationalite')->default('Tunisienne');
            $table->string('sexe')->nullable();
            $table->string('adresse_fr')->nullable();
            $table->string('adresse_ar')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('situation_professionnelle')->nullable();
            $table->string('entreprise_actuelle')->nullable();
            $table->string('tuteur_entreprise')->nullable();
            $table->string('telephone_tuteur')->nullable();
            $table->date('date_debut_formation')->nullable();
            $table->date('date_fin_formation')->nullable();
            $table->string('contrat_apprentissage_numero')->nullable();
            $table->date('contrat_apprentissage_date')->nullable();
            $table->string('specialite_formation')->nullable();
            $table->string('statut')->default('Actif');
            $table->text('observations')->nullable();
            $table->string('photo')->nullable(); // Champ ajouté pour la photo
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('apprentis');
    }
};
