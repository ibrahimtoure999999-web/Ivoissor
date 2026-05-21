@extends('layouts.app')

@section('title', 'Inscription')

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
            <h2>Rejoignez l'Espace Citoyen</h2>
            <p>Créez votre compte en quelques instants pour soumettre vos demandes, suivre l'avancement de vos dossiers et réserver vos rendez-vous consulaires.</p>
        </div>
        <div class="auth-banner-footer">
            © 2026 République de Côte d'Ivoire. Tous droits réservés.
        </div>
    </div>

    <!-- inscrition -->
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="auth-card-header">
                <h2>Inscription</h2>
                <p>Créez votre profil de citoyen ivoirien en ligne.</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
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

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                    <div class="form-input-wrapper">
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" required placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    Créer mon compte
                    <span class="material-symbols-outlined">person_add</span>
                </button>
            </form>

            <div class="auth-links">
                <p>Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
