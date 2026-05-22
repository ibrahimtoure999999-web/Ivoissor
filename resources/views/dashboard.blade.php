@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
<div class="dashboard-container">
    <!-- 1. Sidebar Navigation -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-logo">
            <span class="logo-text">Ivoissor</span><span>.</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="sidebar-link @if(Route::is('dashboard')) active @endif">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="link-text">Accueil</span>
                </a>
            </li>
            <li>
                <a href="{{ route('demandes.index') }}" class="sidebar-link @if(Route::is('demandes.index') || Route::is('demandes.create') || Route::is('demandes.show')) active @endif">
                    <span class="material-symbols-outlined">description</span>
                    <span class="link-text">Mes Demandes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('rendezvous.index') }}" class="sidebar-link @if(Route::is('rendezvous.index') || Route::is('rendezvous.create')) active @endif">
                    <span class="material-symbols-outlined">calendar_month</span>
                    <span class="link-text">Rendez-vous</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <span class="material-symbols-outlined">payments</span>
                    <span class="link-text">Paiements</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <span class="material-symbols-outlined">person</span>
                    <span class="link-text">Mon Profil</span>
                </a>
            </li>
            @if(auth()->user()->hasRole(\App\Enums\RoleEnum::AGENT->value))
            <li>
                <a href="{{ route('agent.dashboard') }}" class="sidebar-link" style="background: rgba(249, 115, 22, 0.1); border-left: 4px solid var(--orange);">
                    <span class="material-symbols-outlined" style="color: var(--orange);">admin_panel_settings</span>
                    <span class="link-text" style="color: var(--orange); font-weight: 700;">Espace Agent</span>
                </a>
            </li>
            @endif
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

    <!-- 2. Main content area -->
    <main class="dashboard-main">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="dashboard-title">
                <h1>Mon Espace Consulaire</h1>
                <p>Gérez et suivez vos dossiers en temps réel.</p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="{{ route('demandes.create') }}" class="btn btn-orange btn-header-action">
                    <span class="material-symbols-outlined">add_circle</span>
                    Nouvelle Demande
                </a>
                <div class="user-badge">
                    <span class="material-symbols-outlined">account_circle</span>
                    <span>{{ Auth::user()->name ?? Auth::user()->email }}</span>
                    <span class="role-tag">{{ Auth::user()->roles->first()?->name ?? 'Citoyen' }}</span>
                </div>
            </div>
        </header>

        <!-- Metrics Grid -->
        <section class="metrics-grid">
            <div class="glass-panel metric-card">
                <div class="metric-card-header">
                    <h3>Demandes actives</h3>
                    <div class="metric-icon orange">
                        <span class="material-symbols-outlined">pending_actions</span>
                    </div>
                </div>
                <div class="metric-value">{{ $demandes->where('statut', '!=', 'VALIDE')->count() }}</div>
            </div>

            <div class="glass-panel metric-card">
                <div class="metric-card-header">
                    <h3>Rendez-vous prévus</h3>
                    <div class="metric-icon green">
                        <span class="material-symbols-outlined">event</span>
                    </div>
                </div>
                <div class="metric-value">Aucun</div>
            </div>

            <div class="glass-panel metric-card">
                <div class="metric-card-header">
                    <h3>Solde paiements</h3>
                    <div class="metric-icon orange">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                    </div>
                </div>
                <div class="metric-value">0 FCFA</div>
            </div>
        </section>

        <!-- Current Dossier Tracking (If any request exists) -->
        @if($demandes->isNotEmpty())
            @php
                $latest = $demandes->first();
                $percent = 10;
                if ($latest->statut === 'SOUMIS') $percent = 35;
                elseif ($latest->statut === 'INSTRUCTION') $percent = 65;
                elseif ($latest->statut === 'VALIDE') $percent = 100;
                elseif ($latest->statut === 'REJETE') $percent = 65;
            @endphp
            <section class="glass-panel panel">
                <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Suivi en temps réel : {{ \App\Enums\DemandeTypeEnum::from($latest->type_demande)->label() }} (#{{ substr($latest->id, 0, 8) }}...)</h2>
                    <a href="{{ route('demandes.show', $latest->id) }}" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.8rem;">Détails complets</a>
                </div>
                
                <!-- Process Tracker widget -->
                <div class="process-tracker">
                    <div class="tracker-progress-line" style="width: {{ $percent }}%;"></div>
                    
                    <div class="step-node completed">
                        <div class="step-circle">
                            <span class="material-symbols-outlined">check</span>
                        </div>
                        <div class="step-label">Création</div>
                    </div>
                    
                    <div class="step-node @if($percent >= 35) completed @else upcoming @endif">
                        <div class="step-circle">
                            @if($percent >= 35)
                                <span class="material-symbols-outlined">check</span>
                            @else
                                2
                            @endif
                        </div>
                        <div class="step-label">Soumission</div>
                    </div>
                    
                    @if($latest->statut === 'REJETE')
                        <div class="step-node active" style="color: var(--danger-hover);">
                            <div class="step-circle" style="background-color: var(--danger); border-color: var(--danger); color: var(--white); box-shadow: 0 0 0 4px var(--danger-light);">
                                <span class="material-symbols-outlined">close</span>
                            </div>
                            <div class="step-label">Rejeté</div>
                        </div>
                    @else
                        <div class="step-node @if($percent > 65) completed @elseif($percent == 65) active @else upcoming @endif">
                            <div class="step-circle">
                                @if($percent > 65)
                                    <span class="material-symbols-outlined">check</span>
                                @else
                                    3
                                @endif
                            </div>
                            <div class="step-label">Instruction</div>
                        </div>
                    @endif
                    
                    <div class="step-node @if($percent == 100) completed @else upcoming @endif">
                        <div class="step-circle">
                            @if($percent == 100)
                                <span class="material-symbols-outlined">check</span>
                            @else
                                4
                            @endif
                        </div>
                        <div class="step-label">Délivrance</div>
                    </div>
                </div>
            </section>
        @endif

        <!-- Historique de mes dossiers -->
        <section class="glass-panel panel">
            <div class="panel-header">
                <h2>Mes Dossiers Consulaires</h2>
            </div>
            
            @if($demandes->isEmpty())
                <div style="background-color: var(--bg-surface-elevated); padding: 3rem 1.5rem; border-radius: var(--radius-sm); border: 1px dashed var(--border-color); text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: var(--text-muted); margin-bottom: 1rem;">folder_open</span>
                    <p style="color: var(--text-secondary); font-size: 1rem; margin-bottom: 1.5rem;">
                        Vous n'avez pas encore déposé de demande consulaire.
                    </p>
                    <a href="{{ route('demandes.create') }}" class="btn btn-orange" style="padding: 0.75rem 2rem; display: inline-flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined">add_circle</span>
                        Déposer ma première demande
                    </a>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="demandes-table">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Type de démarche</th>
                                <th>Demandeur</th>
                                <th>Date de création</th>
                                <th>Statut</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($demandes as $demande)
                                <tr>
                                    <td style="font-family: monospace; font-weight: 700; color: var(--text-primary);">
                                        #{{ substr($demande->id, 0, 8) }}
                                    </td>
                                    <td style="font-weight: 600;">
                                        {{ \App\Enums\DemandeTypeEnum::from($demande->type_demande)->label() }}
                                    </td>
                                    <td>
                                        {{ $demande->citoyen->nom }} {{ $demande->citoyen->prenoms }}
                                    </td>
                                    <td style="color: var(--text-secondary);">
                                        {{ $demande->created_at->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $demande->statut }}">
                                            {{ \App\Enums\DemandeStatutEnum::from($demande->statut)->label() }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('demandes.show', $demande->id) }}" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                            Suivre
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
