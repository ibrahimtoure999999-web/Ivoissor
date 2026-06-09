<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = [
        'code_district',
        'nom_district',
        'annee',
        'latitude',
        'longitude',
        'population',
    ];

    /**
     * Définition de la relation : Un District possède plusieurs Régions.
     * Cette méthode permettra d'écrire plus tard : District::query()->with('regions') pour éviter le problème de performance N+1.
     *
     * @return HasMany<Region, $this>
     */

    public function regions():HasMany
    {
        return $this->hasMany(Region::class, 'district_id', $this->getKeyName());
    }
}
