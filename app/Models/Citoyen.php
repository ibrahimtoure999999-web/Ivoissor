<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Représente les informations personnelles d'un citoyen dans le système.
- Objectif (Le "Pourquoi") : Centraliser et stocker les données d'identité nécessaires pour traiter les demandes d'enrôlement.
- Connexions et Dépendances : Modèle Eloquent standard, lié au modèle `Demande` (un citoyen peut avoir fait plusieurs demandes).

💻 Code Commenté
*/


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Citoyen extends Model
{
    // Utilise des identifiants uniques complexes au lieu de simples nombres (1, 2, 3).
    use HasUuids;

    // Ces champs sont ceux que l'on autorise à remplir depuis un formulaire.
    protected $fillable = [
        'nni',
        'nom',
        'prenoms',
        'date_naissance',
        'lieu_naissance',
        'genre',
        'pays_residence',
        'adresse_residence',
        'telephone',
    ];

    // On dit à Laravel que ce champ est une date, pour qu'il puisse nous aider à la manipuler facilement.
    protected $casts = [
        'date_naissance' => 'date',
    ];

    // Un citoyen peut avoir plusieurs demandes dans notre système.
    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class);
    }
}

/*
📖 Glossaire des notions clés
- Eloquent : Le langage "magique" de Laravel pour communiquer avec la base de données comme si on manipulait de simples objets PHP.
- Model : Une classe qui représente une table de la base de données. Chaque instance d'un modèle correspond à une ligne dans cette table.
- Fillable : Une liste de sécurité qui dit à Laravel quels champs sont autorisés à être remplis en masse depuis une requête utilisateur.
*/
