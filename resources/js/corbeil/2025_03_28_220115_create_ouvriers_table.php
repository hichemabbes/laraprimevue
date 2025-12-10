<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ouvriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('matricule')->unique();
            $table->string('nom_fr')->nullable();
            $table->string('prenom_fr')->nullable();
            $table->string('nom_ar')->nullable();
            $table->string('prenom_ar')->nullable();
            $table->string('cin')->unique()->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance_fr')->nullable();
            $table->string('lieu_naissance_ar')->nullable();
            $table->string('nationalite')->default('Tunisienne');
            $table->string('sexe')->nullable();

            $table->string('adresse_fr')->nullable();
            $table->string('adresse_ar')->nullable();
            $table->string('gouvernorat_fr')->nullable();
            $table->string('gouvernorat_ar')->nullable();
            $table->string('delegation_fr')->nullable();
            $table->string('delegation_ar')->nullable();
            $table->string('tele1')->nullable();
            $table->string('tele2')->nullable();
            $table->string('email')->nullable();

            $table->string('niveau_etude')->nullable();
            $table->string('specialite')->nullable();
            $table->string('fonction_principal')->nullable(); // Directeur, Formateur, Administratif, etc.
            $table->string('charge_par')->nullable();
            $table->string('type')->default('Titulaire'); // Titulaire, Contractuel
            $table->date('date_embauche')->nullable();
            $table->string('type_contrat')->nullable();
            $table->string('num_contrat')->nullable();
            $table->string('num_autorisation_contrat')->nullable();
            $table->date('date_debut_contrat')->nullable();
            $table->date('date_fin_contrat')->nullable();
            $table->integer('regime_horaire')->nullable(); // 40 heures par semaine par defaut
            $table->string('salaire_base')->nullable();
            $table->string('grade')->nullable();
            $table->string('categorie')->nullable();
            $table->string('echelle')->nullable();
            $table->string('echllon')->nullable();
            $table->string('banque')->nullable();
            $table->string('numero_compte')->nullable();
            $table->string('etat_civil')->nullable();

            $table->string('statut')->default('Actif'); // Actif, En congé, Démisionnaire, Licencié
            $table->text('observations')->nullable();

            $table->string('photo')->nullable(); // Champ ajouté pour la photo
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ouvriers');
    }
};
