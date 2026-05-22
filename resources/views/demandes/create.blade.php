@extends('layouts.app')

@section('title', 'Nouvelle Demande')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/demandes.css') }}">
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
                <h1>Nouvelle Demande de Document</h1>
                <p>Remplissez les détails identitaires et joignez les justificatifs demandés.</p>
            </div>
            
            <div class="user-badge">
                <span class="material-symbols-outlined">account_circle</span>
                <span>{{ Auth::user()->name ?? Auth::user()->email }}</span>
                <span class="role-tag">{{ Auth::user()->roles->first()?->name ?? 'Citoyen' }}</span>
            </div>
        </header>

        <section class="glass-panel panel" style="margin-top: 1rem;">
            @if ($errors->any())
                <div class="alert-danger-list">
                    <strong style="display: block; margin-bottom: 0.5rem;">Veuillez corriger les erreurs suivantes :</strong>
                    <ul style="padding-left: 1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-danger-list" style="background: var(--danger-light); border: 1px solid var(--danger); color: var(--danger-hover); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('demandes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Étape 1 : Type de Demande -->
                <div class="form-section-title" style="margin-top: 0;">1. Type de document demandé</div>
                <div class="type-selector-grid">
                    <!-- Option Passeport -->
                    <label class="type-option @if(old('type_demande', 'PASSEPORT') === 'PASSEPORT') active @endif" data-type="PASSEPORT">
                        <input type="radio" name="type_demande" value="PASSEPORT" @if(old('type_demande', 'PASSEPORT') === 'PASSEPORT') checked @endif>
                        <div class="type-icon">
                            <span class="material-symbols-outlined" style="font-size: 28px;">fingerprint</span>
                        </div>
                        <span class="type-title">Passeport / CNI</span>
                    </label>

                    <!-- Option État Civil -->
                    <label class="type-option @if(old('type_demande') === 'ETAT_CIVIL') active @endif" data-type="ETAT_CIVIL">
                        <input type="radio" name="type_demande" value="ETAT_CIVIL" @if(old('type_demande') === 'ETAT_CIVIL') checked @endif>
                        <div class="type-icon">
                            <span class="material-symbols-outlined" style="font-size: 28px;">description</span>
                        </div>
                        <span class="type-title">État Civil</span>
                    </label>

                    <!-- État Civil (Sous-type) -->
                    <div id="sous-type-container" class="form-group" style="display: none; grid-column: span 3; margin-top: 1rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        <label for="sous_type">Quel acte souhaitez-vous transcrire ? <span style="color: var(--danger);">*</span></label>
                        <select name="sous_type" id="sous_type" class="form-control">
                            <option value="">-- Veuillez choisir le type d'acte --</option>
                            <option value="NAISSANCE" @if(old('sous_type') === 'NAISSANCE') selected @endif>Naissance</option>
                            <option value="MARIAGE" @if(old('sous_type') === 'MARIAGE') selected @endif>Mariage</option>
                            <option value="DECES" @if(old('sous_type') === 'DECES') selected @endif>Décès</option>
                        </select>
                    </div>

                    <!-- Option Carte Consulaire -->
                    <label class="type-option @if(old('type_demande') === 'CARTE_CONSULAIRE') active @endif" data-type="CARTE_CONSULAIRE">
                        <input type="radio" name="type_demande" value="CARTE_CONSULAIRE" @if(old('type_demande') === 'CARTE_CONSULAIRE') checked @endif>
                        <div class="type-icon">
                            <span class="material-symbols-outlined" style="font-size: 28px;">badge</span>
                        </div>
                        <span class="type-title">Carte Consulaire</span>
                    </label>
                </div>

                <!-- Zone d'Importation OCR -->
                <div class="ocr-upload-zone" id="ocr-drop-zone">
                    <div class="ocr-loading" id="ocr-loader">
                        <div class="ocr-spinner"></div>
                        <p style="font-weight: 700; color: var(--orange);">Analyse de votre document en cours...</p>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Veuillez patienter quelques instants.</p>
                    </div>
                    
                    <span class="material-symbols-outlined ocr-icon">document_scanner</span>
                    <h3 class="ocr-title">💡 Gain de temps : Importez votre pièce d'identité</h3>
                    <p class="ocr-subtitle">
                        Déposez votre CNI ou Passeport ici pour <strong>pré-remplir automatiquement</strong> le formulaire grâce à l'analyse optique (OCR).
                    </p>
                    <input type="file" id="ocr-file-input" style="display: none;" accept="image/*,.pdf">
                    <button type="button" class="btn btn-outline" style="margin-top: 1rem;" onclick="document.getElementById('ocr-file-input').click()">
                        Sélectionner un fichier
                    </button>
                </div>

                <!-- Étape 2 : Informations Identitaires -->
                <div class="form-section-title">2. Informations Personnelles (Demandeur)</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom">Nom de famille (tel qu'écrit sur l'acte de naissance)</label>
                        <input type="text" name="nom" id="nom" class="form-control" value="{{ old('nom') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="prenoms">Prénoms</label>
                        <input type="text" name="prenoms" id="prenoms" class="form-control" value="{{ old('prenoms') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" name="date_naissance" id="date_naissance" class="form-control" value="{{ old('date_naissance') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="lieu_naissance">Lieu de naissance</label>
                        <input type="text" name="lieu_naissance" id="lieu_naissance" class="form-control" value="{{ old('lieu_naissance') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <select name="genre" id="genre" class="form-control" required>
                            <option value="M" @if(old('genre') === 'M') selected @endif>Masculin (M)</option>
                            <option value="F" @if(old('genre') === 'F') selected @endif>Féminin (F)</option>
                        </select>
                    </div>

                    <div class="form-group" id="nni-container">
                        <label for="nni">Numéro National d'Identification (NNI) <span class="nni-required" style="color: var(--danger);">*</span></label>
                        <input type="text" name="nni" id="nni" class="form-control" value="{{ old('nni') }}" placeholder="Ex : 1029384756">
                    </div>
                </div>

                <!-- Étape 3 : Coordonnées et Résidence -->
                <div class="form-section-title">3. Coordonnées & Résidence Actuelle</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="pays_residence">Pays de résidence</label>
                        <input type="text" name="pays_residence" id="pays_residence" class="form-control" value="{{ old('pays_residence', 'France') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="adresse_residence">Adresse complète (Rue, Ville, Code Postal)</label>
                        <input type="text" name="adresse_residence" id="adresse_residence" class="form-control" value="{{ old('adresse_residence') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="telephone">Numéro de téléphone actif</label>
                        <input type="tel" name="telephone" id="telephone" class="form-control" value="{{ old('telephone') }}" placeholder="Ex : +33 6 12 34 56 78" required>
                    </div>
                </div>

                <!-- Étape 4 : Téléversement des Pièces Justificatives -->
                <div class="form-section-title">4. Pièces Justificatives (PDF, JPG, PNG, Max 5 Mo par fichier)</div>
                
                <!-- Conteneur Pièces Passeport -->
                <div class="form-grid doc-group-fields" id="docs-PASSEPORT">
                    <div class="document-upload-card">
                        <h5>Extrait de naissance <span style="color: var(--danger);">*</span></h5>
                        <p>Copie lisible de l'acte de naissance original.</p>
                        <input type="file" name="extrait_naissance" class="form-control">
                    </div>
                    <div class="document-upload-card">
                        <h5>Certificat de nationalité ivoirienne <span style="color: var(--danger);">*</span></h5>
                        <p>Document officiel attestant de la nationalité.</p>
                        <input type="file" name="certificat_nationalite" class="form-control">
                    </div>
                    <div class="document-upload-card">
                        <h5>Justificatif de domicile consulaire <span style="color: var(--danger);">*</span></h5>
                        <p>Facture d'électricité, de gaz ou quittance de loyer récente.</p>
                        <input type="file" name="justificatif_domicile" class="form-control">
                    </div>
                    <div class="document-upload-card">
                        <h5>Photo d'identité <span style="color: var(--danger);">*</span></h5>
                        <p>Format d'identité standard sur fond blanc neutre (JPG/PNG).</p>
                        <input type="file" name="photo" class="form-control">
                    </div>
                </div>

                <!-- Conteneur Pièces État Civil -->
                <div class="form-grid doc-group-fields" id="docs-ETAT_CIVIL" style="display: none;">
                    <div class="document-upload-card">
                        <h5>Copie intégrale de l'acte étranger <span style="color: var(--danger);">*</span></h5>
                        <p>L'acte original étranger à transcrire.</p>
                        <input type="file" name="acte_etranger" class="form-control" disabled>
                    </div>
                    
                    <div class="document-upload-card doc-field-NAISSANCE" style="display: none;">
                        <h5>Pièce d'identité des parents <span style="color: var(--danger);">*</span></h5>
                        <input type="file" name="piece_identite_parents" class="form-control" disabled>
                    </div>
                    
                    <div class="document-upload-card doc-field-MARIAGE" style="display: none;">
                        <h5>Pièce d'identité époux ivoirien <span style="color: var(--danger);">*</span></h5>
                        <input type="file" name="piece_identite_epoux_ivoirien" class="form-control" disabled>
                    </div>
                    <div class="document-upload-card doc-field-MARIAGE" style="display: none;">
                        <h5>Pièce d'identité conjoint <span style="color: var(--danger);">*</span></h5>
                        <input type="file" name="piece_identite_conjoint" class="form-control" disabled>
                    </div>
                    
                    <div class="document-upload-card doc-field-DECES" style="display: none;">
                        <h5>Pièce d'identité défunt <span style="color: var(--danger);">*</span></h5>
                        <input type="file" name="piece_identite_defunt" class="form-control" disabled>
                    </div>
                    <div class="document-upload-card doc-field-DECES" style="display: none;">
                        <h5>Pièce d'identité déclarant <span style="color: var(--danger);">*</span></h5>
                        <input type="file" name="piece_identite_declarant" class="form-control" disabled>
                    </div>

                    <div class="document-upload-card">
                        <h5>Demande écrite signée <span style="color: var(--danger);">*</span></h5>
                        <input type="file" name="demande_ecrite" class="form-control" disabled>
                    </div>
                </div>

                <!-- Conteneur Pièces Carte Consulaire -->
                <div class="form-grid doc-group-fields" id="docs-CARTE_CONSULAIRE" style="display: none;">
                    <div class="form-group" style="grid-column: span 2; margin-bottom: 1rem;">
                        <label for="mode_identification">Mode d'identification ivoirienne <span style="color: var(--danger);">*</span></label>
                        <select name="mode_identification" id="mode_identification" class="form-control" disabled>
                            <option value="cni_passport" @if(old('mode_identification', 'cni_passport') === 'cni_passport') selected @endif>Carte Nationale d'Identité (CNI) ou Passeport ivoirien en cours de validité</option>
                            <option value="extrait_nationalite" @if(old('mode_identification') === 'extrait_nationalite') selected @endif>Extrait d'acte de naissance + Certificat de nationalité</option>
                        </select>
                    </div>

                    <!-- Carte/Passeport en cours de validité (Conditionnel) -->
                    <div class="document-upload-card" id="card-cni-passport">
                        <h5>CNI ou passeport ivoirien valide <span style="color: var(--danger);">*</span></h5>
                        <p>Copie de votre pièce d'identité ivoirienne en cours de validité.</p>
                        <input type="file" name="cni_ou_passeport" class="form-control" disabled>
                    </div>

                    <!-- Extrait de naissance (Conditionnel) -->
                    <div class="document-upload-card" id="card-extrait-naissance" style="display: none;">
                        <h5>Extrait d'acte de naissance <span style="color: var(--danger);">*</span></h5>
                        <p>Copie de l'acte de naissance original.</p>
                        <input type="file" name="extrait_naissance" class="form-control" disabled>
                    </div>

                    <!-- Certificat de nationalité (Conditionnel) -->
                    <div class="document-upload-card" id="card-certificat-nationalite" style="display: none;">
                        <h5>Certificat de nationalité ivoirienne <span style="color: var(--danger);">*</span></h5>
                        <p>Document officiel attestant de la nationalité ivoirienne.</p>
                        <input type="file" name="certificat_nationalite" class="form-control" disabled>
                    </div>

                    <!-- Communs -->
                    <div class="document-upload-card">
                        <h5>Justificatif de domicile récent <span style="color: var(--danger);">*</span></h5>
                        <p>Facture d'énergie ou bail de location de moins de 3 mois dans le pays d'accueil.</p>
                        <input type="file" name="justificatif_domicile" class="form-control" disabled>
                    </div>
                    <div class="document-upload-card">
                        <h5>Photo d'identité <span style="color: var(--danger);">*</span></h5>
                        <p>Format d'identité standard sur fond blanc neutre (JPG/PNG).</p>
                        <input type="file" name="photo" class="form-control" disabled>
                    </div>
                    <div class="document-upload-card">
                        <h5>Reçu de paiement consulaire (10 €) <span style="color: var(--danger);">*</span></h5>
                        <p>Reçu de paiement des droits de chancellerie.</p>
                        <input type="file" name="recu_paiement" class="form-control" disabled>
                    </div>
                </div>

                <!-- Soumission -->
                <div style="margin-top: 3rem; display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline" style="padding: 0.75rem 1.5rem; text-decoration: none;">Annuler</a>
                    <button type="submit" class="btn btn-orange" style="padding: 0.75rem 2rem;">Soumettre mon dossier</button>
                </div>
            </form>
        </section>
    </main>
