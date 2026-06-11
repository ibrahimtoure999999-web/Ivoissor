<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NiveauEtude;
use App\Enums\Sexe;
use App\Enums\SituationMatrimoniale;
// Importation des classes nécessaires pour les relations
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ressortissant extends Model
{
    protected $fillable =
        [
            'matricule',
            'nom',
            'prenoms',
            'sexe',
            'date_naissance',
            'lieu_naissance',
            'situation_matrimoniale',
            'niveau_etude',
            'profession',
            'telephone',
            'email',
            'famille',
            'pays_residence',
            'ville_residence',
            'quartier_residence',
            'adresse_complete',
            'village_residence_id',
            'commune_id',
            'village_id',
        ];

    // Indique le type de chaque colonne (par exemple : transforme une date en texte en vrai objet Date)
    protected function casts(): array
    {
        return [
            'sexe' => Sexe::class,
            'situation_matrimoniale' => SituationMatrimoniale::class,
            'niveau_etude' => NiveauEtude::class,
            'date_naissance' => 'date',
        ];
    }

    public function villageResidence(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_residence_id', 'id');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id', 'id');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_id', 'id');
    }
}
