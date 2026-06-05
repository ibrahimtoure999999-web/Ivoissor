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
        'nom',
    ];

    public function GroupeEthnique():BelongsTo
    {
        return $this->BelongsTo(GroupeEthnique::class, 'groupe_ethnique_id', 'id');
    }

    public function Tribu():HasMany
    {
        return $this->HasMany(Tribu::class, 'canton_id', $this->getKeyName());
    }

}
