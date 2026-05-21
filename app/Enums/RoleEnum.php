<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Définit les différents rôles utilisateur possibles dans le système (Admin, Agent, Citoyen).
- Objectif (Le "Pourquoi") : Centraliser la gestion des rôles pour permettre un contrôle d'accès sécurisé et cohérent à travers tout le projet.
- Connexions et Dépendances : Utilisé pour la sécurité et l'assignation de droits aux utilisateurs.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'ADMIN';
    case AGENT = 'AGENT';
    case CITOYEN = 'CITOYEN';

    // Retourne le nom lisible du rôle pour l'interface utilisateur.
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrateur',
            self::AGENT => 'Agent Consulaire',
            self::CITOYEN => 'Citoyen',
        };
    }
}
