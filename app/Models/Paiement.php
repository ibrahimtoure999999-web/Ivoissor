<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Représente une transaction financière associée à une demande.
- Objectif (Le "Pourquoi") : Garder une trace des paiements pour valider les demandes d'enrôlement.
- Connexions et Dépendances : Lié au modèle `Demande`.

💻 Code Commenté
*/


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasUuids;

    protected $fillable = [
        'demande_id',
        'reference_transaction',
        'montant',
        'devise',
        'statut',
        'moyen_paiement',
    ];

    // À quelle demande ce paiement correspond-il ?
    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }
}
