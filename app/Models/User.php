<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Représente l'utilisateur du système dans la base de données et définit ses capacités d'authentification et ses relations.
- Objectif (Le "Pourquoi") : C'est le cœur de la gestion des identités. Ce modèle permet à Laravel de savoir qui est connecté, quels sont ses droits et à quelles données il est lié (demandes, rendez-vous, logs).
- Connexions et Dépendances : Étend `Authenticatable` (base de Laravel pour les utilisateurs). Il est lié aux modèles `Role`, `Demande`, `RendezVous` et `AuditLog`.

💻 Code Commenté
*/


namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Fillable définit quels champs peuvent être remplis par l'utilisateur via un formulaire.
// Hidden définit quels champs doivent être cachés lors de l'affichage (ex: ne jamais afficher le mot de passe !).
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    // HasFactory permet de créer des faux utilisateurs pour les tests.
    // Notifiable permet d'envoyer des notifications (ex: emails).
    // HasUuids génère automatiquement des identifiants uniques complexes (au lieu de 1, 2, 3).
    use HasFactory, Notifiable, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        // "Casting" : on transforme automatiquement les données à la sortie de la base.
        // Ici, on s'assure que le mot de passe est toujours traité comme "haché".
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Un utilisateur peut avoir plusieurs rôles (ex: Admin et Agent).
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    // Un utilisateur peut faire plusieurs demandes.
    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class);
    }

    // Un agent peut s'occuper de plusieurs rendez-vous.
    public function rendezVous(): HasMany
    {
        return $this->hasMany(RendezVous::class, 'agent_id');
    }

    // Un utilisateur peut avoir plusieurs entrées dans le journal d'audit.
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Vérifie si l'utilisateur possède un rôle donné.
     */
    public function hasRole(string $role): bool
    {
        // On récupère la liste des noms de rôles de l'utilisateur et on vérifie si celui demandé est dedans.
        return $this->roles->pluck('name')->contains($role);
    }
}

/*
📖 Glossaire des notions clés
- Eloquent : Le langage "magique" de Laravel pour communiquer avec la base de données comme si on manipulait de simples objets PHP.
- Casting : Technique pour convertir automatiquement le format de données d'une base (ex: texte) vers un format utile dans le code (ex: date ou mot de passe haché).
- UUID : Identifiant Unique Universel, une suite de caractères aléatoires garantissant que chaque enregistrement est unique dans le monde entier.
*/
