<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnels_centres', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('matricule')->nullable()->unique();
            $table->string('cin')->nullable()->unique();
            $table->date('date_cin')->nullable();
            $table->string('lieu_cin_fr')->nullable();
            $table->string('lieu_cin_ar')->nullable();
            $table->string('etat_civil_fr')->nullable();
            $table->string('etat_civil_ar')->nullable();

            //fin service
            $table->date('date_fin_service')->nullable();
            $table->Text('cause_inactivite_fr')->nullable();
            $table->Text('cause_inactivite_ar')->nullable();
            // Observations
            $table->text('observation_fr')->nullable();
            $table->text('observation_ar')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['matricule', 'cin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnels_centres');
    }
};
