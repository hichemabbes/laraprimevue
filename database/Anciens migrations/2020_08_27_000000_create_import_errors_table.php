<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('import_errors', function (Blueprint $table) {
            $table->id();
            $table->string('table_name'); // Nom de la table concernée
            $table->json('row_data'); // Données de la ligne non importée
            $table->string('error_message'); // Message d'erreur
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_errors');
    }
};
