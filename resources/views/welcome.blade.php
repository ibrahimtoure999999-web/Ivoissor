<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ivoissor — Portail Consulaire de la République de Côte d'Ivoire</title>
    <meta name="description" content="Plateforme officielle et sécurisée pour la gestion des demandes de passeports, cartes consulaires et actes d'état civil.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body>

<!-- ══════════ NAVBAR ══════════ -->
<nav class="navbar">
    <div class="container">
        <a href="/" class="navbar-brand">Ivoissor<span>.</span></a>
        <ul class="navbar-links">
            <li><a href="#services">Démarches en ligne</a></li>
            <li><a href="#guide">Comment ça marche</a></li>
            <li><a href="#contact">Contact & Consulats</a></li>
        </ul>
        <div class="navbar-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-green">
                    <span class="material-symbols-outlined">dashboard</span>
                    Espace Citoyen
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-outline">Déconnexion</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline">Se connecter</a>
                <a href="{{ route('register') }}" class="btn btn-orange">Créer un compte</a>
            @endauth
        </div>
    </div>
</nav>

<!-- ══════════ HERO SECTION ══════════ -->
<section class="hero">
    <div class="container">
        <div class="hero-left">
            <div class="hero-badge">
                <span class="material-symbols-outlined" style="font-size: 16px;">gavel</span>
                Portail Officiel de la République de Côte d'Ivoire
            </div>
            <h1>Le Portail de Vos <span class="highlight">Démarches Consulaires</span></h1>
            <p class="hero-subtitle">
                Gérez vos demandes de passeport biométrique, de carte consulaire et d'actes d'état civil en ligne en toute simplicité et suivez leur avancement en temps réel.
            </p>
            
            <!-- Tracking Widget (Inspiration e-justice.ci) -->
            <div class="hero-tracking-widget">
                <div class="tracking-input-wrapper">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" id="dossier-search-input" placeholder="Entrez votre numéro de dossier... (ex: IV-2026-TEST)">
                </div>
                <button type="button" id="dossier-search-btn" class="btn btn-orange">Suivre</button>
            </div>
            
            <div class="hero-actions-hint">
                <span class="material-symbols-outlined" style="font-size: 18px; color: var(--green);">info</span>
                <span>Débutez directement une démarche ? <a href="{{ route('register') }}">Inscrivez-vous ici</a></span>
            </div>
        </div>
        
        <!-- Right Side Mockup Graphic -->
        <div class="hero-mockup-panel">
            <div class="mockup-card">
                <div class="mockup-header">
                    <div>
                        <h4 style="font-family: 'Montserrat', sans-serif; font-size: 1rem; color: var(--text-primary);">Suivi interactif</h4>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Dossier Consulaire</span>
                    </div>
                    <span class="material-symbols-outlined" style="color: var(--orange); font-size: 28px;">badge</span>
                </div>
                <div class="mockup-step-list">
                    <div class="mockup-step completed">
                        <div class="mockup-circle">
                            <span class="material-symbols-outlined" style="font-size: 16px;">done</span>
                        </div>
                        <span class="mockup-label">Création du dossier</span>
                    </div>
                    <div class="mockup-step completed">
                        <div class="mockup-circle">
                            <span class="material-symbols-outlined" style="font-size: 16px;">done</span>
                        </div>
                        <span class="mockup-label">Soumission & Paiement</span>
                    </div>
                    <div class="mockup-step active">
                        <div class="mockup-circle">3</div>
                        <span class="mockup-label">Instruction par l'agent</span>
                    </div>
                    <div class="mockup-step">
                        <div class="mockup-circle">4</div>
                        <span class="mockup-label">Délivrance / Retrait</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════ INTERACTIVE TRACKING RESULT PANEL ══════════ -->
