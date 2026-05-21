<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Définit les types d'utilisateurs autorisés dans le système (ex: Citoyen, Agent, Admin).
- Objectif (Le "Pourquoi") : Permettre une gestion flexible et dynamique des droits d'accès via une relation plusieurs-à-plusieurs.
- Connexions et Dépendances : Lié au modèle `User`.

💻 Code Commenté
*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'description'];

    // Quels utilisateurs possèdent ce rôle ?
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
