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
        Schema::create('ressortissants', function (Blueprint $table) {
            // Identifiant technique unique (Clé primaire auto-incrémentée)
            $table->bigIncrements('id');
            $table->string('matricule')->unique();

            // Informations d'Identité
            $table->string('nom');
            $table->string('prenoms');
            $table->string('sexe');
            $table->string('famille')->nullable();

            // État civil et Éducation (rendus nullables)
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('situation_matrimoniale')->nullable(); // Géré par l'Enum SituationMatrimoniale
            $table->string('niveau_etude')->nullable();  // Géré par l'Enum NiveauEtude

            // Coordonnées et Profession (Optionnels / Nullable)
            $table->string('profession')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();

            // Résidence (1-1 inline) - AJOUTÉS
            $table->string('pays_residence')->nullable();
            $table->string('ville_residence')->nullable();
            $table->string('quartier_residence')->nullable();
            $table->text('adresse_complete')->nullable();
            $table->foreignId('village_residence_id')->nullable()->constrained('villages')->nullOnDelete();

            // Liaison vers la commune de résidence/rattachement
            $table->foreignId('commune_id')->nullable()->constrained('communes')->nullOnDelete();

            // Liaison vers le village d'origine
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ressortissants');
    }
};
