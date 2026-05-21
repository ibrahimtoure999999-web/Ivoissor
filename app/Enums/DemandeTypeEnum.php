<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Définit les types de demandes disponibles dans le système.
- Objectif (Le "Pourquoi") : Centraliser les types de services consulaires pour garantir la cohérence dans le code et les formulaires.
- Connexions et Dépendances : Utilisé pour la logique métier et la validation des demandes.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Enums;

enum DemandeTypeEnum: string
{
    case PASSEPORT = 'PASSEPORT';
    case ETAT_CIVIL = 'ETAT_CIVIL';
    case CARTE_CONSULAIRE = 'CARTE_CONSULAIRE';

    // Retourne le nom officiel du service consulaire.
    public function label(): string
    {
        return match($this) {
            self::PASSEPORT => 'Passeport & Carte d\'Identité',
            self::ETAT_CIVIL => 'Transcription d\'État Civil',
            self::CARTE_CONSULAIRE => 'Carte Consulaire de Résidence',
        };
    }
}
