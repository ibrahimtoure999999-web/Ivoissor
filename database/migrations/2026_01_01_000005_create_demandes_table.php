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
        Schema::create('demandes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('citoyen_id')->nullable()->constrained('citoyens')->nullOnDelete();
            $table->string('type_demande', 50);
            $table->string('statut', 20)->default('BROUILLON');
            $table->text('motif_rejet')->nullable();
            $table->timestamps();
            
            $table->index('statut');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
