@extends('layouts.backoffice')

@section('title', 'Espace Agent - Tableau de bord')

@section('page-title', 'Espace Agent')
@section('page-subtitle', 'Bienvenue sur votre tableau de bord consulaire.')

@section('header-actions')
    <a href="{{ route('agent.demandes.index') }}" class="btn btn-orange" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 0.65rem 1.25rem; text-decoration: none;">
        <span class="material-symbols-outlined">description</span>
        Toutes les demandes
    </a>
@endsection

@section('main-content')
    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card" style="border-top: 4px solid var(--blue-500);">
            <h3>Dossiers Soumis</h3>
            <div class="stat-value">{{ $stats['soumis'] }}</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid var(--orange-500);">
            <h3>En Instruction</h3>
            <div class="stat-value">{{ $stats['instruction'] }}</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid var(--green-500);">
            <h3>Dossiers Validés</h3>
            <div class="stat-value">{{ $stats['valide'] }}</div>
        </div>
        <div class="stat-card" style="border-top: 4px solid var(--danger-500);">
            <h3>Dossiers Rejetés</h3>
            <div class="stat-value">{{ $stats['rejete'] }}</div>
        </div>
    </div>

    <!-- Dossiers prioritaires -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">10 Dossiers Prioritaires (En attente d'action)</h2>
        </div>
        <div class="card-body" style="padding:0;">
            @if($dossiersPrioritaires->isEmpty())
                <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
                    Aucun dossier prioritaire en attente.
                </div>
            @else
                <div class="table-responsive">
                    <table class="bo-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--gray-50); border-bottom: 1px solid var(--gray-200); text-align: left;">
                                <th style="padding: 1rem;">ID</th>
                                <th style="padding: 1rem;">Type de demande</th>
                                <th style="padding: 1rem;">Citoyen</th>
                                <th style="padding: 1rem;">Date de soumission</th>
                                <th style="padding: 1rem;">Statut</th>
                                <th style="padding: 1rem; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dossiersPrioritaires as $demande)
                                <tr style="border-bottom: 1px solid var(--gray-100);">
                                    <td style="padding: 1rem;">#{{ substr($demande->id, 0, 8) }}</td>
                                    <td style="padding: 1rem;">
                                        @php
                                            $typeEnum = \App\Enums\DemandeTypeEnum::tryFrom($demande->type_demande);
                                            echo $typeEnum ? $typeEnum->label() : $demande->type_demande;
                                        @endphp
                                    </td>
                                    <td style="padding: 1rem;">
                                        <strong>{{ $demande->citoyen->nom }}</strong> {{ $demande->citoyen->prenoms }}
                                    </td>
                                    <td style="padding: 1rem;">{{ $demande->created_at->format('d/m/Y à H:i') }}</td>
                                    <td style="padding: 1rem;">
                                        @php
                                            $statutEnum = \App\Enums\DemandeStatutEnum::tryFrom($demande->statut);
                                        @endphp
                                        @if($statutEnum)
                                            <span class="badge badge-{{ $statutEnum->color() }}">{{ $statutEnum->label() }}</span>
                                        @else
                                            <span class="badge badge-slate">{{ $demande->statut }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <a href="{{ route('agent.demandes.show', $demande->id) }}" class="btn btn-secondary btn-sm">Traiter</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
