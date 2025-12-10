<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades_personnels', function (Blueprint $table) {
            $table->id();

            // Relation avec personnels_centres
            $table->unsignedBigInteger('personnel_centre_id');
            $table->foreign('personnel_centre_id')
                ->references('id')
                ->on('personnels_centres')
                ->onDelete('cascade');

            // Champs spécifiques (sans liaison à la table grades)
            $table->string('filiere_fr')->nullable(); // liste déroulante de choix coté frente qui recupere tous les filiere_fr depuis la table grades
            $table->string('filiere_ar')->nullable(); // filiere_ar depuis la table grades qui correspond au filiere_fr selectionné
            $table->string('grade_fr')->nullable(); // Liste indirecte depuis la table grades qui recupere tous le grade-fr qui ont une filiere_fr = au filiere_fr selectionné
            $table->string('grade_ar')->nullable(); // grade_ar depuis la table grades qui correspond au grade_fr selectionné
            $table->string('poste_fr')->nullable(); // Liste indirecte depuis la table grades qui recupere tous le poste_fr qui ont une grade_fr = au grade_fr selectionné
            $table->string('poste_ar')->nullable(); // poste_ar depuis la table grades qui correspond au poste_fr selectionné
            $table->string('categorie')->nullable();
            $table->string('echelle')->nullable();
            $table->string('echelon')->nullable();

            // Dates et statut
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->default('Actif'); // ["Actif", "Inactif"]
            // Type de personnel si filiere_fr = Cadre de formation
            $table->string('for_cons_fr')->nullable(); // Liste côté front: ["Formateur", "Conseiller d'apprentissage"]
            $table->string('for_cons_ar')->nullable();
            // Observations
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();

            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['personnel_centre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades_personnels');
    }
};
