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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->string('type_document', 100);
            $table->string('chemin_fichier', 255);
            $table->json('donnees_ocr')->nullable();
            $table->string('statut_validation', 30)->default('PENDING');
            $table->timestamps();

            $table->index('demande_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
