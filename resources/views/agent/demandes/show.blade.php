@extends('layouts.backoffice')

@section('title', 'Détails du dossier - Espace Agent')

@section('page-title')
    Dossier #{{ substr($demande->id, 0, 8) }}
@endsection

@section('page-subtitle')
    @php
        $typeEnum = \App\Enums\DemandeTypeEnum::tryFrom($demande->type_demande);
        echo $typeEnum ? $typeEnum->label() : $demande->type_demande;
    @endphp
@endsection

@section('header-actions')
    <a href="{{ route('agent.demandes.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 0.65rem 1.25rem; text-decoration: none;">
        <span class="material-symbols-outlined">arrow_back</span>
        Retour à la liste
    </a>
@endsection

@section('backoffice-styles')
    <style>
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) {
            .detail-grid { grid-template-columns: 1fr; }
        }
        .info-group {
            margin-bottom: 1rem;
        }
        .info-label {
            font-size: 0.875rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }
        .info-value {
            font-size: 1rem;
            color: var(--gray-900);
            font-weight: 500;
        }
        .action-panel {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--gray-200);
            margin-top: 2rem;
        }

        /* custom modal style */
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
            transition: opacity var(--transition-normal, 0.2s ease);
        }
        .custom-modal-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        .custom-modal-content {
            background: white;
            border: 1px solid var(--gray-200);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-radius: var(--radius-md);
            width: 90%;
            max-width: 480px;
            padding: 2.25rem 2rem;
            transform: scale(0.9);
            transition: transform var(--transition-normal, 0.2s ease);
            text-align: center;
        }
        .custom-modal-overlay.show .custom-modal-content {
            transform: scale(1);
        }
        .modal-icon {
            font-size: 3.5rem;
            border-radius: 50%;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .modal-icon.blue {
            color: #3b82f6;
            background-color: rgba(59, 130, 246, 0.1);
        }
        .modal-icon.green {
            color: var(--green);
            background-color: var(--green-light);
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
            border-color: var(--gray-300);
            color: var(--gray-700);
        }
        .btn-modal-cancel:hover {
            background: var(--gray-100);
        }
        .btn-modal-confirm.blue {
            background: #3b82f6;
            color: white;
        }
        .btn-modal-confirm.blue:hover {
            background: #2563eb;
        }
        .btn-modal-confirm.green {
            background: var(--green);
            color: white;
        }
        .btn-modal-confirm.green:hover {
            background: var(--green-hover);
        }
    </style>
@endsection

@section('main-content')
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 2rem; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-outlined">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 2rem;">
            <ul style="margin:0; padding-left: 1rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="detail-grid">
        <!-- Informations Citoyen -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Informations du Citoyen</h2>
            </div>
            <div class="card-body">
                <div class="info-group">
                    <div class="info-label">Nom et Prénoms</div>
                    <div class="info-value">{{ mb_strtoupper($demande->citoyen->nom) }} {{ $demande->citoyen->prenoms }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Date et lieu de naissance</div>
                    <div class="info-value">{{ $demande->citoyen->date_naissance->format('d/m/Y') }} à {{ $demande->citoyen->lieu_naissance }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Genre</div>
                    <div class="info-value">{{ $demande->citoyen->genre === 'M' ? 'Masculin' : 'Féminin' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Numéro National d'Identification (NNI)</div>
                    <div class="info-value">{{ $demande->citoyen->nni ?? 'Non renseigné' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Pays et Adresse de résidence</div>
                    <div class="info-value">{{ $demande->citoyen->adresse_residence }}, {{ mb_strtoupper($demande->citoyen->pays_residence) }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Téléphone</div>
                    <div class="info-value">{{ $demande->citoyen->telephone }}</div>
                </div>
            </div>
        </div>

        <!-- Informations Demande & Documents -->
        <div>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Statut et Rendez-vous</h2>
                </div>
                <div class="card-body">
                    <div class="info-group">
                        <div class="info-label">Statut actuel</div>
                        <div class="info-value">
                            @php
                                $statutEnum = \App\Enums\DemandeStatutEnum::tryFrom($demande->statut);
                            @endphp
                            @if($statutEnum)
                                <span class="badge badge-{{ $statutEnum->color() }}">{{ $statutEnum->label() }}</span>
                            @else
                                <span class="badge badge-slate">{{ $demande->statut }}</span>
                            @endif
                        </div>
                    </div>
                    @if($demande->motif_rejet)
                        <div class="info-group" style="margin-top: 1rem; padding: 1rem; background: var(--danger-light); border: 1px solid var(--danger); border-radius: var(--radius-md);">
                            <div class="info-label" style="color: var(--danger-hover);">Motif du rejet</div>
                            <div class="info-value" style="color: var(--danger-hover); font-weight: 600;">{{ $demande->motif_rejet }}</div>
                        </div>
                    @endif

                    <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--gray-200);">

                    <div class="info-group">
                        <div class="info-label">Rendez-vous consulaire</div>
                        @if($demande->rendezVous && $demande->rendezVous->statut !== 'ANNULE')
                            <div class="info-value" style="color: var(--primary-color);">
                                <strong>{{ $demande->rendezVous->date_heure->format('d/m/Y à H:i') }}</strong><br>
                                <span class="text-muted" style="font-size: 0.875rem;">{{ $demande->rendezVous->lieu }}</span>
                            </div>
                        @else
                            <div class="info-value text-muted">Aucun rendez-vous actif.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Pièces Justificatives</h2>
                </div>
                <div class="card-body" style="padding:0;">
                    <ul style="list-style:none; padding:0; margin:0;">
                        @forelse($demande->documents as $doc)
                            <li style="padding: 1rem; border-bottom: 1px solid var(--gray-100); display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong style="text-transform: capitalize;">{{ str_replace('_', ' ', $doc->type_document) }}</strong>
                                    <div class="text-sm text-muted">Soumis le {{ $doc->created_at->format('d/m/Y') }}</div>
                                </div>
                                <a href="{{ route('documents.download', $doc->id) }}" target="_blank" class="btn btn-secondary btn-sm" style="text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">download</span>
                                    Télécharger
                                </a>
                            </li>
                        @empty
                            <li style="padding: 1rem; color: var(--gray-500); text-align: center;">Aucun document.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Panneau d'action Agent -->
    @if(in_array($demande->statut, ['SOUMIS', 'INSTRUCTION']))
        <div class="action-panel">
            <h3 style="margin-top:0; margin-bottom: 1.5rem;">Actions Administratives</h3>
            <div style="display:flex; gap: 1rem; flex-wrap: wrap;">
                
                @if($demande->statut === 'SOUMIS')
                    <form id="instruire-form" action="{{ route('agent.demandes.instruire', $demande->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="button" class="btn btn-primary btn-instruire-trigger">Mettre en instruction</button>
                    </form>
                @endif

                <form id="valider-form" action="{{ route('agent.demandes.valider', $demande->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="button" class="btn btn-valider-trigger" style="background: var(--green); color: white;">Valider le dossier</button>
                </form>

                <button type="button" class="btn btn-secondary" onclick="document.getElementById('reject-form-panel').style.display = 'block'; this.style.display='none';">Rejeter le dossier...</button>
            </div>

            <div id="reject-form-panel" style="display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-300);">
                <form action="{{ route('agent.demandes.rejeter', $demande->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label for="motif_rejet" style="display:block; margin-bottom: 0.5rem; font-weight: 500;">Motif du rejet (obligatoire)</label>
                        <textarea name="motif_rejet" id="motif_rejet" rows="3" class="form-control" required minlength="10" placeholder="Veuillez expliquer précisément pourquoi ce dossier est rejeté (ex: Acte de naissance illisible)..."></textarea>
                    </div>
                    <div style="display:flex; gap: 1rem;">
                        <button type="submit" class="btn" style="background: var(--danger); color: white;">Confirmer le rejet</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('reject-form-panel').style.display = 'none'; document.querySelector('.btn-secondary').style.display='inline-block';">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Custom Modal for Instruction Confirmation -->
    <div id="instruction-modal" class="custom-modal-overlay">
        <div class="custom-modal-content">
            <span class="material-symbols-outlined modal-icon blue">autorenew</span>
            <h2>Mettre en instruction</h2>
            <p>Voulez-vous passer ce dossier sous le statut d'instruction en cours ? Cela indique au citoyen que son dossier est en train d'être examiné.</p>
            <div class="custom-modal-actions">
                <button type="button" id="modal-instruire-close" class="btn-modal-cancel">Annuler</button>
                <button type="button" id="modal-instruire-confirm" class="btn-modal-confirm blue">Passer en instruction</button>
            </div>
        </div>
    </div>

    <!-- Custom Modal for Validation Confirmation -->
    <div id="validation-modal" class="custom-modal-overlay">
        <div class="custom-modal-content">
            <span class="material-symbols-outlined modal-icon green">verified</span>
            <h2>Valider définitivement</h2>
            <p>Êtes-vous sûr de vouloir valider définitivement ce dossier d'enrôlement ? Cette action est irréversible et déclenche l'étape finale de délivrance du document.</p>
            <div class="custom-modal-actions">
                <button type="button" id="modal-valider-close" class="btn-modal-cancel">Annuler</button>
                <button type="button" id="modal-valider-confirm" class="btn-modal-confirm green">Valider le dossier</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal Instruction
        const instructionModal = document.getElementById('instruction-modal');
        const instruireTrigger = document.querySelector('.btn-instruire-trigger');
        const instruireCancel = document.getElementById('modal-instruire-close');
        const instruireConfirm = document.getElementById('modal-instruire-confirm');
        const instruireForm = document.getElementById('instruire-form');

        if (instruireTrigger) {
            instruireTrigger.addEventListener('click', function (e) {
                e.preventDefault();
                instructionModal.classList.add('show');
            });
        }

        if (instruireCancel) {
            instruireCancel.addEventListener('click', function () {
                instructionModal.classList.remove('show');
            });
        }

        if (instruireConfirm) {
            instruireConfirm.addEventListener('click', function () {
                instruireForm.submit();
            });
        }

        // Modal Validation
        const validationModal = document.getElementById('validation-modal');
        const validerTrigger = document.querySelector('.btn-valider-trigger');
        const validerCancel = document.getElementById('modal-valider-close');
        const validerConfirm = document.getElementById('modal-valider-confirm');
        const validerForm = document.getElementById('valider-form');

        if (validerTrigger) {
            validerTrigger.addEventListener('click', function (e) {
                e.preventDefault();
                validationModal.classList.add('show');
            });
        }

        if (validerCancel) {
            validerCancel.addEventListener('click', function () {
                validationModal.classList.remove('show');
            });
        }

        if (validerConfirm) {
            validerConfirm.addEventListener('click', function () {
                validerForm.submit();
            });
        }

        // Close on clicking outside modal
        window.addEventListener('click', function (e) {
            if (e.target === instructionModal) {
                instructionModal.classList.remove('show');
            }
            if (e.target === validationModal) {
                validationModal.classList.remove('show');
            }
        });
    });
</script>
@endpush
