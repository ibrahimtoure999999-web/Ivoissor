<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Définit les statuts possibles d'une demande (brochure, soumise, etc.) avec leurs libellés et couleurs associés.
- Objectif (Le "Pourquoi") : Centraliser les états d'une demande pour éviter les erreurs de saisie et faciliter l'affichage cohérent dans l'interface.
- Connexions et Dépendances : Utilisé dans les vues et les services liés aux demandes.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Enums;

// Une "Enum" (Énumération) est une liste fixe de choix possibles.
enum DemandeStatutEnum: string
{
    case BROUILLON = 'BROUILLON';
    case SOUMIS = 'SOUMIS';
    case INSTRUCTION = 'INSTRUCTION';
    case VALIDE = 'VALIDE';
    case REJETE = 'REJETE';

    // Retourne un texte propre à afficher pour l'utilisateur.
    public function label(): string
    {
        return match($this) {
            self::BROUILLON => 'Brouillon',
            self::SOUMIS => 'Soumis',
            self::INSTRUCTION => 'Instruction en cours',
            self::VALIDE => 'Validé / Délivré',
            self::REJETE => 'Rejeté',
        };
    }

    // Retourne une couleur pour les badges dans l'interface.
    public function color(): string
    {
        return match($this) {
            self::BROUILLON => 'slate',
            self::SOUMIS => 'blue',
            self::INSTRUCTION => 'orange',
            self::VALIDE => 'green',
            self::REJETE => 'danger',
        };
    }
}
