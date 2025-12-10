<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // un chef filiere est un personnel qui a un filiere_fr = Cadre de formation
        Schema::create('chefs_filieres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_centre_id');
            $table->foreign('personnel_centre_id')->references('id')->on('personnels_centres')->onDelete('cascade');
            $table->foreignId('secteur_id')->constrained('secteurs')->onDelete('restrict');
            $table->foreignId('annee_formation_id')->constrained('annees_formations')->onDelete('restrict');
            $table->string('statut')->default('Actif'); // Liste côté front: ["Actif", "Inactif"]
            $table->integer('heures_administratives')->default(8);
            $table->integer('heures_enseignement')->nullable();
            $table->text('observation')->nullable();
            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['personnel_centre_id', 'annee_formation_id'], 'chefs_filieres_personnel_annee_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chefs_filieres');
    }
};
