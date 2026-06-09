<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Canton extends Model
{
    protected $fillable = 
    [
        'groupe_ethnique_id',
        'region_id',
        'nom',
    ];

    public function groupeEthnique():BelongsTo
    {
        return $this->belongsTo(GroupeEthnique::class, 'groupe_ethnique_id', 'id');
    }

    public function region():BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function tribus():HasMany
    {
        return $this->hasMany(Tribu::class, 'canton_id', $this->getKeyName());
    }
    
}
