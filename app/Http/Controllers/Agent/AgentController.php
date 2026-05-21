<?php

declare(strict_types=1);

/*
🎯 Fiche d'Identité du Code
- Rôle principal : Gérer les actions de l'Agent Consulaire dans le Back-office (tableau de bord, liste des demandes, instruction, validation, rejet).
- Objectif (Le "Pourquoi") : Permettre aux agents de traiter efficacement les demandes d'enrôlement soumises par les citoyens. Ce contrôleur centralise le flux de travail administratif.
- Connexions et Dépendances : Modèles `Demande`, `AuditLog`. Interagit avec les vues `agent.*`.

💻 Code Commenté
*/

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Demande;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentController extends Controller
{
    /**
     * Dashboard de l'espace agent avec statistiques.
     */
    public function dashboard(): View
    {
        // On récupère le nombre de demandes pour chaque statut afin d'alimenter les compteurs du tableau de bord.
        $stats = [
            'soumis'      => Demande::where('statut', 'SOUMIS')->count(),
            'instruction' => Demande::where('statut', 'INSTRUCTION')->count(),
            'valide'      => Demande::where('statut', 'VALIDE')->count(),
            'rejete'      => Demande::where('statut', 'REJETE')->count(),
        ];

        // On récupère les 10 dossiers les plus anciens qui nécessitent une action (SOUMIS ou INSTRUCTION).
        $dossiersPrioritaires = Demande::with(['citoyen', 'user'])
            ->whereIn('statut', ['SOUMIS', 'INSTRUCTION'])
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        return view('agent.dashboard', compact('stats', 'dossiersPrioritaires'));
    }

    /**
     * Liste paginée de tous les dossiers avec filtres.
     */
    public function index(Request $request): View
    {
        // On récupère les critères de recherche envoyés dans l'URL.
        $statut = $request->input('statut', '');
        $type   = $request->input('type', '');
        $search = $request->input('search', '');

        // On prépare notre requête en chargeant les informations liées (citoyen et utilisateur).
        $query = Demande::with(['citoyen', 'user'])->orderBy('created_at', 'desc');

        // Application des filtres si l'utilisateur en a sélectionné
        if ($statut) {
            $query->where('statut', $statut);
        }
        if ($type) {
            $query->where('type_demande', $type);
        }
        if ($search) {
            // Recherche textuelle sur le nom, prénom ou NNI du citoyen concerné par la demande.
            $query->whereHas('citoyen', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenoms', 'like', "%{$search}%")
                  ->orWhere('nni', 'like', "%{$search}%");
            });
        }

        // On divise les résultats en pages de 15 éléments pour ne pas surcharger l'écran.
        // "withQueryString" permet de garder les filtres actifs quand on change de page.
        $demandes = $query->paginate(15)->withQueryString();

        return view('agent.demandes.index', compact('demandes', 'statut', 'type', 'search'));
    }

    /**
     * Affiche un dossier complet pour l'agent.
     */
    public function show(Demande $demande): View
    {
        // On charge toutes les données relatives au dossier pour un affichage complet sans requêtes supplémentaires.
        $demande->load(['citoyen', 'user', 'documents', 'rendezVous']);
        return view('agent.demandes.show', compact('demande'));
    }

    /**
     * Passe un dossier en statut INSTRUCTION.
     */
    public function instruire(Demande $demande, Request $request): RedirectResponse
    {
        // Seul un dossier "SOUMIS" peut passer à l'étape "INSTRUCTION".
        abort_if($demande->statut !== 'SOUMIS', 400, 'Seuls les dossiers soumis peuvent être mis en instruction.');

        $demande->update(['statut' => 'INSTRUCTION']);

        // On trace cette action dans notre journal de sécurité.
        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'demande_instruction',
            'description' => "Dossier {$demande->type_demande} (ID: {$demande->id}) pour {$demande->citoyen->nom} {$demande->citoyen->prenoms} passé en instruction.",
            'ip_address'  => $request->ip() ?? '127.0.0.1',
            'user_agent'  => substr($request->userAgent() ?? 'N/A', 0, 255),
        ]);

        return back()->with('success', 'Le dossier est maintenant en cours d\'instruction.');
    }

    /**
     * Valide définitivement un dossier.
     */
    public function valider(Demande $demande, Request $request): RedirectResponse
    {
        // Vérification que le dossier est dans un état qui permet la validation.
        abort_if(!in_array($demande->statut, ['SOUMIS', 'INSTRUCTION']), 400, 'Ce dossier ne peut pas être validé dans son état actuel.');

        // On valide et on efface tout éventuel motif de rejet précédent.
        $demande->update(['statut' => 'VALIDE', 'motif_rejet' => null]);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'demande_validation',
            'description' => "Dossier {$demande->type_demande} (ID: {$demande->id}) pour {$demande->citoyen->nom} {$demande->citoyen->prenoms} VALIDÉ.",
            'ip_address'  => $request->ip() ?? '127.0.0.1',
            'user_agent'  => substr($request->userAgent() ?? 'N/A', 0, 255),
        ]);

        return back()->with('success', 'Le dossier a été validé avec succès.');
    }

    /**
     * Rejette un dossier avec un motif obligatoire.
     */
    public function rejeter(Demande $demande, Request $request): RedirectResponse
    {
        // On impose à l'agent de fournir une explication claire et suffisante (minimum 10 caractères) pour justifier le rejet.
        $request->validate([
            'motif_rejet' => 'required|string|min:10|max:500',
        ], [
            'motif_rejet.required' => 'Le motif de rejet est obligatoire.',
            'motif_rejet.min'      => 'Le motif doit contenir au moins 10 caractères.',
        ]);

        abort_if(!in_array($demande->statut, ['SOUMIS', 'INSTRUCTION']), 400, 'Ce dossier ne peut pas être rejeté dans son état actuel.');

        $demande->update([
            'statut'      => 'REJETE',
            'motif_rejet' => $request->input('motif_rejet'),
        ]);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'demande_rejet',
            'description' => "Dossier {$demande->type_demande} (ID: {$demande->id}) pour {$demande->citoyen->nom} {$demande->citoyen->prenoms} REJETÉ. Motif : {$demande->motif_rejet}",
            'ip_address'  => $request->ip() ?? '127.0.0.1',
            'user_agent'  => substr($request->userAgent() ?? 'N/A', 0, 255),
        ]);

        return back()->with('success', 'Le dossier a été rejeté et le citoyen sera notifié du motif.');
    }
}
