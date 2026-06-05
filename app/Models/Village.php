<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable = 
    [
        'tribu_id',
        'nom',
    ];

    public function Tribu(): BelongsTo
    {
        return $this->BelongsTo(Tribu::class, 'tribu_id', 'id');
    }

    public function ressortissants(): HasMany
    {
        return $this->HasMany(Ressortissant::class, 'village_id', $this->getKeyName());
    }
}
