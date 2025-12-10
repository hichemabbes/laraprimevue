<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table grades : Stocke les filières, grades et postes disponibles
        Schema::create('grades', function (Blueprint $table) {
            $table->id(); // Clé primaire auto-incrémentée
            $table->string('filiere_fr'); // Filière en français (ex. : Ouvriers)
            $table->string('filiere_ar'); // Filière en arabe (ex. : العملة)
            $table->string('grade_fr'); // Grade en français (ex. : Ouvrier)
            $table->string('grade_ar'); // Grade en arabe (ex. : عامل)
            $table->string('poste_fr'); // Poste en français (ex. : Agent de services)
            $table->string('poste_ar'); // Poste en arabe (ex. : عون خدمات)
            $table->timestamps(); // Horodatages created_at et updated_at
            $table->softDeletes(); // Horodatage de suppression logique
            $table->unique(['filiere_fr', 'grade_fr', 'poste_fr'], 'uniq_grades_filiere_grade_poste_fr'); // Unicité sur filière, grade et poste en français
            $table->unique(['filiere_ar', 'grade_ar', 'poste_ar'], 'uniq_grades_filiere_grade_poste_ar'); // Unicité sur filière, grade et poste en arabe
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
