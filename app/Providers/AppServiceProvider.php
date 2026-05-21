<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Fournisseur de services principal de l'application Laravel.
- Objectif (Le "Pourquoi") : Point d'entrée pour configurer des services personnalisés ou effectuer des actions de démarrage (boot) nécessaires au fonctionnement de l'application.
- Connexions et Dépendances : Base Laravel (ServiceProvider).

💻 Code Commenté
*/


namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Le ServiceProvider est comme le "cerveau" qui initialise certains composants de Laravel au démarrage.
class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre des services personnalisés dans le "conteneur" de Laravel (pour les rendre disponibles partout).
     */
    public function register(): void
    {
        // Actuellement vide, on y ajoute des services si besoin.
    }

    /**
     * Effectue des actions de démarrage, comme la configuration de politiques d'accès.
     */
    public function boot(): void
    {
        //
    }
}
