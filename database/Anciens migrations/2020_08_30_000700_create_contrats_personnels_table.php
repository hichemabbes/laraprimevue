<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats_personnels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_centre_id');
            $table->foreign('personnel_centre_id')->references('id')->on('personnels_centres')->onDelete('cascade');
            $table->string('type_contrat_fr')->nullable();
            $table->string('type_contrat_ar')->nullable();
            $table->string('num_contrat')->unique();
            $table->string('num_decision')->nullable();
            $table->date('date_decision')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->default('Actif'); // Liste côté front: ["Actif", "Terminé", "Résilié"]
            $table->integer('heures_travail')->nullable();
            $table->integer('heures_enseignement_max')->nullable(); // Pour administratifs
            $table->date('date_resiliation')->nullable();
            $table->text('motif_resiliation')->nullable();
            $table->mediumText('document_path')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();
            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->index('personnel_centre_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats_personnels');
    }
};
