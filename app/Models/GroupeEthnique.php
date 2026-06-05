<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupeEthnique extends Model
{
    protected $fillable = 
    [
        'nom',
    ];

    public function Canton(): HasMany
    {
        return $this->HasMany(Canton::class, 'groupe_ethnique_id', $this->getKeyName());
    }
}
