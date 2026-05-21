<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Authentifie l'utilisateur.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $email = (string) $request->input('email');
        $throttleKey = Str::transliterate(Str::lower($email) . '|' . $request->ip());

        // Max 5 attempts per minute
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            // Log brute force block attempt
            $this->authService->logAudit(
                user: null,
                action: 'login_blocked',
                description: 'Tentative de connexion bloquée suite à trop d\'échecs pour l\'adresse email : ' . $email,
                ipAddress: $request->ip()
            );

            return back()->withErrors([
                'email' => "Trop de tentatives de connexion. Veuillez réessayer dans {$seconds} secondes.",
            ])->onlyInput('email');
        }

        if ($this->authService->login($request->only('email', 'password'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = auth()->user();
            if ($user instanceof User) {
                $this->authService->logAudit(
                    user: $user,
                    action: 'login_success',
                    description: 'Connexion réussie de l\'utilisateur.',
                    ipAddress: $request->ip()
                );

                if ($user->hasRole(\App\Enums\RoleEnum::AGENT->value)) {
                    return redirect()->intended(route('agent.dashboard'));
                }
            }

            return redirect()->intended(route('dashboard'));
        }

       

        // les fameux 60 secondes de pénalité pour éviter les attaques par force brute. 
        // C'est une mesure de sécurité essentielle pour protéger les comptes des utilisateurs 
        // contre les tentatives de connexion non autorisées.
        RateLimiter::hit($throttleKey, 60);

        $this->authService->logAudit(
            user: null,
            action: 'login_failed',
            description: 'Échec de connexion pour l\'adresse email : ' . $email,
            ipAddress: $request->ip()
        );

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