<section class="tracker-result-section" id="tracker-result-section">
    <div class="container">
        <div class="tracker-result-box glass-panel" style="padding: 2.5rem; border-radius: var(--radius-md);">
            <div class="tracker-result-header">
                <div>
                    <h3 style="font-family: 'Montserrat', sans-serif; font-size: 1.4rem; color: var(--text-primary);" id="result-dossier-id">Dossier #IV-2026-TEST</h3>
                    <span style="font-size: 0.85rem; color: var(--text-secondary);" id="result-date">Soumis le 20 Mai 2026 au Consulat de Paris</span>
                </div>
                <span class="tracker-badge" id="result-status-badge">Instruction en cours</span>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <p style="font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Demandeur : <strong id="result-name" style="color: var(--text-primary);">KOFFI Kouamé Jean</strong></p>
                <p style="font-size: 0.95rem; color: var(--text-secondary);">Type de demande : <strong id="result-type" style="color: var(--text-primary);">Passeport Biométrique (Renouvellement)</strong></p>
            </div>

            <!-- Progression Tracker Graphic -->
            <div class="dashboard-tracker-steps" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; position: relative; margin-bottom: 2.5rem; padding-top: 1rem;">
                <div class="step-col finished" style="text-align: center;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: var(--success); margin: 0 auto 8px; box-shadow: 0 0 0 4px var(--success-light);"></div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-primary); display: block;">Création</span>
                </div>
                <div class="step-col finished" style="text-align: center;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: var(--success); margin: 0 auto 8px; box-shadow: 0 0 0 4px var(--success-light);"></div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-primary); display: block;">Soumission</span>
                </div>
                <div class="step-col in-progress" id="step-instruction" style="text-align: center;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: var(--orange); margin: 0 auto 8px; box-shadow: 0 0 0 4px var(--orange-glow);"></div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--orange-hover); display: block;">Instruction</span>
                </div>
                <div class="step-col upcoming" id="step-delivery" style="text-align: center;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: var(--border-color); margin: 0 auto 8px;"></div>
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block;">Délivrance</span>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined" style="color: var(--orange);">notifications_active</span>
                    <span style="font-size: 0.9rem; color: var(--text-secondary);" id="result-desc">Vos pièces justificatives ont été reçues. Rendez-vous prévu le 26 Mai 2026 à 10:00.</span>
                </div>
                <button type="button" class="btn btn-outline" id="dossier-close-btn" style="padding: 0.5rem 1.25rem;">Masquer</button>
            </div>
        </div>
    </div>
</section>

<!-- ══════════ SERVICES SECTION (e-justice.ci style) ══════════ -->
<section class="services-section" id="services">
    <div class="container">
        <div class="section-header">
            <h2>Démarches Consulaires En Ligne</h2>
            <p>Sélectionnez une démarche pour consulter la liste des pièces requises et initier votre dossier en toute sécurité.</p>
        </div>
        
        <div class="services-grid">
            
            <!-- Service 1: Passeport / CNI -->
            <div class="service-card orange">
                <div>
                    <div class="service-icon-box orange">
                        <span class="material-symbols-outlined">fingerprint</span>
                    </div>
                    <h3>Passeport & Carte d'Identité</h3>
                    <p class="service-description">Demande de premier titre ou renouvellement de passeport biométrique et carte nationale d'identité ivoirienne.</p>
                    
                    <div class="required-files-box">
                        <h4>Pièces requises à préparer :</h4>
                        <ul class="required-files-list">
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Extrait de naissance original</li>
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Certificat de Nationalité Ivoirienne</li>
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Justificatif de domicile consulaire</li>
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>2 photos d'identité récentes</li>
                        </ul>
                    </div>
                </div>
                <a href="{{ route('register') }}" class="btn btn-orange" style="width: 100%;">Faire la demande</a>
            </div>
            
            <!-- Service 2: État Civil -->
            <div class="service-card green">
                <div>
                    <div class="service-icon-box green">
                        <span class="material-symbols-outlined">description</span>
                    </div>
                    <h3>Transcription d'État Civil</h3>
                    <p class="service-description">Transcription d'actes de naissance, de mariage ou de décès dressés à l'étranger dans les registres ivoiriens.</p>
                    
                    <div class="required-files-box">
                        <h4>Pièces requises à préparer :</h4>
                        <ul class="required-files-list">
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Copie intégrale de l'acte étranger</li>
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Pièces d'identité des parents / conjoints</li>
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Livret de famille ivoirien (le cas échéant)</li>
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Demande écrite signée</li>
                        </ul>
                    </div>
                </div>
                <a href="{{ route('register') }}" class="btn btn-green" style="width: 100%;">Faire la demande</a>
            </div>
            
            <!-- Service 3: Carte Consulaire -->
            <div class="service-card orange">
                <div>
                    <div class="service-icon-box orange">
                        <span class="material-symbols-outlined">badge</span>
                    </div>
                    <h3>Carte Consulaire de Résidence</h3>
                    <p class="service-description">Inscription au registre des ivoiriens résidant à l'étranger pour obtenir votre carte de résident consulaire.</p>
                    
                    <div class="required-files-box">
                        <h4>Pièces requises à préparer :</h4>
                        <ul class="required-files-list">
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Justificatif de séjour valide à l'étranger</li>
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Passeport ou CNI ivoirienne</li>
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Justificatif d'activité ou de profession</li>
                            <li><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>Justificatif de domicile récent</li>
                        </ul>
                    </div>
                </div>
                <a href="{{ route('register') }}" class="btn btn-orange" style="width: 100%;">Faire la demande</a>
            </div>
            
        </div>
    </div>
