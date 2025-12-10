<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();

            // Session choisie pour cette formation (si c'est ta logique)
            $table->foreignId('session_id')
                ->nullable()
                ->constrained('sessions')
                ->onDelete('cascade');

            // Spécialité (peut être null pour les formations purement "thème")
            $table->foreignId('specialite_id')
                ->nullable()
                ->constrained('specialites')
                ->onDelete('cascade');

            // Thème (toujours présent)
            $table->foreignId('theme_id')
                ->constrained('themes')
                ->onDelete('cascade');

            $table->string('code', 20)->nullable();
            $table->string('nom_fr')->nullable();
            $table->string('nom_ar')->nullable();

            // Cible (public visé)
            $table->string('cible_fr')->nullable(); // public ou personnels d'une entreprise en cas de formation continue
            $table->string('cible_ar')->nullable();

            $table->string('statut')->nullable();
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable(); // cohérence bilingue

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
