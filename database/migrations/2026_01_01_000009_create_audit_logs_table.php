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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 255);
            $table->timestamps();
            
            $table->index('created_at');
        });
    }

    /**
     * la migration est inversée, ce qui supprime la table "audit_logs" de la base de données.
     * Cela est utile pour revenir en arrière si nécessaire, par exemple lors de tests ou de déploiements.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
