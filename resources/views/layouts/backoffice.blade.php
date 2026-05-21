@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice.css') }}">
    @yield('backoffice-styles')
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Sidebar Navigation -->
    <aside class="dashboard-sidebar" style="background-color: #0F172A;">
        <div class="sidebar-logo">
            <span class="logo-text">Ivoissor Agent</span><span>.</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('agent.dashboard') }}" class="sidebar-link @if(Route::is('agent.dashboard')) active @endif">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="link-text">Accueil</span>
                </a>
            </li>
            <li>
                <a href="{{ route('agent.demandes.index') }}" class="sidebar-link @if(Route::is('agent.demandes.index') || Route::is('agent.demandes.show')) active @endif">
                    <span class="material-symbols-outlined">description</span>
                    <span class="link-text">Gestion demandes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}" class="sidebar-link" style="border-top: 1px solid rgba(255, 255, 255, 0.08); margin-top: 1rem; padding-top: 1rem;">
                    <span class="material-symbols-outlined">badge</span>
                    <span class="link-text">Espace Citoyen</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="btn-text">Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div class="dashboard-title">
                <h1>@yield('page-title')</h1>
                <p>@yield('page-subtitle')</p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                @yield('header-actions')
                <div class="user-badge" style="background: var(--bg-surface-elevated); color: var(--text-primary); border-color: var(--border-color);">
                    <span class="material-symbols-outlined">account_circle</span>
                    <span>{{ Auth::user()->name }}</span>
                    <span class="role-tag" style="background-color: rgba(20, 184, 166, 0.15); color: #14b8a6; font-weight:700;">AGENT</span>
                </div>
            </div>
        </header>

        @yield('main-content')
    </main>
</div>
@endsection
