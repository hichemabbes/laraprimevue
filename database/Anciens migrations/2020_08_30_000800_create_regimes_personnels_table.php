<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regimes_personnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annee_formation_id')->nullable()->constrained('annees_formations')->onDelete('cascade');
            $table->foreignId('annee_administrative_id')->nullable()->constrained('annees_administratives')->onDelete('cascade');
            $table->unsignedBigInteger('personnel_centre_id');
            $table->foreign('personnel_centre_id')->references('id')->on('personnels_centres')->onDelete('cascade');
            $table->integer('regime_horaire')->default(40);
            $table->integer('rabattement')->default(0);
            $table->integer('heures_enseignement_max')->nullable(); // Pour administratifs
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();
            $table->string('statut')->default('Actif'); // Liste côté front: ["Actif", "Inactif"]
            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['annee_formation_id', 'personnel_centre_id'], 'uniq_regimes_formation_personnel');
            $table->unique(['annee_administrative_id', 'personnel_centre_id'], 'uniq_regimes_admin_personnel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regimes_personnels');
    }
};
