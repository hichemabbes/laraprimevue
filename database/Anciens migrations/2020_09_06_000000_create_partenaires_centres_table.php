<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table partenaires_centres
        Schema::create('partenaires_centres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('centre_id')->constrained('centres')->onDelete('cascade');

            $table->json('specialites')->nullable(); // [{ "specialite_id": x, "capacite": y }, ...]

            $table->string('type_partenaire')->nullable();
            $table->integer('capacite_accueil_totale')->default(0)->nullable();

            $table->string('statut')->nullable();

            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            $table->text('observation')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['entreprise_id', 'centre_id']);
        });

        // Table conventions
        Schema::create('conventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('centre_id')->constrained('centres')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('reference', 50)->nullable();
            $table->date('date_signature')->nullable();
            $table->date('date_debut_validite')->nullable();
            $table->date('date_fin_validite')->nullable();
            $table->boolean('renouvelable')->default(false);

            $table->string('statut')->nullable();

            $table->mediumText('fichier')->nullable();
            $table->text('observation')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['entreprise_id', 'centre_id']);
        });

        // Table convention_specialites
        Schema::create('convention_specialites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convention_id')->constrained('conventions')->onDelete('cascade');
            $table->foreignId('specialite_id')->constrained('specialites')->onDelete('cascade');

            $table->integer('nombre_stagiaires')->default(0);
            $table->decimal('montant_bourse', 10, 2)->nullable();
            $table->integer('jours_conge')->default(30);

            $table->string('statut')->nullable();

            $table->text('observation')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['convention_id', 'specialite_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('convention_specialites');
        Schema::dropIfExists('conventions');
        Schema::dropIfExists('partenaires_centres');
    }
};
