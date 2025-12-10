<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // les formateurs sont des personnels chargé a la formation
        Schema::create('formateurs_specialites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_centre_id');
            $table->foreign('personnel_centre_id')->references('id')->on('personnels_centres')->onDelete('cascade');
            $table->foreignId('specialite_id')->constrained('specialites')->onDelete('restrict');
            $table->string('code_secteur')->nullable();
            $table->string('code_sous_secteur')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->default('Actif'); // Liste côté front: ["Actif", "Inactif"]
            $table->string('correspondance_specialite_fr')->nullable();
            $table->string('correspondance_specialite_ar')->nullable();
            $table->text('observation')->nullable();
            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['personnel_centre_id', 'specialite_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formateurs_specialites');
    }
};