</div>

<!-- Scripts pour l'interactivité dynamique -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const options = document.querySelectorAll('.type-option');
    const docGroups = document.querySelectorAll('.doc-group-fields');
    const nniContainer = document.getElementById('nni-container');
    const nniInput = document.getElementById('nni');
    const nniRequiredLabel = document.querySelector('.nni-required');
    const modeIdentificationSelect = document.getElementById('mode_identification');

    function updateCarteConsulaireFields() {
        const mode = modeIdentificationSelect.value;
        const cniPassportCard = document.getElementById('card-cni-passport');
        const extraitCard = document.getElementById('card-extrait-naissance');
        const nationaliteCard = document.getElementById('card-certificat-nationalite');

        const cniPassportInput = cniPassportCard.querySelector('input[type="file"]');
        const extraitInput = extraitCard.querySelector('input[type="file"]');
        const nationaliteInput = nationaliteCard.querySelector('input[type="file"]');

        const isCarteConsulaireActive = document.querySelector('input[name="type_demande"]:checked')?.value === 'CARTE_CONSULAIRE';

        if (mode === 'cni_passport') {
            cniPassportCard.style.display = 'block';
            if (isCarteConsulaireActive) {
                cniPassportInput.removeAttribute('disabled');
            }
            
            extraitCard.style.display = 'none';
            extraitInput.setAttribute('disabled', 'true');
            
            nationaliteCard.style.display = 'none';
            nationaliteInput.setAttribute('disabled', 'true');
        } else {
            cniPassportCard.style.display = 'none';
            cniPassportInput.setAttribute('disabled', 'true');
            
            extraitCard.style.display = 'block';
            if (isCarteConsulaireActive) {
                extraitInput.removeAttribute('disabled');
            }
            
            nationaliteCard.style.display = 'block';
            if (isCarteConsulaireActive) {
                nationaliteInput.removeAttribute('disabled');
            }
        }
    }

    modeIdentificationSelect.addEventListener('change', updateCarteConsulaireFields);

    const sousTypeSelect = document.getElementById('sous_type');
    const sousTypeContainer = document.getElementById('sous-type-container');

    function updateEtatCivilFields() {
        const sousType = sousTypeSelect.value;
        const fields = document.querySelectorAll('.doc-field-NAISSANCE, .doc-field-MARIAGE, .doc-field-DECES');
        
        fields.forEach(f => {
            f.style.display = 'none';
            f.querySelector('input').setAttribute('disabled', 'true');
        });

        if (sousType) {
            const activeFields = document.querySelectorAll(`.doc-field-${sousType}`);
            activeFields.forEach(f => {
                f.style.display = 'block';
                f.querySelector('input').removeAttribute('disabled');
            });
        }
    }

    sousTypeSelect.addEventListener('change', updateEtatCivilFields);

    options.forEach(option => {
        option.addEventListener('click', () => {
            // Désactiver toutes les options graphiques
            options.forEach(o => o.classList.remove('active'));
            // Activer l'option sélectionnée
            option.classList.add('active');

            const radio = option.querySelector('input[type="radio"]');
            radio.checked = true;
            
            const selectedType = radio.value;

            if (selectedType === 'ETAT_CIVIL') {
                sousTypeContainer.style.display = 'block';
            } else {
                sousTypeContainer.style.display = 'none';
            }

            // Afficher le bon groupe de documents justificatifs et désactiver les autres pour le validator Laravel
            docGroups.forEach(group => {
                if (group.id === `docs-${selectedType}`) {
                    group.style.display = 'grid';
                    if (selectedType !== 'ETAT_CIVIL') {
                        group.querySelectorAll('input, select').forEach(input => {
                            input.removeAttribute('disabled');
                        });
                    } else {
                        // Pour état civil, on laisse la logique updateEtatCivilFields gérer les inputs
                        group.querySelector('input[name="acte_etranger"]').removeAttribute('disabled');
                        group.querySelector('input[name="demande_ecrite"]').removeAttribute('disabled');
                        updateEtatCivilFields();
                    }
                } else {
                    group.style.display = 'none';
                    group.querySelectorAll('input, select').forEach(input => {
                        input.setAttribute('disabled', 'true');
                    });
                }
            });

            // Ajuster les champs Carte Consulaire
            if (selectedType === 'CARTE_CONSULAIRE') {
                updateCarteConsulaireFields();
            }

            // Ajuster l'obligation du NNI
            if (selectedType === 'PASSEPORT') {
                nniInput.setAttribute('required', 'true');
                nniRequiredLabel.style.display = 'inline';
            } else {
                nniInput.removeAttribute('required');
                nniRequiredLabel.style.display = 'none';
            }
        });
    });

    // Déclencher le changement de type initial au chargement pour la reprise des données (Old input)
    const activeRadio = document.querySelector('input[name="type_demande"]:checked');
    if (activeRadio) {
        const parentLabel = activeRadio.closest('.type-option');
        if (parentLabel) parentLabel.click();
    }
    }

    // --- LOGIQUE OCR ---
    const dropZone = document.getElementById('ocr-drop-zone');
    const fileInput = document.getElementById('ocr-file-input');
    const loader = document.getElementById('ocr-loader');

    // Drag & Drop handlers
    dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragging');
    });

    ['dragleave', 'drop'].forEach(evt => {
    dropZone.addEventListener(evt, () => dropZone.classList.remove('dragging'));
    });

    dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    const files = e.dataTransfer.files;
    if (files.length) handleOcrFile(files[0]);
    });

    fileInput.addEventListener('change', (e) => {
    if (e.target.files.length) handleOcrFile(e.target.files[0]);
    });

    async function handleOcrFile(file) {
    const formData = new FormData();
    formData.append('document', file);
    formData.append('_token', '{{ csrf_token() }}');

    loader.style.display = 'flex';

    try {
        const response = await fetch('{{ route('demandes.ocr-analyze') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            fillFormWithOcrData(result.data, file);
        } else {
            alert(result.message || 'Une erreur est survenue lors de l\'analyse.');
        }
    } catch (error) {
        console.error('OCR Error:', error);
        alert('Erreur technique lors de l\'analyse du document.');
    } finally {
        loader.style.display = 'none';
    }
    }

    function fillFormWithOcrData(data, file) {
    const fields = ['nom', 'prenoms', 'date_naissance', 'lieu_naissance', 'genre', 'nni'];

    fields.forEach(field => {
        const input = document.getElementById(field);
        if (input && data[field]) {
            input.value = data[field];
            // Animation de succès
            input.classList.add('pulse-success');
            setTimeout(() => input.classList.remove('pulse-success'), 2000);
        }
    });

    // Liaison automatique du fichier aux pièces justificatives si possible
    // Si c'est un passeport ou une CNI, on essaie de le mettre dans le champ correspondant
    const selectedType = document.querySelector('input[name="type_demande"]:checked').value;

    if (selectedType === 'CARTE_CONSULAIRE') {
        const cniPassportInput = document.querySelector('input[name="cni_ou_passeport"]');
        if (cniPassportInput && !cniPassportInput.disabled) {
            syncFileInput(cniPassportInput, file);
        }
    } else if (selectedType === 'PASSEPORT') {
        // Pour le passeport, on ne sait pas forcément quel champ remplir par défaut avec l'OCR (souvent c'est l'extrait de naissance qui est demandé)
        // Mais on peut laisser l'utilisateur choisir ou remplir le premier champ vide
    }

    if (data.message) {
        // Optionnel : afficher un petit toast de succès
        console.log('OCR Info:', data.message);
    }
    }

    function syncFileInput(input, file) {
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    input.files = dataTransfer.files;

    // Visuel pour montrer que le fichier est lié
    const card = input.closest('.document-upload-card');
    if (card) {
        card.style.borderColor = 'var(--green)';
        card.style.background = 'rgba(34, 197, 94, 0.05)';
    }
    }
    });
    </script>

