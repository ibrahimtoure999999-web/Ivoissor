<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    protected $fillable = 
    [
        'tribu_id',
        'nom',
    ];

    public function tribu(): BelongsTo
    {
        return $this->belongsTo(Tribu::class, 'tribu_id', 'id');
    }

    public function ressortissants(): HasMany
    {
        return $this->hasMany(Ressortissant::class, 'village_id', $this->getKeyName());
    }
}
