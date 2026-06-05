<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $fillable = 
    [
        'sous_prefecture_id', // Identifiant de la sous-préfecture parente
        'code_commune',       // Code officiel unique ANStat
        'nom_commune',        // Libellé de la commune / ville
        'annee',
    ]

    public function SousPrefecture():BelongsTO
    {
        return $this->BelongsTO(SousPrefecture::class, 'sous_prefecture_id', 'id');
    }

    public function Ressortissant():HasMany
    {
        return $this->HasMany(Ressortissant::class, 'commune_id', $this->getKeyNames());
    }
}
