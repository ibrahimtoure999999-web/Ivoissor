<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Représente une demande d'enrôlement soumise par un utilisateur pour un citoyen.
- Objectif (Le "Pourquoi") : Centraliser l'état d'une demande (soumise, rejetée, validée) et faire le pont entre l'utilisateur, le citoyen, les documents et les rendez-vous.
- Connexions et Dépendances : Lié aux modèles `User`, `Citoyen`, `Document`, `RendezVous` et `Paiement`.

💻 Code Commenté
*/


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Demande extends Model
{
    // Utilise des identifiants uniques complexes.
    use HasUuids;

    // Champs modifiables pour créer ou mettre à jour une demande.
    protected $fillable = [
        'user_id',
        'citoyen_id',
        'type_demande',
        'statut',
        'motif_rejet',
    ];

    // Qui a créé cette demande ?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Pour quel citoyen ?
    public function citoyen(): BelongsTo
    {
        return $this->belongsTo(Citoyen::class);
    }

    // Quels sont les justificatifs fournis avec cette demande ?
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // Y a-t-il un rendez-vous lié à cette demande ?
    public function rendezVous(): HasOne
    {
        return $this->hasOne(RendezVous::class);
    }

    // Quels sont les paiements associés ?
    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }
}
