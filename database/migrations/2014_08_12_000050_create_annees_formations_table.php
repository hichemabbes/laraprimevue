<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table des années de formation
        Schema::create('annees_formations', function (Blueprint $table) {
            $table->id();
            $table->string('intitule')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->nullable();
            $table->text('observation')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['date_debut', 'date_fin']);
        });

        // Table des repos de formation
        Schema::create('repos_formations', function (Blueprint $table) {
            $table->id();

            // Relation inchangée avec l'année de formation
            $table->foreignId('annee_formation_id')->constrained('annees_formations')->onDelete('cascade');

            // Type de repos : en clair, sans relation à la table `listes`
            $table->string('type_repos_formation_fr')->nullable(); // ex: "Repos hebdomadaire"
            $table->string('type_repos_formation_ar')->nullable(); // sera rempli par mapping

            // Détails du repos
            $table->date('date_debut')->nullable();
            $table->integer('nombre_jour')->nullable();
            $table->date('date_fin')->nullable();
            $table->text('description')->nullable();

            // Métadonnées
            $table->string('statut')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Index utiles
            $table->index(['annee_formation_id'], 'idx_repos_formation_annee');
            $table->index(['type_repos_formation_fr'], 'idx_repos_formation_type_fr');
            $table->index(['date_debut', 'date_fin'], 'idx_repos_formation_periode');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repos_formations');
        Schema::dropIfExists('annees_formations');
    }
};
