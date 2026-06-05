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
        Schema::create('regions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Le lien direct vers la table parente 'districts'
            // 'foreignId' crée le champ 'district_id'
            // 'constrained' vérifie que le district existe bien dans la table 'districts'
            // 'restrictOnDelete' interdit de supprimer un district tant qu'il a des régions liées
            $table->foreignId('district_id')->constrained('districts')->restrictOnDelete();
            $table->string('code_reg')->unique();
            $table->string('nom_reg');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
