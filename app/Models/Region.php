<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = [
        'district_id',
        'code_reg',
        'nom_reg',
        'annee',
    ];

    public function district(): BelongsTo
    {
        // 'belongsTo' indique que la clé 'district_id' se trouve dans notre table actuelle 'regions'
        // Nous lions cette clé au modèle District
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    /**
     * Relation vers le BAS : Une Région possède plusieurs Départements (future étape).
     * Permet d'extraire tous les enfants d'un coup de manière performante.
     *
     *
     */
    public function departements(): HasMany
    {
        // Nous utilisons 'getKeyName()' pour récupérer dynamiquement le nom de la clé primaire (généralement 'id')
        // Cela respecte scrupuleusement les exigences de la charte de développement fournie
        return $this->hasMany(Departement::class, 'region_id', $this->getKeyName());
    }
}
