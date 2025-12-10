<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->nullable(); // ex: AUTOCAD, REVIT-STRUCT, ANGLAIS-B2
            $table->string('nom_fr')->nullable();
            $table->string('nom_ar')->nullable();

            $table->string('certificat_fr')->nullable();
            $table->string('certificat_ar')->nullable();

            $table->json('criteres_admission_fr')->nullable();
            $table->json('criteres_admission_ar')->nullable();

            $table->longText('description_fr')->nullable();
            $table->longText('description_ar')->nullable();

            $table->enum('statut', ['Actif', 'Inactif'])->default('Actif');
            $table->unsignedSmallInteger('ordre')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('themes');
    }
};
