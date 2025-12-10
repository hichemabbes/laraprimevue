<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table espaces_centres
        Schema::create('espaces_centres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centre_id')->constrained('centres')->onDelete('cascade');
            $table->string('type_espace_fr')->nullable();
            $table->string('type_espace_ar')->nullable();

            $table->string('nom_fr')->nullable();
            $table->string('nom_ar')->nullable();
            $table->string('code')->nullable();

            $table->string('bloc_fr')->nullable();
            $table->string('bloc_ar')->nullable();
            $table->string('etage_fr')->nullable();
            $table->string('etage_ar')->nullable();
            $table->string('localisation_fr')->nullable();
            $table->string('localisation_ar')->nullable();

            $table->unsignedInteger('capacite')->default(0);
            $table->decimal('longueur', 10, 2)->nullable();
            $table->decimal('largeur', 10, 2)->nullable();
            $table->decimal('hauteur', 10, 2)->nullable();
            $table->decimal('surface', 10, 2)->nullable();
            $table->decimal('hsp', 10, 2)->nullable();

            $table->string('etat_murs_fr')->nullable();
            $table->string('etat_murs_ar')->nullable();

            $table->string('etat_plafond_fr')->nullable();
            $table->string('etat_plafond_ar')->nullable();

            $table->string('etat_sol_fr')->nullable();
            $table->string('etat_sol_ar')->nullable();

            $table->string('etat_portes_fr')->nullable();
            $table->string('etat_portes_ar')->nullable();

            $table->string('etat_fenetres_fr')->nullable();
            $table->string('etat_fenetres_ar')->nullable();

            $table->boolean('reseau_informatique_existant')->default(false);
            $table->string('qualite_reseau_fr')->nullable();
            $table->string('qualite_reseau_ar')->nullable();

            $table->unsignedInteger('prises_reseau')->default(0);
            $table->unsignedInteger('prises_rj45')->default(0);
            $table->boolean('wifi_disponible')->default(false);
            $table->boolean('couverture_wifi')->default(false);
            $table->boolean('serveur_local')->default(false);

            $table->string('etat_eclairage_fr')->nullable();
            $table->string('etat_eclairage_ar')->nullable();
            $table->unsignedInteger('nombre_points_lumineux')->default(0);
            $table->string('type_ampoules_fr')->nullable();
            $table->string('type_ampoules_ar')->nullable();
            $table->boolean('eclairage_urgence')->default(false);
            $table->boolean('detecteur_mouvement')->default(false);

            $table->boolean('climatisation')->default(false);
            $table->string('type_climatisation_fr')->nullable();
            $table->string('type_climatisation_ar')->nullable();
            $table->unsignedInteger('nombre_climatiseurs')->default(0);
            $table->boolean('ventilation_mecanique')->default(false);
            $table->string('etat_climatisation_fr')->nullable();
            $table->string('etat_climatisation_ar')->nullable();

            $table->boolean('acces_handicape')->default(false);
            $table->unsignedInteger('nombre_sorties_secours')->default(0);
            $table->boolean('detecteur_fumee')->default(false);
            $table->boolean('extincteur')->default(false);
            $table->boolean('alarme_incendie')->default(false);
            $table->boolean('camera_surveillance')->default(false);

            $table->date('date_derniere_maintenance')->nullable();
            $table->date('date_prochaine_maintenance')->nullable();
            $table->text('notes_maintenance')->nullable();

            $table->text('observation')->nullable();
            $table->mediumText('albums_photos')->nullable();
            $table->json('documents')->nullable();

            $table->string('priorite')->nullable(); // 'Immédiat', 'Urgent', 'En temps voulu', 'Planifié', 'Optionnel', 'Non requis'

            $table->softDeletes();
            $table->timestamps();

            $table->index(['centre_id']);
            $table->index(['code']);
        });

        // Table affectations_espaces
        Schema::create('affectations_espaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('espace_id')->constrained('espaces_centres')->onDelete('cascade');
            $table->foreignId('user_centre_id')->constrained('users_centres')->onDelete('cascade');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            $table->string('type_affectation_fr')->nullable();
            $table->string('type_affectation_ar')->nullable();
            $table->string('statut')->nullable();
            $table->text('observation')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['espace_id', 'user_centre_id']);
            $table->index(['date_debut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations_espaces');
        Schema::dropIfExists('espaces_centres');
    }
};
