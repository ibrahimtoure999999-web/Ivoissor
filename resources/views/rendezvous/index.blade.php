@extends('layouts.app')

@section('title', 'Mes Rendez-vous')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/demandes.css') }}?v={{ filemtime(public_path('css/demandes.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/rendezvous.css') }}?v={{ filemtime(public_path('css/rendezvous.css')) }}">
    <style>
        .rdv-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .rdv-card-container {
            margin-top: 0; /* Override default margin */
        }
        .empty-rdv-card {
            background-color: var(--bg-surface-elevated);
            padding: 3rem 1.5rem;
            border-radius: var(--radius-sm);
            border: 1px dashed var(--border-color);
            text-align: center;
            margin-top: 1rem;
        }
        .btn-card-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: var(--radius-sm);
            font-weight: 700;
        }

        /* custom modal for cancellation */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--transition-normal);
        }
        .custom-modal-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        .custom-modal-content {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            box-shadow: var(--glass-shadow-hover);
            border-radius: var(--radius-md);
            width: 90%;
            max-width: 480px;
            padding: 2.25rem 2rem;
            transform: scale(0.9);
            transition: transform var(--transition-normal);
            text-align: center;
        }
        .custom-modal-overlay.show .custom-modal-content {
            transform: scale(1);
        }
        .modal-warning-icon {
            font-size: 3.5rem;
            color: var(--danger);
            background-color: var(--danger-light);
            border-radius: 50%;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .custom-modal-content h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }
        .custom-modal-content p {
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .custom-modal-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        .btn-modal-cancel, .btn-modal-confirm {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
            border: 1px solid transparent;
            font-family: inherit;
        }
        .btn-modal-cancel {
            background: transparent;
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        .btn-modal-cancel:hover {
            background: var(--bg-surface-elevated);
            border-color: var(--border-hover);
        }
        .btn-modal-confirm {
            background: var(--danger);
            color: var(--white);
        }
        .btn-modal-confirm:hover {
            background: var(--danger-hover);
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.2);
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
                <h1>Mes Rendez-vous Consulaires</h1>
                <p>Retrouvez et gérez l'ensemble de vos rendez-vous de capture biométrique.</p>
            </div>
        </header>

        <!-- Messages Flash de validation -->
        @if (session('success'))
            <div class="glass-panel" style="background: var(--green-light); border: 1px solid var(--green); color: var(--green-hover); padding: 1rem; border-radius: var(--radius-sm); margin-top: 1rem; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($rendezVous->isEmpty())
            <div class="empty-rdv-card">
                <span class="material-symbols-outlined" style="font-size: 48px; color: var(--text-muted); margin-bottom: 1rem;">calendar_today</span>
                <p style="color: var(--text-secondary); font-size: 1rem; margin-bottom: 1.5rem;">
                    Vous n'avez aucun rendez-vous consulaire planifié pour le moment.
                </p>
                <a href="{{ route('demandes.index') }}" class="btn btn-orange" style="padding: 0.75rem 2rem; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                    <span class="material-symbols-outlined">description</span>
                    Voir mes dossiers pour planifier un rendez-vous
                </a>
            </div>
        @else
            <div class="rdv-grid">
                @foreach($rendezVous as $rdv)
                    <div class="rdv-card rdv-card-container">
                        <div class="rdv-header">
                            <div class="rdv-title">
                                <span class="material-symbols-outlined" style="color: var(--orange); font-size: 22px;">event_available</span>
                                Rendez-vous Biométrique
                            </div>
                            <span class="badge-rdv-status {{ $rdv->statut }}">{{ $rdv->statut }}</span>
                        </div>
                        <div class="rdv-body">
                            <div class="rdv-item">
                                <span class="rdv-icon"><span class="material-symbols-outlined" style="font-size: 14px;">calendar_month</span></span>
                                <span>Date : <strong>{{ $rdv->date_heure->translatedFormat('l d F Y') }}</strong></span>
                            </div>
                            <div class="rdv-item">
                                <span class="rdv-icon"><span class="material-symbols-outlined" style="font-size: 14px;">schedule</span></span>
                                <span>Heure : <strong>{{ $rdv->date_heure->format('H:i') }}</strong></span>
                            </div>
                            <div class="rdv-item">
                                <span class="rdv-icon"><span class="material-symbols-outlined" style="font-size: 14px;">location_on</span></span>
                                <span>Lieu : <strong>{{ $rdv->lieu }}</strong></span>
                            </div>
                            <div class="rdv-item" style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--border-color);">
                                <span class="rdv-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><span class="material-symbols-outlined" style="font-size: 14px;">description</span></span>
                                <span>Dossier : <a href="{{ route('demandes.show', $rdv->demande->id) }}" style="color: var(--orange-hover); font-weight: 700; text-decoration: none;">#{{ substr($rdv->demande->id, 0, 8) }}</a> ({{ \App\Enums\DemandeTypeEnum::from($rdv->demande->type_demande)->label() }})</span>
                            </div>
                        </div>
                        <div style="margin-top: 0.75rem; border-top: 1px solid var(--border-color); padding-top: 1rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                            <a href="{{ route('demandes.show', $rdv->demande->id) }}" class="btn-card-action btn-outline" style="color: var(--text-primary);">
                                <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                Voir Dossier
                            </a>
                            @if($rdv->statut === 'PLANIFIE')
                                <form method="POST" action="{{ route('rendezvous.destroy', $rdv->id) }}"
                                    class="cancel-rdv-form"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-card-action btn-outline btn-cancel-trigger" style="color: var(--danger); border-color: var(--danger); background: transparent; cursor: pointer;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">event_busy</span>
                                        Annuler
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    <!-- Custom Modal for Appointment Cancellation -->
    <div id="cancel-modal" class="custom-modal-overlay">
        <div class="custom-modal-content">
            <span class="material-symbols-outlined modal-warning-icon">warning</span>
            <h2>Annuler le rendez-vous</h2>
            <p>Êtes-vous sûr de vouloir annuler ce rendez-vous ? Le créneau horaire sera libéré pour d'autres citoyens.</p>
            <div class="custom-modal-actions">
                <button type="button" id="modal-btn-close" class="btn-modal-cancel">Retour</button>
                <button type="button" id="modal-btn-confirm" class="btn-modal-confirm">Confirmer l'annulation</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('cancel-modal');
        const confirmBtn = document.getElementById('modal-btn-confirm');
        const closeBtn = document.getElementById('modal-btn-close');
        let formToSubmit = null;

        // Open modal
        document.querySelectorAll('.btn-cancel-trigger').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                formToSubmit = this.closest('.cancel-rdv-form');
                if (formToSubmit) {
                    modal.classList.add('show');
                }
            });
        });

        // Close modal
        closeBtn.addEventListener('click', function () {
            modal.classList.remove('show');
            formToSubmit = null;
        });

        // Confirm cancellation
        confirmBtn.addEventListener('click', function () {
            if (formToSubmit) {
                formToSubmit.submit();
            }
        });

        // Close when clicking outside content
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                formToSubmit = null;
            }
        });
    });
</script>
@endpush
@endsection
