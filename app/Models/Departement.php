<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $fillable = 
    [
        'region_id',
        'code_dep',
        'nom_dep',
        'annee',
    ];

    //Relation vers le HAUT : Un Département appartient à une unique Région.
    // Permet d'écrire en code : $departement->region->nom_reg

    public function region():BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function sousPrefectures(): HasMany
    {
        // Déclare qu'un département possède plusieurs sous-préfectures (prochaine étape du plan d'action)
        // Utilise getKeyName() conformément aux standards exigeants du projet pour cibler la clé primaire de base
        return $this->hasMany(Model::class, 'departement_id', $this->getKeyName());
    } 
}
