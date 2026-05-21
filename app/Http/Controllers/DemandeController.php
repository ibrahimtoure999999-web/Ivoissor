<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Gère les interactions utilisateur liées aux demandes d'enrôlement (création, affichage).
- Objectif (Le "Pourquoi") : Servir d'interface entre l'utilisateur qui remplit son dossier et le système qui le traite, en garantissant la sécurité des données (accès).
- Connexions et Dépendances : Utilise `DemandeService` pour la logique métier et `CreateDemandeRequest` pour valider les données entrantes.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateDemandeRequest;
use App\Models\Demande;
use App\Services\DemandeService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemandeController extends Controller
{
    // On injecte le service DemandeService pour déléguer la logique métier complexe.
    public function __construct(private readonly DemandeService $demandeService)       
    {
    }

    /**
     * Liste des demandes de l'utilisateur connecté.
     */
    public function index(Request $request): View
    {
        $query = auth()->user()->demandes()->with('citoyen');

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type_demande', $request->input('type'));
        }

        $demandes = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('demandes.index', compact('demandes'));
    }

    /**
     * Affiche le formulaire de création d'une demande.
     */
    public function create(): View
    {
        return view('demandes.create');
    }

    /**
     * Enregistre une nouvelle demande et ses documents joints.
     */
    public function store(CreateDemandeRequest $request): RedirectResponse
    {
        try {
            // Le service s'occupe de tout le travail lourd de sauvegarde en base et fichiers.
            $demande = $this->demandeService->createDemande(
                $request->validated(),
                $request->allFiles(),
                auth()->user()
            );

            return redirect()
                ->route('demandes.show', $demande->id)
                ->with('success', 'Votre demande a été soumise avec succès.');      
        } catch (Exception $e) {
            // En cas de problème, on redirige l'utilisateur en arrière avec les données qu'il a déjà saisies.
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de l\'enregistrement de votre dossier : ' . $e->getMessage());
        }
    }

    /**
     * Affiche les détails et le suivi d'une demande spécifique.
     */
    public function show(Demande $demande): View
    {
        // Contrôle d'accès : propriétaire du dossier OU membre du personnel (Agent / Admin)
        $user = auth()->user();
        $isOwner = $user && $user->id === $demande->user_id;
        $isStaff = $user instanceof \App\Models\User && ($user->hasRole('AGENT') || $user->hasRole('ADMIN'));

        abort_unless($isOwner || $isStaff, 403, 'Vous n\'êtes pas autorisé à consulter cette demande.');

        // "Chargement" des informations liées pour éviter de faire des requêtes inutiles à la base (optimisation).
        $demande->load(['citoyen', 'documents']);

        return view('demandes.show', compact('demande'));
    }
}
