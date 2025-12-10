<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('centre_id')->nullable();
            $table->string('log_name', 255)->nullable()->index();
            $table->text('description');
            $table->string('subject_type', 255)->nullable()->index();
            $table->string('event', 255)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->string('causer_type', 255)->nullable()->index();
            $table->unsignedBigInteger('causer_id')->nullable()->index();
            $table->longText('properties')->nullable()->collation('utf8mb4_bin');
            $table->char('batch_uuid', 36)->nullable();
            $table->timestamps();

            // Index supplémentaires si nécessaire
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
