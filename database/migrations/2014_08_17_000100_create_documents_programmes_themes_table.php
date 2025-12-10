<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table programmes d'études (thèmes)
        Schema::create('programmes_themes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('theme_id')
                ->nullable()
                ->constrained('themes')
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

            $table->unique(['theme_id', 'version'], 'uniq_theme_version');
            $table->index('theme_id', 'idx_programme_theme');
        });

        // Table modules (thèmes)
        Schema::create('modules_themes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('programme_theme_id')
                ->nullable()
                ->constrained('programmes_themes')
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

            $table->index(['programme_theme_id', 'code'], 'idx_module_theme_code');
        });

        // Table documents des programmes (thèmes)
        Schema::create('documents_programmes_themes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('programme_theme_id')
                ->constrained('programmes_themes')
                ->onDelete('cascade');

            $table->string('titre_fr')->nullable();
            $table->string('titre_ar')->nullable();

            $table->string('type_document_fr')->nullable();  // 'Types Documents Thèmes'
            $table->string('type_document_ar')->nullable();  // mapping

            // ✅ Base64 → LONGTEXT
            $table->longText('fichier')->nullable();

            $table->string('statut')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('programme_theme_id', 'idx_doc_programme');
        });

        // Table documents des modules (thèmes)
        Schema::create('documents_modules_themes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('module_theme_id')
                ->constrained('modules_themes')
                ->onDelete('cascade');

            $table->string('titre_fr')->nullable();
            $table->string('titre_ar')->nullable();

            $table->string('type_document_fr')->nullable();  // 'Types Documents Modules'
            $table->string('type_document_ar')->nullable();  // 'أنواع وثائق الوحدات'

            // ✅ Base64 → LONGTEXT
            $table->longText('fichier')->nullable();

            $table->string('statut')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('module_theme_id', 'idx_doc_module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_modules_themes');
        Schema::dropIfExists('documents_programmes_themes');
        Schema::dropIfExists('modules_themes');
        Schema::dropIfExists('programmes_themes');
    }
};
