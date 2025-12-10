<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_centres', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('centre_id')->constrained('centres')->onDelete('cascade');
            // Statut du centre
            $table->string('centre_statut_fr'); // Liste côté front: ["Centre officiel", "Centre secondaire"]
            $table->string('centre_statut_ar');
            // Type de mouvement
            $table->string('type_mouvement_fr')->nullable(); // Liste côté front: ["Affectation initiale", "Transfert", "Détachement"]
            $table->string('type_mouvement_ar')->nullable();
            // Dates et statut
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->default('Actif'); // Liste côté front: ["Actif", "Inactif", "Transféré"]
            // Observations
            $table->longText('observation_fr')->nullable();
            $table->longText('observation_ar')->nullable();
            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            // Unicité: un personnel ne peut avoir qu'un seul centre officiel à la fois
            $table->unique(['user_id', 'centre_statut_fr', 'date_debut'], 'user_centre_unique_officiel');
            $table->unique(['user_id', 'centre_id', 'date_debut'], 'user_centre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_centres');
    }
};
