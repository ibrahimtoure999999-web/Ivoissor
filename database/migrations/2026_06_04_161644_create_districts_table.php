<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cette méthode s'exécute lorsque l'on lance la commande 'php artisan migrate'.
     * Elle crée physiquement la table dans notre base de données.
     */
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {

            $table->bigIncrements('id');  // Crée une clé primaire auto-incrémentée
            $table->string('code_district')->unique();
            $table->string('nom_district');
            $table->unsignedSmallInteger('annee')->nullable(); // Crée une colonne pour l'année de découpage ou décret, optionnelle (nullable) et stockée sous forme de petit entier positif
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
        Schema::dropIfExists('districts');
    }
};
