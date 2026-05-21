<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Demande;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Affiche le tableau de bord de l'administrateur.
     *
     * Récupère les statistiques globales et les derniers logs d'audit,
     * puis renvoie la vue admin.dashboard.
     */
    public function dashboard(): View
    {
        // Statistiques globales pour le dashboard
        $stats = [
            'total_users'     => User::count(),
            'total_demandes'  => Demande::count(),
            'soumis'          => Demande::where('statut', 'SOUMIS')->count(),
            'instruction'     => Demande::where('statut', 'INSTRUCTION')->count(),
            'valide'          => Demande::where('statut', 'VALIDE')->count(),
            'rejete'          => Demande::where('statut', 'REJETE')->count(),
            'total_agents'    => User::whereHas('roles', fn($q) => $q->where('name', 'AGENT'))->count(),
            'total_citoyens'  => User::whereHas('roles', fn($q) => $q->where('name', 'CITOYEN'))->count(),
        ];

        // Les derniers événements d'audit avec l'utilisateur lié
        $recentLogs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLogs'));
    }

    /**
     * Affiche la liste des utilisateurs.
     *
     * Permet de rechercher par nom ou email, et de filtrer par rôle.
     */
    public function users(Request $request): View
    {
        $search = $request->input('search', '');
        $roleFilter = $request->input('role', '');

        // Construire la requête de base pour récupérer les utilisateurs
        $query = User::with('roles')->orderBy('created_at', 'desc');

        if ($search) {
            // Filtre de recherche sur le nom et l'email
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            // Filtre par rôle lié à l'utilisateur
            $query->whereHas('roles', fn($q) => $q->where('name', $roleFilter));
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles', 'search', 'roleFilter'));
    }

    /**
     * Assigne un rôle à un utilisateur.
     *
     * Cette action remplace le rôle actuel de l'utilisateur par le rôle choisi.
     */
    public function assignRole(User $user, Request $request): RedirectResponse
    {
        // Validation du rôle autorisé
        $request->validate([
            'role' => 'required|in:ADMIN,AGENT,CITOYEN',
        ]);

        // Interdit de modifier son propre rôle
        abort_if(auth()->id() === $user->id, 403, 'Vous ne pouvez pas modifier votre propre rôle.');

        $role = Role::where('name', $request->input('role'))->firstOrFail();

        // Sync assure qu'un utilisateur n'a qu'un seul rôle actif
        $user->roles()->sync([$role->id]);

        // Enregistrer l'action dans le journal d'audit
        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'admin_role_assignation',
            'description' => "Rôle {$role->name} assigné à l'utilisateur {$user->name} ({$user->email}) par l'admin.",
            'ip_address'  => $request->ip() ?? '127.0.0.1',
            'user_agent'  => substr($request->userAgent() ?? 'N/A', 0, 255),
        ]);

        return back()->with('success', "Le rôle \"{$role->name}\" a été assigné à {$user->name}.");
    }

    /**
     * Affiche les logs d'audit.
     *
     * Permet de filtrer par action et de rechercher dans la description,
     * l'adresse IP ou le nom de l'utilisateur.
     */
    public function logs(Request $request): View
    {
        $action = $request->input('action', '');
        $search = $request->input('search', '');

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($action) {
            $query->where('action', $action);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Liste des actions existantes pour le filtre de la vue
        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.logs.index', compact('logs', 'actions', 'action', 'search'));
    }
}
