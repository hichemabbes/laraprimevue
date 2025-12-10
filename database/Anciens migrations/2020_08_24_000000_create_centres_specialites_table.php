<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialites_centres', function (Blueprint $table) {
            $table->id();

            $table->foreignId('specialite_id')->constrained('specialites')->onDelete('cascade');
            $table->foreignId('centre_id')->constrained('centres')->onDelete('cascade');

            // Métadonnées
            $table->string('statut')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->longText('observation_fr')->nullable();
            $table->longText('observation_ar')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Contraintes et index
            $table->unique(['specialite_id', 'centre_id']);
            $table->index('specialite_id');
            $table->index('centre_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialites_centres');
    }
};
