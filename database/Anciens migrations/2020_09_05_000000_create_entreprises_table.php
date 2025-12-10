<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table entreprises
        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();

            $table->foreignId('secteur_id')->nullable()->constrained('secteurs')->onDelete('set null');
            $table->foreignId('sous_secteur_id')->nullable()->constrained('sous_secteurs')->onDelete('set null');

            $table->string('code', 50)->nullable();
            $table->string('nom_fr', 100)->nullable();
            $table->string('nom_ar', 100)->nullable();
            $table->string('adresse_fr')->nullable();
            $table->string('adresse_ar')->nullable();

            $table->string('gouvernorat_ar')->nullable();
            $table->string('delegation_fr')->nullable();
            $table->string('delegation_ar')->nullable();

            $table->string('activite_principale_fr')->nullable();
            $table->string('activite_principale_ar')->nullable();
            $table->string('activite_secondaire_fr')->nullable();
            $table->string('activite_secondaire_ar')->nullable();

            $table->string('org_securite_sociale')->nullable();
            $table->string('numero_affiliation', 50)->nullable();
            $table->string('matricule_fiscal', 50)->nullable();
            $table->string('code_tva', 50)->nullable();
            $table->string('code_categorie', 50)->nullable();
            $table->string('num_etablissement_secondaire', 50)->nullable();
            $table->string('numero_patent', 50)->nullable();

            $table->string('tele1', 20)->nullable();
            $table->string('tele2', 20)->nullable();
            $table->string('fax1', 20)->nullable();
            $table->string('fax2', 20)->nullable();
            $table->string('email', 100)->nullable();

            // Chef entreprise
            $table->string('nom_chef_entr_fr')->nullable();
            $table->string('prenom_chef_entr_fr')->nullable();
            $table->string('nom_chef_entr_ar')->nullable();
            $table->string('prenom_chef_entr_ar')->nullable();
            $table->string('profession_chef_entr_fr')->nullable();
            $table->string('profession_chef_entr_ar')->nullable();
            $table->string('adresse_personnel_chef_entr_fr')->nullable();
            $table->string('adresse_personnel_chef_entr_ar')->nullable();
            $table->string('cin_chef_entr', 20)->nullable();
            $table->date('date_cin_chef_entr')->nullable();
            $table->string('lieu_cin_chef_entr_fr')->nullable();
            $table->string('lieu_cin_chef_entr_ar')->nullable();
            $table->string('tel_chef_entr', 20)->nullable();
            $table->string('email_chef_entr', 100)->nullable();

            // Représentant
            $table->string('nom_representant_entr_fr')->nullable();
            $table->string('prenom_representant_entr_fr')->nullable();
            $table->string('nom_representant_entr_ar')->nullable();
            $table->string('prenom_representant_entr_ar')->nullable();
            $table->string('fonction_representant_entr_fr')->nullable();
            $table->string('fonction_representant_entr_ar')->nullable();
            $table->string('adresse_personnel_representant_entr_fr')->nullable();
            $table->string('adresse_personnel_representant_entr_ar')->nullable();
            $table->string('cin_representant_entr', 20)->nullable();
            $table->date('date_cin_representant_entr')->nullable();
            $table->string('lieu_cin_representant_entr_fr')->nullable();
            $table->string('lieu_cin_representant_entr_ar')->nullable();
            $table->string('tel_representant_entr', 20)->nullable();
            $table->string('email_representant_entr', 100)->nullable();

            $table->string('statut')->nullable();
            $table->text('observation')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        // Table tuteurs
        Schema::create('tuteurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');

            $table->string('nom_fr')->nullable();
            $table->string('prenom_fr')->nullable();
            $table->string('nom_ar')->nullable();
            $table->string('prenom_ar')->nullable();
            $table->string('fonction_fr')->nullable();
            $table->string('fonction_ar')->nullable();
            $table->string('adresse_personnel_fr')->nullable();
            $table->string('adresse_personnel_ar')->nullable();
            $table->string('cin', 20)->nullable();
            $table->date('date_cin')->nullable();
            $table->string('lieu_cin_fr')->nullable();
            $table->string('lieu_cin_ar')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('email', 100)->nullable();

            $table->string('statut')->nullable();
            $table->text('observation')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        // Table tuteurs_entreprises
        Schema::create('tuteurs_entreprises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tuteur_id')->constrained('tuteurs')->onDelete('cascade');
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');

            $table->string('statut')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->text('observation')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['tuteur_id', 'entreprise_id']);
        });

        // Table entreprises_centres
        Schema::create('entreprises_centres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('centre_id')->constrained('centres')->onDelete('cascade');

            $table->string('statut')->nullable();
            $table->text('observation')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['entreprise_id', 'centre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entreprises_centres');
        Schema::dropIfExists('tuteurs_entreprises');
        Schema::dropIfExists('tuteurs');
        Schema::dropIfExists('entreprises');
    }
};
