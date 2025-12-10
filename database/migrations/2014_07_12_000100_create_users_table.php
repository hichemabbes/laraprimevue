<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Identité commune
            $table->string('nom_fr')->nullable();
            $table->string('prenom_fr')->nullable();
            $table->string('nom_ar')->nullable();
            $table->string('prenom_ar')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance_fr')->nullable();
            $table->string('lieu_naissance_ar')->nullable();
            $table->string('nationalite_fr')->default('Tunisienne');
            $table->string('nationalite_ar')->default('تونسية');
            // Genre et statut
            $table->string('genre_fr')->nullable(); // Liste côté front: ['Homme', 'Femme', 'Autre']
            $table->string('genre_ar')->nullable();
            $table->string('statut')->default('Actif'); // Liste côté front: ['Actif', 'Inactif']
            // Contact
            $table->longText('adresse_fr')->nullable();
            $table->longText('adresse_ar')->nullable();
            $table->string('gouvernorat_fr')->nullable(); // Gouvernorat en français (depuis listes 'nom_fr' => 'Gouvernorats')
            $table->string('gouvernorat_ar')->nullable(); // Gouvernorat en arabe (depuis listes 'nom_ar' => 'الولايات')
            $table->string('delegation_fr')->nullable();
            $table->string('delegation_ar')->nullable();
            $table->string('telephone_1')->nullable();
            $table->string('telephone_2')->nullable();
            // Divers
            $table->longText('observation_fr')->nullable();
            $table->longText('observation_ar')->nullable();
            $table->mediumText('photo')->nullable();
            $table->unsignedBigInteger('ajouter_par')->nullable();
            $table->foreign('ajouter_par')->references('id')->on('users')->onDelete('set null');
            // Authentification
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            // Type utilisateur
            $table->string('type_user_fr')->nullable(); // Liste côté front: ['Parent, Personnel '', 'Stagiaire', ]
            $table->string('type_user_ar')->nullable(); // par mapping: ['عون ', '', 'متربص', 'ولي']

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
