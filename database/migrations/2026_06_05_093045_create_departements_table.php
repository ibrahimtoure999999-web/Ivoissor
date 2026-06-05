<?php

declare(strict_types=1);

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
        Schema::create('departements', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Clé étrangère connectée à la table 'regions'
            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            // Indique la table parente ciblée par ce lien
            // Bloque la suppression de la région si un département y est encore rattaché

            $table->string('code_dep')->unique();
            $table->string('nom_dep');
            $table->unsignedSmallInteger('annee')->nullable();        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departements');
    }
};
