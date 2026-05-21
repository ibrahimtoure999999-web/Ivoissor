@extends('layouts.backoffice')

@section('title', 'Toutes les demandes - Espace Agent')

@section('page-title', 'Gestion des demandes')
@section('page-subtitle', 'Consultez et filtrez l\'ensemble des dossiers consulaires.')

@section('header-actions')
    <a href="{{ route('agent.dashboard') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 0.65rem 1.25rem; text-decoration: none;">
        <span class="material-symbols-outlined">dashboard</span>
        Retour Dashboard
    </a>
@endsection

@section('backoffice-styles')
    <style>
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
    </style>
@endsection

@section('main-content')
    <!-- Filtres -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <form action="{{ route('agent.demandes.index') }}" method="GET" class="filter-bar">
                <div class="filter-group" style="flex: 1; min-width: 200px;">
                    <label for="search">Rechercher</label>
                    <input type="text" id="search" name="search" class="form-control" placeholder="Nom, prénoms ou NNI..." value="{{ request('search') }}">
                </div>
                <div class="filter-group" style="min-width: 200px;">
                    <label for="type">Type de demande</label>
                    <select id="type" name="type" class="form-control">
                        <option value="">Tous les types</option>
                        @foreach(\App\Enums\DemandeTypeEnum::cases() as $typeCase)
                            <option value="{{ $typeCase->value }}" {{ request('type') === $typeCase->value ? 'selected' : '' }}>
                                {{ $typeCase->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group" style="min-width: 200px;">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut" class="form-control">
                        <option value="">Tous les statuts</option>
                        @foreach(\App\Enums\DemandeStatutEnum::cases() as $statutCase)
                            <option value="{{ $statutCase->value }}" {{ request('statut') === $statutCase->value ? 'selected' : '' }}>
                                {{ $statutCase->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group" style="justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="height: 40px; padding: 0 1.5rem; font-weight: 600;">Filtrer</button>
                </div>
                @if(request()->hasAny(['search', 'type', 'statut']))
                    <div class="filter-group" style="justify-content: flex-end;">
                        <a href="{{ route('agent.demandes.index') }}" class="btn btn-secondary" style="height: 40px; display: inline-flex; align-items: center; padding: 0 1.5rem; font-weight: 600; text-decoration: none;">Réinitialiser</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body" style="padding:0;">
            @if($demandes->isEmpty())
                <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
                    Aucun dossier ne correspond à vos critères de recherche.
                </div>
            @else
                <div class="table-responsive">
                    <table class="bo-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--gray-50); border-bottom: 1px solid var(--gray-200); text-align: left;">
                                <th style="padding: 1rem;">ID</th>
                                <th style="padding: 1rem;">Type de demande</th>
                                <th style="padding: 1rem;">Citoyen</th>
                                <th style="padding: 1rem;">Date</th>
                                <th style="padding: 1rem;">Statut</th>
                                <th style="padding: 1rem; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($demandes as $demande)
                                <tr style="border-bottom: 1px solid var(--gray-100);">
                                    <td style="padding: 1rem;">#{{ substr($demande->id, 0, 8) }}</td>
                                    <td style="padding: 1rem;">
                                        @php
                                            $typeEnum = \App\Enums\DemandeTypeEnum::tryFrom($demande->type_demande);
                                            echo $typeEnum ? $typeEnum->label() : $demande->type_demande;
                                        @endphp
                                    </td>
                                    <td style="padding: 1rem;">
                                        <strong>{{ mb_strtoupper($demande->citoyen->nom) }}</strong> {{ $demande->citoyen->prenoms }}
                                        <div class="text-sm text-muted">NNI: {{ $demande->citoyen->nni ?? 'N/A' }}</div>
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
                                        <a href="{{ route('agent.demandes.show', $demande->id) }}" class="btn btn-secondary btn-sm">Détails</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div style="padding: 1rem; border-top: 1px solid var(--gray-200);">
            {{ $demandes->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
