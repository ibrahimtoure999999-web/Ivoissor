<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commune extends Model
{
    protected $fillable =
        [
            'sous_prefecture_id', // Identifiant de la sous-préfecture parente
            'code_commune',       // Code officiel unique ANStat
            'nom_commune',        // Libellé de la commune / ville
            'annee',
            'latitude',
            'longitude',
            'population',
        ];

    public function sousPrefecture(): BelongsTo
    {
        return $this->belongsTo(SousPrefecture::class, 'sous_prefecture_id', 'id');
    }

    public function ressortissants(): HasMany
    {
        return $this->hasMany(Ressortissant::class, 'commune_id', $this->getKeyName());
    }
}
