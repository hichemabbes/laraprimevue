<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();

            // Année de formation (table existante)
            $table->foreignId('annee_formation_id')
                ->constrained('annees_formations')
                ->onDelete('restrict');

            // Identité de la session
            $table->string('code', 50)->nullable();      // ex: AUTOCAD-AVR-2025, TGC-2025-A
            $table->string('intitule_fr')->nullable();   // "Session Avril 2025"
            $table->string('intitule_ar')->nullable();

            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            $table->integer('capacite_min')->nullable();
            $table->integer('capacite_max')->nullable();

            $table->enum('statut', [
                'Planifiee',
                'Ouverte_inscription',
                'En_cours',
                'Terminee',
                'Annulee',
            ])->default('Planifiee');

            $table->longText('observation_fr')->nullable();
            $table->longText('observation_ar')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['annee_formation_id'], 'idx_session');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};
