@extends('layouts.app')

@section('title', 'Prendre Rendez-vous')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/demandes.css') }}?v={{ filemtime(public_path('css/demandes.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/rendezvous.css') }}?v={{ filemtime(public_path('css/rendezvous.css')) }}">
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
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="{{ route('demandes.show', $demande->id) }}" class="btn btn-outline" style="padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; border-radius: var(--radius-sm);">
                        <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span>
                    </a>
                    <h1>Prendre Rendez-vous</h1>
                </div>
                <p>Dossier #{{ substr($demande->id, 0, 8) }}... — {{ \App\Enums\DemandeTypeEnum::from($demande->type_demande)->label() }}</p>
            </div>
        </header>

        <!-- Erreurs de validation -->
        @if ($errors->any())
            <div class="alert-danger-list" style="margin-top: 1rem;">
                <strong>Veuillez corriger les erreurs suivantes :</strong>
                <ul style="margin-top: 0.5rem; padding-left: 1.2rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="rdv-form" method="POST" action="{{ route('rendezvous.store', $demande->id) }}" style="margin-top: 1rem;">
            @csrf

            <!-- Étape 1 : Lieu du consulat -->
            <section class="glass-panel panel">
                <div class="panel-header">
                    <h2>
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; color: var(--orange);">location_on</span>
                        Lieu du Rendez-vous
                    </h2>
                    <p class="panel-subtitle">Pré-sélectionné selon votre pays de résidence. Modifiez si nécessaire.</p>
                </div>
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="lieu">Ambassade / Consulat compétent <span style="color: var(--danger);">*</span></label>
                    <select name="lieu" id="lieu" class="form-control" required>
                        <option value="Consulat Général de Côte d'Ivoire à Paris" @if($lieuDefaut === "Consulat Général de Côte d'Ivoire à Paris") selected @endif>Consulat Général de Côte d'Ivoire à Paris</option>
                        <option value="Ambassade de Côte d'Ivoire à Bruxelles" @if($lieuDefaut === "Ambassade de Côte d'Ivoire à Bruxelles") selected @endif>Ambassade de Côte d'Ivoire à Bruxelles</option>
                        <option value="Ambassade de Côte d'Ivoire à Dakar" @if($lieuDefaut === "Ambassade de Côte d'Ivoire à Dakar") selected @endif>Ambassade de Côte d'Ivoire à Dakar</option>
                        <option value="Ambassade de Côte d'Ivoire à Rabat" @if($lieuDefaut === "Ambassade de Côte d'Ivoire à Rabat") selected @endif>Ambassade de Côte d'Ivoire à Rabat</option>
                        <option value="Ambassade de Côte d'Ivoire à Ottawa" @if($lieuDefaut === "Ambassade de Côte d'Ivoire à Ottawa") selected @endif>Ambassade de Côte d'Ivoire à Ottawa</option>
                        <option value="Ambassade de Côte d'Ivoire à Washington" @if($lieuDefaut === "Ambassade de Côte d'Ivoire à Washington") selected @endif>Ambassade de Côte d'Ivoire à Washington</option>
                        <option value="Ambassade de Côte d'Ivoire à Abidjan (SNEDAI)" @if($lieuDefaut === "Ambassade de Côte d'Ivoire à Abidjan (SNEDAI)") selected @endif>SNEDAI — Direction Générale d'Abidjan</option>
                    </select>
                </div>
            </section>

            <!-- Étape 2 : Date -->
            <section class="glass-panel panel" style="margin-top: 1.5rem;">
                <div class="panel-header">
                    <h2>
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; color: var(--orange);">calendar_today</span>
                        Choisir une Date
                    </h2>
                    <p class="panel-subtitle">Uniquement les jours ouvrés (lundi – vendredi) sont disponibles.</p>
                </div>
                <div class="form-group" style="margin-top: 1rem; max-width: 320px;">
                    <label for="date">Date du rendez-vous <span style="color: var(--danger);">*</span></label>
                    <input type="date" name="date" id="date" class="form-control"
                        value="{{ old('date') }}"
                        min="{{ now()->addDay()->format('Y-m-d') }}"
                        required>
                    <small style="color: var(--text-muted); font-size: 0.8rem;">Les week-ends seront refusés automatiquement.</small>
                </div>
                
                <div id="date-warning" class="alert-danger-list" style="display: none; margin-top: 1rem; padding: 0.75rem 1rem;">
                    <strong style="display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">warning</span>
                        Erreur de date :
                    </strong>
                    <span id="date-warning-msg" style="display: block; margin-top: 0.25rem;"></span>
                </div>
            </section>

            <!-- Étape 3 : Créneau horaire -->
            <section class="glass-panel panel" style="margin-top: 1.5rem;">
                <div class="panel-header">
                    <h2>
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; color: var(--orange);">schedule</span>
                        Choisir un Créneau Horaire
                    </h2>
                    <p class="panel-subtitle">Sélectionnez un créneau disponible (09:00–12:00 ou 14:00–16:30).</p>
                </div>

                <div id="slots-loading" style="display: none; margin-top: 1rem; color: var(--text-muted); font-size: 0.9rem;">
                    <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; animation: spin 1s linear infinite;">autorenew</span>
                    Vérification des créneaux disponibles…
                </div>

                <div id="slots-wrapper" style="margin-top: 1rem;">
                    <p id="slots-placeholder" style="color: var(--text-muted); font-size: 0.9rem;">
                        <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">info</span>
                        Sélectionnez d'abord une date et un lieu pour voir les créneaux disponibles.
                    </p>

                    <div class="time-slots-grid" id="time-slots-grid" style="display: none;">
                        @php
                            $allSlots = ['09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30'];
                        @endphp
                        @foreach ($allSlots as $slot)
                            <input type="radio" name="creneau" id="slot-{{ str_replace(':', '-', $slot) }}"
                                value="{{ $slot }}" class="slot-badge-input"
                                @if(old('creneau') === $slot) checked @endif>
                            <label for="slot-{{ str_replace(':', '-', $slot) }}" class="slot-badge-label">
                                {{ $slot }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- Récapitulatif & Soumission -->
            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end; padding-bottom: 2rem;">
                <a href="{{ route('demandes.show', $demande->id) }}" class="btn btn-outline" style="padding: 0.75rem 1.5rem; text-decoration: none;">Annuler</a>
                <button type="submit" id="submit-rdv-btn" class="btn btn-orange" style="padding: 0.75rem 2rem;" disabled>
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">event_available</span>
                    Confirmer le Rendez-vous
                </button>
            </div>
        </form>
    </main>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('date');
    const lieuInput = document.getElementById('lieu');
    const slotsGrid = document.getElementById('time-slots-grid');
    const slotsPlaceholder = document.getElementById('slots-placeholder');
    const slotsLoading = document.getElementById('slots-loading');
    const submitBtn = document.getElementById('submit-rdv-btn');
    const dateWarning = document.getElementById('date-warning');
    const dateWarningMsg = document.getElementById('date-warning-msg');

    function validateDate(dateVal) {
        if (!dateVal) {
            dateWarning.style.display = 'none';
            return true;
        }

        // Découper la date YYYY-MM-DD
        const parts = dateVal.split('-');
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const day = parseInt(parts[2], 10);
        const selectedDate = new Date(year, month, day);

        // Jour de la semaine (0 = dimanche, 6 = samedi)
        const dayOfWeek = selectedDate.getDay();
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            dateWarningMsg.textContent = "Les rendez-vous ne sont pas autorisés le week-end (samedi et dimanche).";
            dateWarning.style.display = 'block';
            slotsGrid.style.display = 'none';
            slotsPlaceholder.style.display = 'none';
            slotsLoading.style.display = 'none';
            submitBtn.disabled = true;
            
            // Décocher tout créneau radio éventuellement sélectionné
            const checkedRadio = document.querySelector('.slot-badge-input:checked');
            if (checkedRadio) checkedRadio.checked = false;
            
            return false;
        }

        // Comparaison par rapport à aujourd'hui à minuit (date locale)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (selectedDate <= today) {
            dateWarningMsg.textContent = "Le rendez-vous doit être programmé à une date future (à partir de demain).";
            dateWarning.style.display = 'block';
            slotsGrid.style.display = 'none';
            slotsPlaceholder.style.display = 'none';
            slotsLoading.style.display = 'none';
            submitBtn.disabled = true;
            
            const checkedRadio = document.querySelector('.slot-badge-input:checked');
            if (checkedRadio) checkedRadio.checked = false;

            return false;
        }

        dateWarning.style.display = 'none';
        return true;
    }

    function fetchOccupiedSlots() {
        const date = dateInput.value;
        const lieu = lieuInput.value;
        
        // Réinitialiser les créneaux sélectionnés et désactiver le bouton de validation
        const checkedRadio = document.querySelector('.slot-badge-input:checked');
        if (checkedRadio) {
            checkedRadio.checked = false;
        }
        submitBtn.disabled = true;

        // Validation locale préalable
        if (!validateDate(date)) {
            return;
        }

        if (!date || !lieu) return;

        slotsLoading.style.display = 'block';
        slotsGrid.style.display = 'none';
        slotsPlaceholder.style.display = 'none';

        fetch(`{{ route('rendezvous.occupied-slots') }}?date=${encodeURIComponent(date)}&lieu=${encodeURIComponent(lieu)}`)
            .then(res => res.json())
            .then(occupiedSlots => {
                slotsLoading.style.display = 'none';
                slotsGrid.style.display = 'grid';

                const labels = document.querySelectorAll('.slot-badge-label');
                labels.forEach(label => {
                    const radioId = label.getAttribute('for');
                    const radio = document.getElementById(radioId);
                    const slotValue = radio ? radio.value : null;

                    if (occupiedSlots.includes(slotValue)) {
                        label.classList.add('disabled');
                        if (radio) radio.disabled = true;
                    } else {
                        label.classList.remove('disabled');
                        if (radio) radio.disabled = false;
                    }
                });
            })
            .catch(() => {
                slotsLoading.style.display = 'none';
                slotsPlaceholder.textContent = 'Impossible de charger les créneaux. Veuillez réessayer.';
                slotsPlaceholder.style.display = 'block';
            });
    }

    dateInput.addEventListener('change', fetchOccupiedSlots);
    lieuInput.addEventListener('change', fetchOccupiedSlots);

    // Activer le bouton quand un créneau est sélectionné
    slotsGrid.addEventListener('change', () => {
        const checkedSlot = document.querySelector('.slot-badge-input:checked');
        submitBtn.disabled = !checkedSlot;
    });

    // Si date déjà remplie (reprise après erreur de validation)
    if (dateInput.value && lieuInput.value) {
        fetchOccupiedSlots();
    }
});
</script>
@endpush
@endsection
