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
        $this->authService->registerCitoyen($request->validated());
        
        // Log the user in directly after registration if desired, or redirect to login.
        // For security, we can redirect to login with a success message.
        return redirect()->route('login')->with('status', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');
    }
}
