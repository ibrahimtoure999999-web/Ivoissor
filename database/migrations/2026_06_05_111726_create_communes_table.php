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
        Schema::create('communes', function (Blueprint $table) {
            $table->bigIncrements('id');

            // LIEN PARENT : Crée la clé étrangère 'sous_prefecture_id' reliée à la table 'sous_prefectures'
            // Point de liaison vers la table parente
            $table->foreignId('sous_prefecture_id')->constrained('sous_prefectures')->restrictOnDelete();

            // Code unique fourni par l'ANStat (ex: 'COM_AGB'), optionnel (nullable) et indexé pour les recherches
            $table->string('code_commune')->nullable()->unique();  

            $table->string('nom_commune');

            // Année liée au décret ou à la révision administrative, facultative (nullable)
            $table->unsignedSmallInteger('annee')->nullable();

            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->integer('population')->nullable();
                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};
