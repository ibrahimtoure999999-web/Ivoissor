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
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('demande_id')->unique()->constrained('demandes')->cascadeOnDelete();
            $table->dateTime('date_heure');
            $table->string('lieu', 150);
            $table->string('statut', 20)->default('PLANIFIE');
            $table->foreignUuid('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('date_heure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
