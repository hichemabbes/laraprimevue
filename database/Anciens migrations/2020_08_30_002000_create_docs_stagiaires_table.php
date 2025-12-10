<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docs_stagiaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('stagiaire_id')->constrained('stagiaires')->onDelete('cascade');
            $table->string('type_doc_fr')->nullable();
            $table->string('type_doc_ar')->nullable();
            $table->mediumText('document_path')->nullable();
            $table->date('date_depot')->nullable();
            $table->string('validite_doc_fr')->nullable(); // Liste côté front: ["Valide", "Non Valide"]
            $table->string('validite_doc_ar')->nullable();
            $table->string('type_depot_fr')->nullable(); // Liste côté front: ["Bureau d'ordre", "Par Poste", "Par mail", "Par fax"]
            $table->string('type_depot_ar')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();
            $table->string('statut')->default('Actif'); // Liste côté front: ["Actif", "Inactif"]
            // Tracé des mises à jour
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->index('stagiaire_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docs_stagiaires');
    }
};
