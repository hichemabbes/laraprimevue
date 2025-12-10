<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('num_extrait_naissance')->nullable()->unique();
            $table->string('niveau_etude_fr')->nullable();
            $table->string('niveau_etude_ar')->nullable();
            $table->string('section_etude_fr')->nullable();
            $table->string('section_etude_ar')->nullable();
            $table->string('specialite_diplome_fr')->nullable();
            $table->string('specialite_diplome_ar')->nullable();
            $table->date('date_obtention_diplome')->nullable();
            $table->string('etablissement_diplome_fr')->nullable();
            $table->string('etablissement_diplome_ar')->nullable();
            $table->string('statut_etablissement_fr')->nullable();
            $table->string('statut_etablissement_ar')->nullable();
            $table->string('num_inscription')->unique();
            $table->date('date_inscription')->nullable();
            $table->boolean('dossier_complet')->default(false);
            // Responsable
            $table->string('nom_responsable_fr')->nullable();
            $table->string('nom_responsable_ar')->nullable();
            $table->string('prenom_responsable_fr')->nullable();
            $table->string('prenom_responsable_ar')->nullable();
            $table->string('role_responsable_fr')->nullable();
            $table->string('role_responsable_ar')->nullable();
            $table->string('cin_responsable')->nullable();
            $table->string('lieu_cin_responsable_fr')->nullable();
            $table->string('lieu_cin_responsable_ar')->nullable();
            $table->date('date_cin_responsable')->nullable();
            $table->string('nationalite_responsable_fr')->default('Tunisienne');
            $table->string('nationalite_responsable_ar')->default('تونسية');
            $table->string('profession_responsable_fr')->nullable();
            $table->string('profession_responsable_ar')->nullable();
            $table->string('adresse_responsable_fr')->nullable();
            $table->string('adresse_responsable_ar')->nullable();
            $table->string('telephone_responsable')->nullable();
            $table->string('email_responsable')->nullable();
            $table->string('statut')->default('Actif'); // Liste côté front: ["Actif", "Inactif", "Transféré", "Terminé"]
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();
            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'num_inscription'], 'stagiaires_user_num_inscription_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaires');
    }
};
