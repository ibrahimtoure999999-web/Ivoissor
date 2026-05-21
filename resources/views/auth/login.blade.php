@extends('layouts.app')

@section('title', 'Connexion')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-split-container">
    <!-- Left Side: Interactive National Banner -->
    <div class="auth-banner-side">
        <div class="auth-banner-header">
            <a href="/" class="auth-banner-logo">Ivoissor<span>.</span></a>
        </div>
        <div class="auth-banner-body">
            <h2>Portail Consulaire Unique</h2>
            <p>Accédez en toute sécurité à vos démarches administratives, enrôlement biométrique, état civil et prise de rendez-vous pour la diaspora ivoirienne.</p>
        </div>
        <div class="auth-banner-footer">
            © 2026 République de Côte d'Ivoire. Tous droits réservés.
        </div>
    </div>

    <!-- Right Side: Auth Form Panel -->
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="auth-card-header">
                <h2>Connexion</h2>
                <p>Heureux de vous revoir ! Connectez-vous à votre espace.</p>
            </div>

            @if (session('status'))
                <div class="alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Adresse Email</label>
                    <div class="form-input-wrapper">
                        <input id="email" type="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="exemple@domaine.ci">
                    </div>
                    @error('email')
                        <div class="text-error">
                            <span class="material-symbols-outlined" style="font-size:16px;">error</span>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="form-input-wrapper">
                        <input id="password" type="password" name="password" class="form-input @error('password') is-invalid @enderror" required placeholder="••••••••">
                    </div>
                    @error('password')
                        <div class="text-error">
                            <span class="material-symbols-outlined" style="font-size:16px;">error</span>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">
                    Se connecter
                    <span class="material-symbols-outlined">login</span>
                </button>
            </form>

            <div class="auth-links">
                <p>Nouveau sur la plateforme ? <a href="{{ route('register') }}">Créer un compte</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
