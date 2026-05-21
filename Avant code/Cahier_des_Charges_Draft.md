# **Cahier des Charges Fonctionnel (Final - Version Master)**
## **Projet : Solution Informatique de Centralisation des Données des Ressortissants Ivoiriens**

---

### **1. Introduction**

#### **1.1 Objet du document**
Ce document a pour but de définir les spécifications fonctionnelles et techniques complètes de l'application dédiée au recensement, à l'identification et à la centralisation des données des ressortissants ivoiriens (notamment la diaspora). Il sert de base pour la conception et le développement du prototype.

#### **1.2 Portée du projet**
Le projet consiste à développer une application (hybride web et mobile PWA) agissant comme un guichet unique. Elle permettra aux citoyens de préparer leurs démarches d'identification à distance et à l'administration consulaire de valider ces données avant synchronisation avec le Registre National des Personnes Physiques (RNPP).

---

### **2. Description Générale**

#### **2.1 Perspective du produit**
L'application est un système "Front-Office" destiné aux citoyens. Elle s'interface (ou est censée s'interfacer via des API sécurisées) avec le système "Back-Office" de l'État (RNPP / ONECI) si nous avons accès à leurs API. Dans le cas contraire, nous utiliserons une base de données locale sécurisée pour stocker les données des citoyens (à des fins de démonstration).

#### **2.2 Classes et caractéristiques des usagers**
*   **Le Citoyen (Ressortissant) :** Utilise l'application pour s'enregistrer, téléverser des documents, demander des transcriptions et prendre rendez-vous.
*   **L'Agent Consulaire :** Utilise l'interface d'administration pour vérifier les documents, traiter les transcriptions et valider les dossiers.
*   **L'Administrateur Système :** Gère les comptes, les rôles (RBAC) et veille à la sécurité et à l'auditabilité du système.
*   **Le Décideur (Ministère/ONECI) :** Consulte les tableaux de bord statistiques (Business Intelligence).

---

### **3. Besoins Fonctionnels (Spécifications des modules)**

#### **3.1 Module Citoyen (Interface Web/Mobile PWA)**
*   **F-01 : Création de compte et Authentification**
    *   L'utilisateur doit pouvoir créer un compte et s'authentifier de manière sécurisée.
*   **F-02 : Pré-enrôlement (Saisie biographique)**
    *   L'utilisateur doit pouvoir remplir un formulaire numérique avec ses données d'état civil pour l'attribution du NNI.
*   **F-03 : Demande de Transcription d'État Civil**
    *   L'utilisateur doit pouvoir soumettre une demande de transcription pour un acte (naissance, mariage, décès) survenu à l'étranger.
*   **F-04 : Téléversement documentaire et OCR (avec Plan B)**
    *   L'utilisateur peut téléverser ses justificatifs. Le système applique un traitement OCR pour pré-remplir les champs. *En cas d'échec ou d'erreur de lecture de l'OCR, la saisie manuelle corrective reste possible.*
*   **F-05 : Prise de Rendez-vous**
    *   Sélection d'un créneau pour la capture des données biométriques (incontournable pour la validation finale).
*   **F-06 : Suivi et Notifications multicanaux**
    *   Tableau de bord pour suivre l'état de la demande. Les notifications de changement de statut sont envoyées par **Email** (et optionnellement par SMS via une API tierce).
*   **F-07 : Paiement en ligne (Réintégré)**
    *   Intégration d'une passerelle de paiement sécurisée pour le règlement des droits de chancellerie afin de limiter les flux d'espèces.

#### **3.2 Module Agent Consulaire (Back-Office)**
*   **F-08 : Traitement des dossiers et OCR**
    *   L'agent visualise les dossiers et les données extraites par OCR pour validation.
*   **F-09 : Validation des Transcriptions**
    *   Module spécifique pour valider les actes d'état civil consulaire.
*   **F-10 : Gestion du planning**
    *   Configuration des plages horaires pour les rendez-vous physiques.

#### **3.3 Module Administrateur Système (Gestion & Sécurité)**
*   **F-11 : Gestion des rôles (RBAC)**
    *   Création et attribution des rôles (Agent, Superviseur, Admin).
*   **F-12 : Gestion des comptes agents**
    *   Création et désactivation des comptes du personnel consulaire.
*   **F-13 : Consultation des Journaux d'Audit (Logs)**
    *   Accès aux logs immuables traçant toutes les actions sensibles.

#### **3.4 Module Business Intelligence (Statistiques)**
*   **F-14 : Tableaux de bord décisionnels**
    *   Génération de statistiques sur la diaspora (répartition géographique, pyramide des âges).
*   **F-15 : Suivi des indicateurs (KPI)**
    *   Temps moyen de traitement des dossiers, taux d'enrôlement.

---

### **4. Besoins Non-Fonctionnels**

#### **4.1 Sécurité, Confidentialité et Souveraineté**
*   **NF-01 : Chiffrement de bout en bout**
    *   Protocoles TLS 1.3 en transit et chiffrement AES-256 au repos.
*   **NF-02 : Localisation des données**
    *   Pour garantir la souveraineté, les serveurs hébergeant les données d'identité doivent être localisés sur le territoire national ivoirien.
*   **NF-03 : Traçabilité (Audit Trail)**
    *   Chaque action (validation, rejet, modification) doit être logguée avec horodatage immuable et adresse IP de l'agent.
*   **NF-04 : Conformité Légale**
    *   Respect de la loi ivoirienne de 2013 et du RGPD européen.

#### **4.2 Résilience et Performance**
*   **NF-05 : Mode Hors-Ligne et Synchronisation (Gestion des conflits)**
    *   Le module Agent doit pouvoir fonctionner en cas de coupure réseau et synchroniser les données plus tard. Un mécanisme de résolution de conflits (priorité au serveur central ou marquage pour résolution manuelle) sera implémenté.
*   **NF-06 : Disponibilité et SLA**
    *   Le système doit garantir une disponibilité de 99,9%.
*   **NF-07 : Temps de réponse**
    *   Le temps de réponse des requêtes critiques ne doit pas excéder 2 secondes.

---

### **5. Éléments Structurels (À développer en Phase Conception)**

#### **5.1 Architecture cible**
*   Architecture orientée microservices (SOA) pour garantir l'indépendance technologique et éviter le *vendor lock-in*.
*   Utilisation d'API RESTful pour les interconnexions.
*   **Base de données :** Utilisation privilégiée d'un SGBD relationnel (SQL) robuste pour garantir l'intégrité et la cohérence des données hautement transactionnelles de l'état civil.

#### **5.2 Dictionnaire de données (Enrichi)**
*   **Données Citoyen :** NNI, Nom, Prénoms, Date de naissance, Lieu de naissance, Nationalité d'origine, Adresse à l'étranger, Empreintes (gabarits stockés uniquement sur serveur centralisé, jamais en local sur l'appareil du citoyen).
*   **Métadonnées Système :** ID_Utilisateur, Rôle (Role-Based Access), Statut_Dossier (Enum : En attente, Validé, Rejeté), Date_Création, Date_Modification, Adresse_IP (pour l'audit).

#### **5.3 Chronogramme (Rappel)**
*   **Mois 1 :** Spécifications et État de l'art.
*   **Mois 2 :** Modélisation (UML/Merise) et Codage du prototype.
*   **Mois 3 :** Tests et Rapport d'évaluation.
