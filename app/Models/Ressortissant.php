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
        'commune_id',
        'village_id',
    ];


    // Définition des casts pour les attributs énumérés et la date de naissance 
    protected function casts(): array
    {
        return [
            'sexe' => Sexe::class,
            'situation_matrimoniale' => SituationMatrimoniale::class,
            'niveau_etude' => NiveauEtude::class,
            'date_naissance' => 'date', // Convertit automatiquement la chaîne en objet Carbon Date
        ];
    }


    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id', 'id');
    }

    public function village() : BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_id', 'id');
    }
}
