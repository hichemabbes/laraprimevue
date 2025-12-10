<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centre', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Identité du centre
            $table->string('nom_fr');
            $table->string('nom_ar')->nullable();
            $table->string('abreviation')->nullable(); // ex: CFI, ISET...
            $table->string('logo')->nullable(); // chemin ou URL du logo

            // Informations légales
            $table->string('matricule_fiscal')->nullable();
            $table->string('registre_commerce')->nullable();
            $table->string('forme_juridique')->nullable(); // SARL, SA, etc.
            $table->date('date_creation')->nullable();

            // Adresse et localisation
            $table->longText('adresse_fr')->nullable();
            $table->longText('adresse_ar')->nullable();
            $table->string('gouvernorat_fr')->nullable();
            $table->string('gouvernorat_ar')->nullable();
            $table->string('delegation_fr')->nullable();
            $table->string('delegation_ar')->nullable();
            $table->string('code_postal', 10)->nullable();

            // Contact
            $table->string('telephone_1')->nullable();
            $table->string('telephone_2')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();

            // Responsable
            $table->string('responsable_nom')->nullable();
            $table->string('responsable_prenom')->nullable();
            $table->string('responsable_fonction')->nullable();
            $table->string('responsable_tel')->nullable();
            $table->string('responsable_email')->nullable();

            // Divers
            $table->longText('observation_fr')->nullable();
            $table->longText('observation_ar')->nullable();
            $table->string('statut')->default('Actif'); // ['Actif', 'Inactif']

            // Relation (par exemple l’utilisateur qui a ajouté le centre)
            $table->unsignedBigInteger('ajouter_par')->nullable();
            $table->foreign('ajouter_par')->references('id')->on('users')->onDelete('set null');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centre');
    }
};
