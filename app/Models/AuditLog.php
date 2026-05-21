<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Enregistre les actions importantes effectuées par les utilisateurs dans le système.
- Objectif (Le "Pourquoi") : Assurer la traçabilité et la sécurité en gardant un historique de "qui a fait quoi et quand".
- Connexions et Dépendances : Lié au modèle `User`.

💻 Code Commenté
*/


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    // Quel utilisateur est à l'origine de cette action ?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
