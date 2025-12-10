<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formateurs_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formateur_specialite_id')->constrained('formateurs_specialites')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('restrict');
            $table->string('statut')->default('Actif'); // Liste côté front: ["Actif", "Inactif"]
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->text('observation')->nullable();
            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['formateur_specialite_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formateurs_modules');
    }
};
