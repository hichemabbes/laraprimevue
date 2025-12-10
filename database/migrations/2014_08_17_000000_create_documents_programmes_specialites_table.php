<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table programmes (spécialités)
        Schema::create('programmes_specialites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('specialite_id')
                ->nullable()
                ->constrained('specialites')
                ->onDelete('restrict');

            $table->string('version', 100)->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();

            $table->string('statut')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['specialite_id', 'version'], 'uniq_specialite_version');
            $table->index('specialite_id', 'idx_programme_formation');
        });

        // Table modules (spécialités)
        Schema::create('modules_specialites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('programme_specialite_id')
                ->nullable()
                ->constrained('programmes_specialites')
                ->onDelete('cascade');

            $table->string('code', 100)->nullable();
            $table->longText('titre_module_fr')->nullable();
            $table->longText('titre_module_ar')->nullable();

            $table->string('type_module_fr')->nullable();
            $table->string('type_module_ar')->nullable();

            $table->integer('heures_theoriques')->nullable();
            $table->integer('heures_pratiques')->nullable();
            $table->integer('heures_evaluation')->nullable();

            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();

            $table->string('statut')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['programme_specialite_id', 'code'], 'idx_module_specialite_code');
        });

        // Table documents des programmes (spécialités)
        Schema::create('documents_programmes_specialites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('programme_specialite_id')
                ->constrained('programmes_specialites')
                ->onDelete('cascade');

            $table->string('titre_fr')->nullable();
            $table->string('titre_ar')->nullable();

            // Type de document (liste récupérée depuis table "listes" côté logique)
            $table->string('type_document_fr')->nullable();  // 'Types Documents Spécialités'
            $table->string('type_document_ar')->nullable();  // mapping 'عنوان المرجع'

            // ✅ Contenu base64 potentiellement lourd → LONGTEXT
            $table->longText('fichier')->nullable();

            $table->string('statut')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('programme_specialite_id', 'idx_doc_programme');
        });

        // Table documents des modules (spécialités)
        Schema::create('documents_modules_specialites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('module_specialite_id')
                ->constrained('modules_specialites')
                ->onDelete('cascade');

            $table->string('titre_fr')->nullable();
            $table->string('titre_ar')->nullable();

            // Type de document (liste côté logique)
            $table->string('type_document_fr')->nullable();  // 'Types Documents Modules'
            $table->string('type_document_ar')->nullable();  // 'أنواع وثائق الوحدات'

            // ✅ Ici aussi LONGTEXT pour le base64
            $table->longText('fichier')->nullable();

            $table->string('statut')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('module_specialite_id', 'idx_doc_module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_modules_specialites');
        Schema::dropIfExists('documents_programmes_specialites');
        Schema::dropIfExists('modules_specialites');
        Schema::dropIfExists('programmes_specialites');
    }
};
