<?php

// Active le mode strict de PHP pour interdire les mauvais types de données et fiabiliser le code
declare(strict_types=1);

namespace App\Models;

// Importation des composants ORM d'Eloquent pour structurer notre modèle et ses relations
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Classe Tribu
 * Représente une ligne de la table 'tribus' et pilote ses interactions avec le reste du projet.
 */
class Tribu extends Model
{
    /**
     * Liste fermée des colonnes de la base de données que Laravel est autorisé à remplir automatiquement (Mass Assignment).
     * C'est notre barrière de sécurité pour empêcher l'injection de champs non surveillés.
     */
    protected $fillable = [
        'canton_id', // Autorise l'enregistrement du lien vers le canton parent
        'nom',       // Autorise l'enregistrement du nom de la tribu
    ];

    /**
     * Relation vers le HAUT : Une Tribu appartient à un seul et unique Canton parent.
     * Grâce à cela, on pourra écrire en code : $tribu->canton->nom
     */
    public function canton(): BelongsTo
    {
        // Indique à Laravel que la colonne 'canton_id' de notre table actuelle pointe vers l'id de la table 'cantons'
        return $this->belongsTo(Canton::class, 'canton_id', 'id');
    }

    /**
     * Relation vers le BAS : Une Tribu possède plusieurs Villages (la toute dernière étape coutumière).
     * Permet de récupérer instantanément tous les villages enfants de la tribu.
     */
    public function villages(): HasMany
    {
        // Lie notre tribu aux futurs villages enfants en utilisant le nom de notre clé primaire dynamique
        // Cela respecte scrupuleusement les exigences de la charte de développement de notre projet
        return $this->hasMany(Village::class, 'tribu_id', $this->getKeyName());
    }
}
