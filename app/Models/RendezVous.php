<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Représente un rendez-vous planifié entre un citoyen (via sa demande) et un agent.
- Objectif (Le "Pourquoi") : Organiser la validation physique des demandes d'enrôlement.
- Connexions et Dépendances : Lié aux modèles `Demande` et `User` (l'agent en charge).

💻 Code Commenté
*/


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RendezVous extends Model
{
    use HasUuids;

    /** @var string Override auto-guessed table name */
    // On précise explicitement le nom de la table car Laravel ne le devine pas bien avec le pluriel.
    protected $table = 'rendez_vous';

    protected $fillable = [
        'demande_id',
        'date_heure',
        'lieu',
        'statut',
        'agent_id',
    ];

    // "Casting" : On s'assure que la date est bien traitée comme un objet "Date/Heure" pour Laravel.
    protected $casts = [
        'date_heure' => 'datetime',
    ];

    // Quelle demande est concernée par ce rendez-vous ?
    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    // Quel agent est responsable du rendez-vous ?
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
