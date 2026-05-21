<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Gère la planification, la consultation et l'annulation des rendez-vous consulaires pour les demandes.
- Objectif (Le "Pourquoi") : Organiser le flux de travail des agents et permettre aux citoyens de prendre rendez-vous de manière autonome tout en respectant les contraintes consulaires.
- Connexions et Dépendances : Lié aux modèles `Demande`, `RendezVous`, `AuditLog`, et `RendezVousRequest` pour la validation.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RendezVousRequest;
use App\Models\AuditLog;
use App\Models\Demande;
use App\Models\RendezVous;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RendezVousController extends Controller
{
    /**
     * Liste des rendez-vous de l'utilisateur connecté.
     */
    public function index(Request $request): View
    {
        $rendezVous = RendezVous::whereIn('demande_id', auth()->user()->demandes()->pluck('id'))
            ->with('demande.citoyen')
            ->orderBy('date_heure', 'asc')
            ->get();

        return view('rendezvous.index', compact('rendezVous'));
    }

    /**
     * Affiche le formulaire de prise de rendez-vous.
     */
    public function create(Demande $demande): View
    {
        // On vérifie que la personne connectée est bien le propriétaire du dossier.
        abort_unless(auth()->id() === $demande->user_id, 403, "Accès non autorisé.");

        // On vérifie que la demande est bien soumise ou en cours d'instruction.
        abort_unless(in_array($demande->statut, ['SOUMIS', 'INSTRUCTION']), 400, "Le dossier doit être au statut 'Soumis' ou 'En instruction' pour planifier un rendez-vous.");

        // On vérifie qu'il n'y a pas déjà un rendez-vous en cours pour cette demande.
        $hasActiveRdv = $demande->rendezVous()
            ->where('statut', '!=', 'ANNULE')
            ->exists();

        abort_if($hasActiveRdv, 400, "Un rendez-vous actif est déjà programmé pour cette demande.");

        // On détermine le consulat par défaut selon le pays de résidence du citoyen.
        $pays = mb_strtolower($demande->citoyen->pays_residence ?? 'france', 'UTF-8'); 
        $lieuDefaut = "Consulat Général de Côte d'Ivoire à Paris";

        if (str_contains($pays, 'belgique')) {
            $lieuDefaut = "Ambassade de Côte d'Ivoire à Bruxelles";
        } elseif (str_contains($pays, 'sénégal') || str_contains($pays, 'senegal')) {
            $lieuDefaut = "Ambassade de Côte d'Ivoire à Dakar";
        } elseif (str_contains($pays, 'maroc')) {
            $lieuDefaut = "Ambassade de Côte d'Ivoire à Rabat";
        } elseif (str_contains($pays, 'canada')) {
            $lieuDefaut = "Ambassade de Côte d'Ivoire à Ottawa";
        } elseif (str_contains($pays, 'usa') || str_contains($pays, 'états-unis') || str_contains($pays, 'etats-unis')) {
            $lieuDefaut = "Ambassade de Côte d'Ivoire à Washington";
        }

        return view('rendezvous.create', compact('demande', 'lieuDefaut'));
    }

    /**
     * Enregistre un rendez-vous.
     */
    public function store(RendezVousRequest $request, Demande $demande): RedirectResponse
    {
        abort_unless(auth()->id() === $demande->user_id, 403, "Accès non autorisé.");

        // On vérifie que la demande est bien soumise ou en cours d'instruction.
        abort_unless(in_array($demande->statut, ['SOUMIS', 'INSTRUCTION']), 400, "Le dossier doit être au statut 'Soumis' ou 'En instruction' pour planifier un rendez-vous.");

        $hasActiveRdv = $demande->rendezVous()
            ->where('statut', '!=', 'ANNULE')
            ->exists();

        abort_if($hasActiveRdv, 400, "Un rendez-vous actif est déjà programmé pour cette demande.");

        $dateHeure = $request->input('date') . ' ' . $request->input('creneau') . ':00';

        // Création officielle du rendez-vous.
        $rdv = RendezVous::create([
            'demande_id' => $demande->id,
            'date_heure' => $dateHeure,
            'lieu' => $request->input('lieu'),
            'statut' => 'PLANIFIE',
        ]);

        // On note l'action dans le journal d'audit.
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'rendez_vous_creation',
            'description' => "Planification d'un rendez-vous pour la demande {$demande->type_demande} (ID: {$demande->id}) le {$rdv->date_heure->format('d/m/Y à H:i')} à {$rdv->lieu}.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => substr(request()->userAgent() ?? 'N/A', 0, 255),
        ]);

        return redirect()->route('demandes.show', $demande->id)
            ->with('success', 'Votre rendez-vous consulaire a été planifié avec succès.');
    }

    /**
     * Supprime/Annule un rendez-vous.
     */
    public function destroy(RendezVous $rendezVous, Request $request): RedirectResponse
    {
        $demande = $rendezVous->demande;
        abort_unless(auth()->id() === $demande->user_id, 403, "Accès non autorisé.");

        // On vérifie que le rendez-vous est bien au statut PLANIFIE.
        abort_unless($rendezVous->statut === 'PLANIFIE', 400, "Ce rendez-vous ne peut plus être annulé.");

        $dateHeureStr = $rendezVous->date_heure->format('d/m/Y à H:i');
        $lieuStr = $rendezVous->lieu;

        // On supprime physiquement le rendez-vous pour rendre le créneau à nouveau disponible.
        $rendezVous->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'rendez_vous_annulation',
            'description' => "Annulation du rendez-vous pour la demande {$demande->type_demande} (ID: {$demande->id}) prévu le {$dateHeureStr} à {$lieuStr}.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => substr($request->userAgent() ?? 'N/A', 0, 255),
        ]);

        return redirect()->route('demandes.show', $demande->id)
            ->with('success', 'Votre rendez-vous consulaire a été annulé et le créneau a été libéré.');
    }

    /**
     * Endpoint API pour obtenir les créneaux déjà réservés d'une date donnée.   
     */
    public function getOccupiedSlots(Request $request): JsonResponse
    {
        // Validation des paramètres de recherche pour éviter les mauvaises requêtes.
        $request->validate([
            'date' => 'required|date',
            'lieu' => 'required|string|max:150',
        ]);

        $date = $request->input('date');
        $lieu = $request->input('lieu');

        // On cherche tous les rendez-vous pris à cette date et dans ce lieu, sauf ceux annulés.
        $occupied = RendezVous::whereBetween('date_heure', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->where('lieu', $lieu)
            ->where('statut', '!=', 'ANNULE')
            ->get()
            ->map(fn($r) => $r->date_heure->format('H:i'))
            ->toArray();

        return response()->json($occupied);
    }
}
