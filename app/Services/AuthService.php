<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Gère le cycle de vie de l'authentification des utilisateurs (connexion, déconnexion, inscription) et assure la traçabilité de ces actions.
- Objectif (Le "Pourquoi") : Centraliser toute la logique liée aux comptes utilisateurs pour garantir une gestion sécurisée des accès et garder une trace de chaque mouvement important (qui se connecte, qui s'inscrit).
- Connexions et Dépendances : Utilise les modèles `User`, `Role`, `AuditLog` et les outils Laravel `Auth` (pour gérer les sessions) et `Hash` (pour sécuriser les mots de passe).

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Tente de connecter l'utilisateur avec les identifiants fournis.
     *
     * @param array $credentials
     * @return bool
     */
    public function login(array $credentials): bool
    {
        // Auth::attempt vérifie automatiquement si l'email et le mot de passe correspondent à un utilisateur.
        // Si oui, il crée la session. Sinon, il nous renvoie "false".
        return Auth::attempt($credentials);
    }

    /**
     * Déconnecte l'utilisateur actuel.
     *
     * @return void
     */
    public function logout(): void
    {
        $user = Auth::user();
        if ($user instanceof User) {
            // Avant de fermer la porte, on note dans le journal que l'utilisateur est parti.
            $this->logAudit(
                user: $user,
                action: 'logout',
                description: 'Déconnexion de l\'utilisateur.'
            );
        }
        // Laravel vide la session et les cookies liés à l'authentification.
        Auth::logout();
    }

    /**
     * Inscrit un nouveau citoyen et lui assigne le rôle CITOYEN.
     *
     * @param array $data
     * @return User
     */
    public function registerCitoyen(array $data): User
    {
        // Hash::make transforme le mot de passe en une suite de caractères indéchiffrable.
        // On ne stocke jamais de mot de passe en clair dans la base de données !
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // On cherche le rôle "CITOYEN" dans notre liste de rôles et on l'attache au nouvel utilisateur.
        $roleCitoyen = Role::where('name', RoleEnum::CITOYEN->value)->first();
        if ($roleCitoyen) {
            $user->roles()->attach($roleCitoyen->id);
        }

        $this->logAudit(
            user: $user,
            action: 'register',
            description: 'Inscription d\'un nouveau citoyen avec l\'adresse email : ' . $user->email
        );

        return $user;
    }

    /**
     * Enregistre une action dans les logs d'audit.
     *
     * @param User|null $user
     * @param string $action
     * @param string|null $description
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return void
     */
    public function logAudit(?User $user, string $action, ?string $description = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        // On crée une ligne dans notre journal de sécurité.
        // request()->ip() permet de récupérer l'adresse IP de l'utilisateur (comme son adresse postale numérique).
        // request()->userAgent() nous dit quel navigateur ou appareil il utilise (ex: Chrome sur Windows).
        \App\Models\AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ipAddress ?? request()->ip() ?? '127.0.0.1',
            'user_agent' => substr($userAgent ?? request()->userAgent() ?? 'N/A', 0, 255),
        ]);
    }
}

/*
📖 Glossaire des notions clés
- Hashage : Procédé qui transforme une donnée (ici le mot de passe) en une "empreinte digitale" unique et irréversible pour garantir la confidentialité.
- Session : Un espace temporaire sur le serveur qui permet de "se souvenir" de l'utilisateur d'une page à l'autre après sa connexion.
- Rôle : Un mécanisme qui permet de définir ce qu'un utilisateur a le droit de faire (ex: citoyen, admin).
*/
