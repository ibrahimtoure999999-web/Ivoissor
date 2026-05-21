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
        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('demande_id')->constrained('demandes')->restrictOnDelete();
            $table->string('reference_transaction', 100)->unique();
            $table->decimal('montant', 10, 2);
            $table->string('devise', 3)->default('XOF');
            $table->string('statut', 20)->default('EN_ATTENTE');
            $table->string('moyen_paiement', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
