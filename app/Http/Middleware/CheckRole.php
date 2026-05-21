<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Middleware de sécurité pour vérifier si un utilisateur possède les rôles nécessaires pour accéder à une page.
- Objectif (Le "Pourquoi") : Empêcher les utilisateurs non autorisés (ex: un citoyen essayant d'accéder à l'interface admin) de consulter des zones protégées.
- Connexions et Dépendances : Utilise le modèle `User` (et ses rôles).

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Un Middleware est un "filtre" de sécurité qui vérifie les requêtes avant qu'elles n'atteignent le code final.
class CheckRole
{
    /**
     * Vérifie si l'utilisateur authentifié possède l'un des rôles requis.
     * Usage dans les routes : middleware('role:ADMIN') ou middleware('role:AGENT,ADMIN')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Si l'utilisateur n'est pas connecté, on lui refuse immédiatement l'accès.
        if (!$user) {
            abort(401, 'Non authentifié.');
        }

        // On récupère la liste des noms de rôles que possède cet utilisateur.
        $userRoles = $user->roles->pluck('name')->toArray();

        // On vérifie si au moins un des rôles demandés pour cette page est présent chez l'utilisateur.
        foreach ($roles as $role) {
            if (in_array($role, $userRoles, true)) {
                // Tout est bon, on laisse passer la requête.
                return $next($request);
            }
        }

        // Aucun rôle ne correspond, on bloque l'accès (erreur 403 : Forbidden).
        abort(403, 'Accès non autorisé. Rôle insuffisant.');
    }
}
