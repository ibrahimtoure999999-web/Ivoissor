@extends('layouts.app')

@section('title', 'Mes Demandes')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/demandes.css') }}">
    <style>
        .filter-panel {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 1.5rem;
            padding: 1.25rem;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 200px;
            flex: 1;
        }
        .filter-group label {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-secondary);
        }
        .btn-filter-submit {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.7rem 1.5rem;
            font-weight: 700;
            height: 45px;
        }
        .demandes-table-card {
            padding: 0;
            overflow: hidden;
        }
        .demandes-table {
            width: 100%;
            border-collapse: collapse;
        }
        .demandes-table th, .demandes-table td {
            padding: 1.1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .demandes-table th {
            font-weight: 700;
            color: var(--text-secondary);
            background-color: rgba(255, 255, 255, 0.4);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .demandes-table tr:last-child td {
            border-bottom: none;
        }
        .demandes-table tr:hover td {
            background-color: var(--bg-surface-elevated);
        }
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .status-badge.SOUMIS {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        .status-badge.INSTRUCTION {
            background-color: var(--orange-light);
            color: var(--orange-hover);
            border: 1px solid var(--orange-glow);
        }
        .status-badge.VALIDE {
            background-color: var(--green-light);
            color: var(--green-hover);
            border: 1px solid var(--green-glow);
        }
        .status-badge.REJETE {
            background-color: var(--danger-light);
            color: var(--danger-hover);
            border: 1px solid var(--danger);
        }
        .pagination-container {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            padding: 1rem;
        }
        /* Custom styles for pagination */
        .pagination-container nav {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .pagination-container nav a, .pagination-container nav span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 6px;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-primary);
        }
        .pagination-container nav .active {
            background: var(--orange);
            color: var(--white);
            border-color: var(--orange);
        }
        .pagination-container nav a:hover:not(.active) {
            background: var(--bg-surface-elevated);
            border-color: var(--orange-hover);
        }
    </style>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Sidebar Navigation -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-logo">
            <span class="logo-text">Ivoissor</span><span>.</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="sidebar-link">
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

    <!-- Main Content -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div class="dashboard-title">
                <h1>Mes Demandes de Documents</h1>
                <p>Retrouvez l'historique et le suivi de vos dossiers d'enrôlement.</p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="{{ route('demandes.create') }}" class="btn btn-orange" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 0.65rem 1.25rem; text-decoration: none;">
                    <span class="material-symbols-outlined">add_circle</span>
                    Nouvelle Demande
                </a>
            </div>
        </header>

        <!-- Filtres de recherche -->
        <section class="glass-panel filter-panel">
            <form method="GET" action="{{ route('demandes.index') }}" style="display: flex; gap: 1.25rem; width: 100%; flex-wrap: wrap; align-items: flex-end;">
                <div class="filter-group">
                    <label for="statut">Statut du dossier</label>
                    <select name="statut" id="statut" class="form-control">
                        <option value="">Tous les statuts</option>
                        @foreach(\App\Enums\DemandeStatutEnum::cases() as $statut)
                            <option value="{{ $statut->value }}" @if(request('statut') === $statut->value) selected @endif>
                                {{ $statut->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="type">Type de demande</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">Tous les types</option>
                        @foreach(\App\Enums\DemandeTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}" @if(request('type') === $type->value) selected @endif>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    @if(request()->filled('statut') || request()->filled('type'))
                        <a href="{{ route('demandes.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; justify-content: center; height: 45px; width: 45px; padding: 0;" title="Réinitialiser les filtres">
                            <span class="material-symbols-outlined">filter_alt_off</span>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-orange btn-filter-submit">
                        <span class="material-symbols-outlined">filter_list</span>
                        Filtrer
                    </button>
                </div>
            </form>
        </section>

        <!-- Tableau des demandes -->
        <section class="glass-panel demandes-table-card">
            <table class="demandes-table">
                <thead>
                    <tr>
                        <th>Référence Dossier</th>
                        <th>Bénéficiaire</th>
                        <th>Type de Demande</th>
                        <th>Date de Soumission</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($demandes as $demande)
                        <tr>
                            <td>
                                <a href="{{ route('demandes.show', $demande->id) }}" style="font-weight: 700; color: var(--orange-hover); text-decoration: none;">
                                    #{{ substr($demande->id, 0, 8) }}...
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary);">
                                    {{ $demande->citoyen->nom }} {{ $demande->citoyen->prenoms }}
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600;">
                                    {{ \App\Enums\DemandeTypeEnum::from($demande->type_demande)->label() }}
                                </span>
                            </td>
                            <td>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">
                                    {{ $demande->created_at->format('d/m/Y à H:i') }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge {{ $demande->statut }}">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">
                                        @if($demande->statut === 'VALIDE') done_all
                                        @elseif($demande->statut === 'REJETE') error
                                        @elseif($demande->statut === 'INSTRUCTION') autorenew
                                        @else check
                                        @endif
                                    </span>
                                    {{ \App\Enums\DemandeStatutEnum::from($demande->statut)->label() }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('demandes.show', $demande->id) }}" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                    Détails
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                <span class="material-symbols-outlined" style="font-size: 48px; color: var(--border-color); margin-bottom: 0.5rem; display: block;">description</span>
                                Aucun dossier d'enrôlement trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <!-- Pagination -->
        @if ($demandes->hasPages())
            <div class="pagination-container">
                {{ $demandes->links() }}
            </div>
        @endif
    </main>
</div>
@endsection
