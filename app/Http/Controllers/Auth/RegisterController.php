<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCitoyenRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /// Le constructeur reçoit une instance du service d'authentification 
    // qui gère la logique métier liée à l'inscription.

    public function __construct(private readonly AuthService $authService)
    {
    }

    /**
     * Affiche le formulaire d'inscription citoyen.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Enregistre un nouveau citoyen.
     */
    public function store(RegisterCitoyenRequest $request): RedirectResponse
    {
        // La validation des données est déjà assurée par le Form Request `RegisterCitoyenRequest`.
        // On délègue la création du compte au service d'authentification pour séparer les responsabilités
        //  et garder le contrôleur léger.

        $this->authService->registerCitoyen($request->validated());
        
        // Après l'inscription, on redirige vers la page de connexion avec un message de succès.

        return redirect()->route('login')->with('status', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');
    }
}
