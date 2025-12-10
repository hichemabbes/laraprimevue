<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table directeurs des centres
        Schema::create('directeurs_centres', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Relation avec l'utilisateur (directeur)
            $table->unsignedBigInteger('user_id')->nullable()->unique();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Relation avec le centre
            $table->unsignedBigInteger('centre_id');
            $table->foreign('centre_id')->references('id')->on('centres')->onDelete('cascade');

            // Type de nomination
            $table->string('type_nomination_fr')->nullable(); // Liste côté front: "Nomination officielle", "Affectation temporaire"
            $table->string('type_nomination_ar')->nullable(); // Mapping: "تعيين رسمي", "تكليف وقتي"

            // Dates de nomination
            $table->date('date_debut_nomination')->nullable();
            $table->date('date_fin_nomination')->nullable();

            // Type d'affectation
            $table->string('type_affectation_fr')->nullable(); // Liste côté front: "Centre principal", "Centre par intérim"
            $table->string('type_affectation_ar')->nullable(); // Mapping: "المركز الأصلي", "مركز بالنيابة"

            // Contrat d'objectif (fichier PDF ou image)
            $table->mediumText('contrat_objectif')->nullable();

            // Statut du directeur
            $table->string('statut')->nullable(); // Ex: "Actif", "Inactif"

            // Observations
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();

            // Timestamps et soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('user_id', 'idx_directeurs_user');
            $table->index('centre_id', 'idx_directeurs_centre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directeurs_centres');
    }
};
