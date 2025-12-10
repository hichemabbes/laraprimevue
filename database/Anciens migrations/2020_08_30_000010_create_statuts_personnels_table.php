<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statuts_personnels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_centre_id');
            $table->foreign('personnel_centre_id')->references('id')->on('personnels_centres')->onDelete('cascade');
            // Qualité
            $table->string('qualite_fr')->nullable(); // Liste côté front: ["Personnel (ATFP)", "Personnel (Externe)"]
            $table->string('qualite_ar')->nullable(); // par mapping : عون الوكالة، عون من خارج الوكالة

            //champ en cas ou  la qualite_fr = Personnel (ATFP)
            $table->date('date_recrutement')->nullable();



            // État du personnel
            $table->string('etat_personnel_fr')->nullable(); // Liste côté front: ["Titulaire", "Contractuel", "Détaché", "Autre"]
            $table->string('etat_personnel_ar')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();



            //champ en cas ou  la qualite_fr = Personnel (Externe)
            $table->string('etablissement_origine_fr')->nullable();
            $table->string('etablissement_origine_ar')->nullable();
            $table->text('mission_fr')->nullable();
            $table->text('mission_ar')->nullable();



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
        Schema::dropIfExists('statuts_personnels');
    }
};
