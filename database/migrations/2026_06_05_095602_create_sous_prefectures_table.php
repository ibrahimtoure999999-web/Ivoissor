<?php


declare(strict_types=1);

// Importation des composants fondamentaux de gestion de bases de données de Laravel
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * S'exécute lors du déploiement de la migration (php artisan migrate).
     * Crée la table 'sous_prefectures' et ses liaisons.
     */
    public function up(): void
    {
        // Demande la création de la table 'sous_prefectures'
        Schema::create('sous_prefectures', function (Blueprint $table) {
            // Clé primaire auto-incrémentée : identifiant technique unique de la sous-préfecture
            $table->bigIncrements('id');

            // Clé étrangère connectée à la table parente 'departements'
            $table->foreignId('departement_id')->constrained('departements')->restrictOnDelete();
                // Indique la table ciblée par cette clé étrangère
                
                // Empêche la suppression du département parent s'il contient encore des sous-préfectures
                

            // Code officiel unique fourni par l'ANStat (ex: 'SP_LOV') pour assurer la traçabilité métier
            $table->string('cod_sp')->unique();

            // Nom officiel de la sous-préfecture (ex: 'Loviguié')
            $table->string('nom_sp');

            // Année liée au décret ou à la révision administrative, nullable si non documentée
            $table->unsignedSmallInteger('annee')->nullable();

            // Génère les colonnes d'historique de modification 'created_at' et 'updated_at'
            $table->timestamps();
        }); 
    } 

    
    public function down(): void
    {
        // Supprime proprement la table 'sous_prefectures' si elle existe
        Schema::dropIfExists('sous_prefectures');
    } // Fin de la méthode down
}; // Fin de la classe