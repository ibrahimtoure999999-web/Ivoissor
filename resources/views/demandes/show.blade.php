@extends('layouts.app')

@section('title', 'Détails du Dossier')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/demandes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rendezvous.css') }}">
    <style>
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

    <!-- Main Content -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div class="dashboard-title">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline" style="padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; border-radius: var(--radius-sm);">
                        <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span>
                    </a>
                    <h1>Dossier #{{ substr($demande->id, 0, 8) }}...</h1>
                </div>
                <p>Créé le {{ $demande->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            
            <div class="user-badge">
                <span class="status-header-badge {{ $demande->statut }}">
                    <span class="material-symbols-outlined" style="font-size: 16px;">
                        @if($demande->statut === 'VALIDE') done_all
                        @elseif($demande->statut === 'REJETE') error
                        @elseif($demande->statut === 'INSTRUCTION') autorenew
                        @else check
                        @endif
                    </span>
                    {{ \App\Enums\DemandeStatutEnum::from($demande->statut)->label() }}
                </span>
            </div>
        </header>

        <!-- Messages Flash de validation -->
        @if (session('success'))
            <div class="glass-panel" style="background: var(--green-light); border: 1px solid var(--green); color: var(--green-hover); padding: 1rem; border-radius: var(--radius-sm); margin-top: 1rem; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Zone d'alerte en cas de rejet -->
        @if ($demande->statut === 'REJETE' && $demande->motif_rejet)
            <div class="rejection-box" style="margin-top: 1rem;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; margin-bottom: 0.5rem;">
                    <span class="material-symbols-outlined">warning</span>
                    <span>Dossier Rejeté par le Consulat</span>
                </div>
                <p style="font-size: 0.95rem;"><strong>Motif du rejet :</strong> {{ $demande->motif_rejet }}</p>
                <p style="font-size: 0.85rem; margin-top: 0.5rem; text-decoration: underline;">Veuillez soumettre une nouvelle demande contenant des pièces conformes.</p>
            </div>
        @endif

        <!-- Dynamic Process Tracker Widget -->
        @php
            $percent = 10;
            if ($demande->statut === 'SOUMIS') $percent = 35;
            elseif ($demande->statut === 'INSTRUCTION') $percent = 65;
            elseif ($demande->statut === 'VALIDE') $percent = 100;
            elseif ($demande->statut === 'REJETE') $percent = 65;
        @endphp
        <section class="glass-panel panel" style="margin-top: 1rem;">
            <div class="panel-header">
                <h2>Suivi d'avancement du dossier</h2>
            </div>
            
            <div class="process-tracker">
                <div class="tracker-progress-line" style="width: {{ $percent }}%;"></div>
                
                <!-- Étape 1 : Création -->
                <div class="step-node completed">
                    <div class="step-circle">
                        <span class="material-symbols-outlined">check</span>
                    </div>
                    <div class="step-label">Création</div>
                </div>
                
                <!-- Étape 2 : Soumission -->
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
                
                <!-- Étape 3 : Instruction -->
                @if($demande->statut === 'REJETE')
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
                
                <!-- Étape 4 : Délivrance -->
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

        <!-- Grid détails civils / pièces justificatives -->
        <div class="details-grid">
            
            <!-- Gauche : Détails du Demandeur -->
            <div class="glass-panel panel" style="margin-top: 0;">
                <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
                    <h3>Informations Civiles du Citoyen</h3>
                </div>
                
                <div class="data-list">
                    <div class="data-item">
                        <span class="data-label">Nom complet</span>
                        <span class="data-value">{{ $demande->citoyen->nom }} {{ $demande->citoyen->prenoms }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Type de démarche</span>
                        <span class="data-value">
                            {{ \App\Enums\DemandeTypeEnum::from($demande->type_demande)->label() }}
                            @if($demande->sous_type)
                                <br><small style="color: var(--text-muted); font-weight: 600;">(Transcription : {{ $demande->sous_type }})</small>
                            @endif
                        </span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Date & Lieu de naissance</span>
                        <span class="data-value">{{ $demande->citoyen->date_naissance->format('d/m/Y') }} à {{ $demande->citoyen->lieu_naissance }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Genre</span>
                        <span class="data-value">{{ $demande->citoyen->genre === 'M' ? 'Masculin (M)' : 'Féminin (F)' }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Numéro National d'Identité (NNI)</span>
                        <span class="data-value">{{ $demande->citoyen->nni ?? 'Non requis / Non fourni' }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Téléphone de contact</span>
                        <span class="data-value">{{ $demande->citoyen->telephone }}</span>
                    </div>
                    <div class="data-item" style="grid-column: span 2;">
                        <span class="data-label">Adresse de résidence</span>
                        <span class="data-value">{{ $demande->citoyen->adresse_residence }} ({{ $demande->citoyen->pays_residence }})</span>
                    </div>
                </div>
            </div>

            <!-- Droite : Documents joints -->
            <div class="glass-panel panel" style="margin-top: 0;">
                <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
                    <h3>Pièces Justificatives</h3>
                </div>
                
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
                    Ces fichiers sont chiffrés et stockés dans notre coffre-fort numérique sécurisé.
                </p>

                <table class="document-table">
                    <thead>
                        <tr>
                            <th>Nom du fichier</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demande->documents as $doc)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span class="material-symbols-outlined" style="color: var(--text-muted); font-size: 18px;">file_present</span>
                                        <strong style="text-transform: capitalize;">{{ str_replace('_', ' ', $doc->type_document) }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--orange-hover);">
                                        {{ $doc->statut_validation }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                                        Télécharger
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted);">Aucune pièce justificative.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- ===== Section Rendez-vous ===== -->
        @php $rdv = $demande->rendezVous()->where('statut', '!=', 'ANNULE')->first(); @endphp

        @if ($rdv)
            <!-- Rendez-vous actif -->
            <div class="rdv-card">
                <div class="rdv-header">
                    <div class="rdv-title">
                        <span class="material-symbols-outlined" style="color: var(--orange); font-size: 22px;">event_available</span>
                        Rendez-vous Planifié
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
                </div>
                <div style="margin-top: 0.75rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <form method="POST" action="{{ route('rendezvous.destroy', $rdv->id) }}"
                        class="cancel-rdv-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline btn-cancel-trigger" style="font-size: 0.85rem; padding: 0.5rem 1rem; color: var(--danger); border-color: var(--danger); cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">event_busy</span>
                            Annuler ce rendez-vous
                        </button>
                    </form>
                </div>
            </div>
        @elseif(in_array($demande->statut, ['SOUMIS', 'INSTRUCTION']))
            <!-- Invitation à prendre rendez-vous -->
            <div class="rdv-card" style="border: 1px dashed var(--orange); background: var(--orange-light);">
                <div class="rdv-header">
                    <div class="rdv-title">
                        <span class="material-symbols-outlined" style="color: var(--orange); font-size: 22px;">calendar_month</span>
                        Rendez-vous Consulaire
                    </div>
                </div>
                <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0.5rem 0 1rem;">
                    Votre dossier est prêt. Planifiez votre rendez-vous de capture biométrique et de vérification des pièces originales auprès du consulat compétent.
                </p>
                <a href="{{ route('rendezvous.create', $demande->id) }}" class="btn btn-orange" style="display: inline-flex; align-items: center; gap: 8px; padding: 0.75rem 1.5rem; text-decoration: none; font-size: 0.9rem;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">event_available</span>
                    Prendre un Rendez-vous
                </a>
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
