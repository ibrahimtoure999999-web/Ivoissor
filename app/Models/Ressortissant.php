<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    protected function casts(): array
    {
        return [
            'sexe' => Sexe::class,
            'situation_matrimoniale' => SituationMatrimoniale::class,
            'niveau_etude' => NiveauEtude::class,
            'date_naissance' => 'date', // Convertit automatiquement la chaîne en objet Carbon Date
        ];
    }


    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id', 'id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id', 'id');
    }
}
