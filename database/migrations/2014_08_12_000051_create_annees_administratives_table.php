<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table des années administratives
        Schema::create('annees_administratives', function (Blueprint $table) {
            $table->id();
            $table->string('intitule')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->nullable();
            $table->text('observation')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['date_debut', 'date_fin'], 'idx_annee_admin_periode');
        });

        // Table des repos administratifs

        Schema::create('repos_administratifs', function (Blueprint $table) {
            $table->id();

            // Relation avec l'année administrative
            $table->foreignId('annee_administrative_id')->constrained('annees_administratives')->onDelete('cascade');

            // Type de repos administratif (sans clé étrangère)
            $table->string('type_repos_administratif_fr')->nullable();
            $table->string('type_repos_administratif_ar')->nullable(); // mapping ultérieur

            // Détails du repos
            $table->date('date_debut')->nullable();
            $table->integer('nombre_jour')->nullable();
            $table->date('date_fin')->nullable();
            $table->text('description')->nullable();

            // Métadonnées
            $table->string('statut')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);

            $table->softDeletes();
            $table->timestamps();

            // Index
            $table->index(['annee_administrative_id'], 'idx_repos_admin_annee');
            $table->index(['type_repos_administratif_fr'], 'idx_repos_admin_type_fr');
            $table->index(['date_debut', 'date_fin'], 'idx_repos_admin_periode');
        });

    }

    public function down()
    {
        Schema::dropIfExists('repos_administratifs');
        Schema::dropIfExists('annees_administratives');
    }
};
