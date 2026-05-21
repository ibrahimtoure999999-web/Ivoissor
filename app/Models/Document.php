<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Représente un fichier justificatif associé à une demande d'enrôlement.
- Objectif (Le "Pourquoi") : Stocker les informations sur les documents envoyés par l'utilisateur, y compris les données extraites automatiquement (OCR).
- Connexions et Dépendances : Lié au modèle `Demande` (appartenance).

💻 Code Commenté
*/


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasUuids;

    protected $fillable = [
        'demande_id',
        'type_document',
        'chemin_fichier',
        'donnees_ocr',
        'statut_validation',
    ];

    // "Casting" : Laravel transforme automatiquement la colonne JSON en tableau PHP pour faciliter l'accès.
    protected $casts = [
        'donnees_ocr' => 'array',
    ];

    // À quelle demande ce document est-il rattaché ?
    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }
}
