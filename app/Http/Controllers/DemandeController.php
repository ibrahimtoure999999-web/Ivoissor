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

        // Filtre par type (Passeport, Carte Consulaire ou Transcription d'État Civil)
        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->where('type_demande', $type);

            // Pour l'État Civil (Transcription), on permet de filtrer en plus par sous-type
            if ($type === \App\Enums\DemandeTypeEnum::ETAT_CIVIL->value && $request->filled('sous_type')) {
                $query->where('sous_type', $request->input('sous_type'));
            }
        }

        $demandes = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('demandes.index', compact('demandes'));
    }

    /**
     * Affiche le formulaire de création d'une demande.
     */
    public function create(Request $request): View
    {
        $type = $request->query('type');
        
        // Logique permettant de distinguer l'intention de l'utilisateur dès l'accès au formulaire
        $isTranscription = $type === \App\Enums\DemandeTypeEnum::ETAT_CIVIL->value;
        $isEnrolement = in_array($type, [
            \App\Enums\DemandeTypeEnum::PASSEPORT->value,
            \App\Enums\DemandeTypeEnum::CARTE_CONSULAIRE->value
        ]);

        return view('demandes.create', compact('isTranscription', 'isEnrolement', 'type'));
    }

    /**
     * Enregistre une nouvelle demande et ses documents joints.
     */
    public function store(CreateDemandeRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            
            // Le service s'occupe de tout le travail lourd de sauvegarde en base et fichiers.
            $demande = $this->demandeService->createDemande(
                $validated,
                $request->allFiles(),
                auth()->user()
            );

            // Logique de distinction des messages de succès et de l'acheminement des dossiers
            if ($demande->isTranscription()) {
                $successMessage = "Votre demande de transcription d'état civil (" . $demande->sous_type . ") a été soumise avec succès.";
            } else {
                $successMessage = "Votre demande de pré-enrôlement (" . \App\Enums\DemandeTypeEnum::from($demande->type_demande)->label() . ") a été soumise avec succès.";
            }

            return redirect()
                ->route('demandes.show', $demande->id)
                ->with('success', $successMessage);      
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

        // Logique de distinction pour adapter l'affichage ou les actions dans la vue
        $isTranscription = $demande->isTranscription();
        $isEnrolement = $demande->isEnrolementClassique();

        return view('demandes.show', compact('demande', 'isTranscription', 'isEnrolement'));
    }
}