</section>

<!-- ══════════ GUIDE / COMMENT ÇA MARCHE SECTION ══════════ -->
<section class="guide-section" id="guide">
    <div class="container">
        <div class="section-header">
            <h2>Votre Dossier en 3 Étapes Simples</h2>
            <p>Une démarche simplifiée, rapide, et entièrement sécurisée pour la diaspora.</p>
        </div>
        
        <div class="guide-steps-grid">
            <div class="guide-step">
                <div class="guide-circle">1</div>
                <h4>Création du compte</h4>
                <p>Inscrivez-vous de manière sécurisée en renseignant vos informations d'identité.</p>
            </div>
            <div class="guide-step">
                <div class="guide-circle">2</div>
                <h4>Soumission du dossier</h4>
                <p>Sélectionnez votre service, téléversez vos justificatifs et payez les frais consulaires.</p>
            </div>
            <div class="guide-step">
                <div class="guide-circle">3</div>
                <h4>Prenez rendez-vous</h4>
                <p>Choisissez votre créneau horaire et votre consulat pour la validation biométrique finale.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════ FOOTER SECTION ══════════ -->
<footer class="footer" id="contact">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-info">
                <h3>Ivoissor<span>.</span></h3>
                <p>Portail national unifié pour la numérisation et la facilitation des démarches consulaires de la diaspora ivoirienne dans le monde entier.</p>
                <div class="footer-logo-ci">
                    <span style="border-left: 6px solid var(--orange); border-right: 6px solid var(--green); padding: 0 10px; font-weight: 700; color: #FFF;">RÉPUBLIQUE DE CÔTE D'IVOIRE</span>
                </div>
            </div>
            <div class="footer-links-col">
                <h4>Services</h4>
                <ul class="footer-menu">
                    <li><a href="#services">Passeport Biométrique</a></li>
                    <li><a href="#services">Carte Consulaire</a></li>
                    <li><a href="#services">Actes d'État Civil</a></li>
                    <li><a href="#guide">Prendre Rendez-vous</a></li>
                </ul>
            </div>
            <div class="footer-links-col">
                <h4>Informations</h4>
                <ul class="footer-menu">
                    <li><a href="#">Ministère des Affaires Étrangères</a></li>
                    <li><a href="#">Consulats Partenaires</a></li>
                    <li><a href="#">Conditions d'utilisation</a></li>
                    <li><a href="#">Sécurité & RGPD</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2026 Ivoissor. Tous droits réservés. République de Côte d'Ivoire.</span>
            <span>Sécurisé par l'État Ivoirien</span>
        </div>
    </div>
</footer>

