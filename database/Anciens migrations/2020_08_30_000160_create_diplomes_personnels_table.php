<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diplomes_personnels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_centre_id');
            $table->foreign('personnel_centre_id')->references('id')->on('personnels_centres')->onDelete('cascade');
            $table->string('niveau_etude_fr')->nullable(); //liste depuis table  listes 'nom_fr' => 'Niveaux Etudes Personnels')
            $table->string('niveau_etude_ar')->nullable(); // nom_ar => مستوى تعليم الموظف depuis table listes qui correspond au nom_fr selectionné
            $table->string('diplome_fr')->nullable();
            $table->string('diplome_ar')->nullable();
            $table->date('date_optention')->nullable();
            $table->string('specialite_diplome_fr')->nullable();
            $table->string('specialite_diplome_ar')->nullable();
            $table->string('code_niveau')->nullable();
            $table->string('statut')->default('Actif'); // Liste côté front: ["Actif", "Inactif"]
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();
            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->index('personnel_centre_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diplomes_personnels');
    }
};
