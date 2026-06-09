<?php

// Force l'application rigoureuse du typage strict pour écarter les bugs silencieux
declare(strict_types=1);

namespace App\Models;

// Importation des classes de gestion des modèles et des relations d'Eloquent
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Classe SousPrefecture
 * Gère les données de la table 'sous_prefectures' et ses connexions avec l'arborescence administrative.
 */
class SousPrefecture extends Model
{
    
    protected $fillable = [
        'departement_id', 
        'cod_sp',         
        'nom_sp',         
        'annee',          
        'latitude',
        'longitude',
        'population',
    ]; 

    
    public function departement():BelongsTo
    {
        // Définit la liaison inverse : notre table détient la clé 'departement_id' pointant sur l'id de la table 'departements'
        return $this->belongsTo(Departement::class, 'departement_id', 'id');
    } 

    
    public function communes():HasMany
    {
        // Déclare qu'une sous-préfecture possède plusieurs communes
        // Utilise getKeyName() conformément aux exigences de propreté de notre charte technique
        return $this->hasMany(Commune::class, 'sous_prefecture_id', $this->getKeyName());
    } 
} 