<!-- ══════════ INTERACTIVE TRACKING SCRIPT ══════════ -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchBtn = document.getElementById('dossier-search-btn');
    const closeBtn = document.getElementById('dossier-close-btn');
    const inputField = document.getElementById('dossier-search-input');
    const resultSection = document.getElementById('tracker-result-section');
    
    // Result elements
    const resultId = document.getElementById('result-dossier-id');
    const resultName = document.getElementById('result-name');
    const resultType = document.getElementById('result-type');
    const resultDate = document.getElementById('result-date');
    const resultStatusBadge = document.getElementById('result-status-badge');
    const resultDesc = document.getElementById('result-desc');
    const stepInstruction = document.getElementById('step-instruction');
    const stepDelivery = document.getElementById('step-delivery');

    // Simulate search logic
    const handleSearch = () => {
        const query = inputField.value.trim().toUpperCase();
        
        if (!query) {
            alert('Veuillez entrer un numéro de dossier.');
            return;
        }

        // Show result section
        resultSection.style.display = 'block';
        
        // Mock data logic
        if (query === 'IV-2026-TEST') {
            resultId.textContent = 'Dossier #IV-2026-TEST';
            resultName.textContent = 'KOFFI Kouamé Jean';
            resultType.textContent = 'Passeport Biométrique (Renouvellement)';
            resultDate.textContent = 'Soumis le 20 Mai 2026 au Consulat de Paris';
            resultStatusBadge.textContent = 'Instruction en cours';
            resultStatusBadge.className = 'tracker-badge';
            resultStatusBadge.style.backgroundColor = 'var(--orange-light)';
            resultStatusBadge.style.color = 'var(--orange-hover)';
            resultDesc.textContent = 'Vos pièces justificatives ont été reçues. Rendez-vous prévu le 26 Mai 2026 à 10:00.';
            
            // Set stepper visual state
            stepInstruction.className = 'step-col in-progress';
            stepInstruction.querySelector('div').style.backgroundColor = 'var(--orange)';
            stepInstruction.querySelector('div').style.boxShadow = '0 0 0 4px var(--orange-glow)';
            
            stepDelivery.className = 'step-col upcoming';
            stepDelivery.querySelector('div').style.backgroundColor = 'var(--border-color)';
            stepDelivery.querySelector('div').style.boxShadow = 'none';
        } else if (query.startsWith('IV-2026-') && query.length > 10) {
            // General simulation for any custom ID entered by the user
            resultId.textContent = `Dossier #${query}`;
            resultName.textContent = 'Aké Loba Stéphane';
            resultType.textContent = 'Carte Consulaire de Résidence';
            resultDate.textContent = 'Soumis le 18 Mai 2026 au Consulat de Lyon';
            resultStatusBadge.textContent = 'Validé / Délivré';
            resultStatusBadge.className = 'tracker-badge success';
            resultStatusBadge.style.backgroundColor = 'var(--success-light)';
            resultStatusBadge.style.color = 'var(--success-hover)';
            resultDesc.textContent = 'Votre carte consulaire a été validée et imprimée. Vous pouvez la retirer au guichet.';
            
            // Set stepper visual state to fully completed
            stepInstruction.className = 'step-col finished';
            stepInstruction.querySelector('div').style.backgroundColor = 'var(--success)';
            stepInstruction.querySelector('div').style.boxShadow = '0 0 0 4px var(--success-light)';
            
            stepDelivery.className = 'step-col finished';
            stepDelivery.querySelector('div').style.backgroundColor = 'var(--success)';
            stepDelivery.querySelector('div').style.boxShadow = '0 0 0 4px var(--success-light)';
            stepDelivery.querySelector('span').style.color = 'var(--success-hover)';
            stepDelivery.querySelector('span').style.fontWeight = '700';
        } else {
            // Unrecognized custom code fallback
            resultId.textContent = `Dossier #${query}`;
            resultName.textContent = 'Citoyen Ivoirien';
            resultType.textContent = 'Démarche Consulaire Générale';
            resultDate.textContent = 'Créé récemment';
            resultStatusBadge.textContent = 'Dossier introuvable';
            resultStatusBadge.className = 'tracker-badge';
            resultStatusBadge.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
            resultStatusBadge.style.color = '#EF4444';
            resultDesc.textContent = 'Aucun dossier actif ne correspond exactement à cette référence. Veuillez vérifier le code.';
            
            // Reset stepper visual state to draft
            stepInstruction.className = 'step-col upcoming';
            stepInstruction.querySelector('div').style.backgroundColor = 'var(--border-color)';
            stepInstruction.querySelector('div').style.boxShadow = 'none';
            stepDelivery.className = 'step-col upcoming';
            stepDelivery.querySelector('div').style.backgroundColor = 'var(--border-color)';
            stepDelivery.querySelector('div').style.boxShadow = 'none';
        }

        // Smooth scroll to the result card
        resultSection.scrollIntoView({ behavior: 'smooth' });
    };

    // Listeners
    searchBtn.addEventListener('click', handleSearch);
    inputField.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });

    closeBtn.addEventListener('click', () => {
        resultSection.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
</script>

</body>
</html>