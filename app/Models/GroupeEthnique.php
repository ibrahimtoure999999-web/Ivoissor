<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupeEthnique extends Model
{
    protected $table = 'groupes_ethniques';

    protected $fillable = 
    [
        'nom',
    ];

    public function cantons():HasMany
    {
        return $this->hasMany(Canton::class, 'groupe_ethnique_id', $this->getKeyName());
    }
}
