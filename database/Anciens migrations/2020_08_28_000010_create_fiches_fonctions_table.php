<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiches_fonctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->mediumText('fichier_fr')->nullable(); // Chemin du fichier en français (image ou PDF)
            $table->mediumText('fichier_ar')->nullable(); // Chemin du fichier en arabe (image ou PDF)
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->nullable(); // Ex: "actif", "inactif", "en_attente"
            $table->longText('description_fr')->nullable();
            $table->longText('description_ar')->nullable();
            $table->longText('observation_fr')->nullable();
            $table->longText('observation_ar')->nullable();

            // Clé étrangère vers la table roles
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            // Index pour les performances
            $table->index('role_id', 'fiches_fonctions_role_id_index');

            // Timestamps et soft deletes pour cohérence
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiches_fonctions');
    }
};
