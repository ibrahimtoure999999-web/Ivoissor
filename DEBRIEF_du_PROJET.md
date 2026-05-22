# 🇨🇮 Note d'Avancement et de Cadrage — Projet Ivoissor

  
**Rédigé par :** Ibrahim Touré   
**Date :** 21 Mai 2026  
**Objet :** Point de situation sur L'avancement du projet **Ivoissor** ---

## 1. Contexte, Enjeux et Portée du Projet

### 1.1 Objectif Général
Le projet **Ivoissor** consiste à concevoir, sécuriser et déployer une solution informatique centralisée (Web et Mobile PWA) dédiée à l'identification, au recensement et à la gestion administrative des ressortissants ivoiriens résidant à l'étranger (la diaspora). L'application fait office de **guichet unique** permettant aux citoyens de préparer leurs démarches à distance avant une synchronisation future avec le Registre National des Personnes Physiques (RNPP) et l'Office National de l'État Civil et de l'Identification (ONECI).

### 1.2 Problématique Administrative
Les méthodes classiques de recensement et d'enrôlement consulaire font face à des défis majeurs :
* **Lenteurs et failles** de traitement dues à des processus semi-manuels ou décentralisés.
* **Manque de fiabilité et d'intégrité** lors de la collecte et de la transmission transfrontalière des données d'état civil.
* **Risques de sécurité** liés à la manipulation de pièces d'identité sensibles et à la gestion des flux financiers (droits de chancellerie) en espèces.

### 1.3 Intérêts du Projet
* **Sociétal :** Faciliter et fluidifier les démarches administratives de la diaspora, tout en rapprochant l'administration de ses usagers.
* **Scientifique & Technique :** Mettre en œuvre les standards modernes du génie logiciel (architecture orientée services, typage strict en PHP, isolation des données) appliqués à la souveraineté numérique d'un État.
* **Académique :** Servir de socle pratique pour le mémoire de fin de cycle, en validant la capacité à concevoir une solution robuste selon les méthodologies UML/Merise et les exigences de sécurité étatiques.

---

## 2. Bilan des Réalisations Techniques (Ce qui est FAIT)

L'architecture globale repose sur le framework **Laravel** et une base de données relationnelle **MySQL**. Les fondations de l'application sont entièrement opérationnelles et documentées.

### 2.1 Création de compte et Authentification (F-01)
* **Migration & Schéma BDD :** Modélisation et exécution des migrations relationnelles stables.
* **Rate Limiting :** Protection des endpoints sensibles (connexion limitée à 5 tentatives par minute) pour contrer les attaques par force brute.
* **HaveIBeenPwned API :** Intégration d'un système de validation en temps réel interdisant l'utilisation de mots de passe compromis dans des fuites publiques.
* **Journaux d'Audit (Audit Logs) :** Implémentation d'une table immuable traçant l'ensemble des actions sensibles (IP, User-Agent, action précise).

### 2.2 Pré-enrôlement et Téléversement documentaire (F-02 & F-04)
* **Formulaire Dynamique :** Saisie biographique complète (nom, prénoms, adresse, NNI obligatoire pour le passeport) avec adaptation dynamique en JavaScript des pièces justificatives requises (Passeport, CNI, Extrait de naissance, Certificat de nationalité).
* **Règles "Carte Consulaire" :** Intégration des pièces réglementaires officielles (justificatif de domicile local, reçu de paiement des droits de chancellerie de 10 €).
* **Stockage Isolé & Privé :** Pour éviter le vol de données et l'exposition publique, les fichiers d'identité sont stockés de manière sécurisée dans `storage/app/private/documents/` et ne sont accessibles que via un contrôleur de téléchargement sécurisé (protection contre les failles IDOR).
* **Robustesse Transactionnelle :** Utilisation de `DB::transaction` assurant un nettoyage automatique des fichiers physiques en cas d'erreur de soumission en base de données.
* **Tests Automatisés (Pest) :** Écriture et validation d'une suite de 13 tests de fonctionnalité couvrant les limites de taille (5 Mo), la validation des extensions de fichiers et les restrictions d'accès croisés.

### 2.3 Prise de Rendez-vous (F-05)
* **Sélection Intelligente :** Formulaire de réservation pré-sélectionnant automatiquement le consulat ou l'ambassade en fonction du pays de résidence du citoyen (Paris, Bruxelles, Dakar, Rabat, Ottawa, Washington).
* **API AJAX Créneaux :** Endpoint dynamique permettant de griser en temps réel sur l'interface client les créneaux horaires déjà occupés pour une date et un lieu donnés.
* **Annulation :** Possibilité pour le citoyen d'annuler son rendez-vous depuis son espace, libérant immédiatement la plage horaire.

### 2.4 Traitement des dossiers et OCR (Agent) (F-08)
* **Tableau de Bord :** Vue consolidée des indicateurs clés (dossiers soumis, en instruction, validés, rejetés) avec priorisation automatique des 10 dossiers les plus anciens en attente.
* **Instruction des Demandes :** Interface paginée dotée de filtres avancés (par statut, type de document ou recherche par NNI/Nom).
* **Rejet Motivé Obligatoire :** Contrainte logicielle forçant l'agent à saisir une explication détaillée d'au moins 10 caractères pour justifier le rejet d'un dossier.
* **Sécurité des Routes :** Protection des accès via le middleware `role:AGENT`.

### 2.5 Documentation du Code
* L'intégralité du code source de l'application (Modèles, Services, Contrôleurs, Requêtes de validation, Middlewares, Enums) a été rigoureusement documentée et commentée en suivant une structure standardisée facilitant la maintenance par des équipes tierces.

---

## 3. Chantiers Restants et Prochaines Étapes (Ce qui RESTE À FAIRE)

Pour achever le prototype conformément aux exigences fonctionnelles du Cahier des Charges et sécuriser la soutenance, les développements se concentreront sur les modules suivants :

### 3.1 Gestion du planning [Agent] (F-10) — *Non commencé*
* Développer l'interface permettant aux agents de configurer les plages horaires d'ouverture, les jours fériés et les capacités d'accueil par consulat.

### 3.2 Espace Administrateur (F-11, F-12 & F-13) — *F-11 et F-13 faits, F-12 non commencé*
* **Gestion des rôles (RBAC) (F-11) :** Finaliser l'attribution dynamique des droits (Citoyen, Agent, Administrateur) via une interface.
* **Gestion des comptes agents (F-12) :** Interface d'administration pour la création et la désactivation des comptes du personnel consulaire.
* **Journaux d'Audit (F-13) :** Interface de supervision et de filtrage avancé des journaux d'audit (`audit_logs`) pour l'administrateur de sécurité (déjà implémentée).

### 3.3 Module de Paiement en ligne (F-07) — *Non commencé*
* Intégration d'une interface de simulation de paiement sécurisée prenant en compte les solutions de Mobile Money dominantes en Côte d'Ivoire (**Wave, Orange Money, MTN Moov Money**) ainsi que les règlements par **Cartes Bancaires** pour s'affranchir des flux d'espèces en chancellerie.

### 3.4 Fonctionnalités Métiers Avancées (Cahier des Charges)
* **Transcription d'État Civil (F-03) :** Formulaire de demande de transcription pour les actes de naissance, mariage ou décès survenus à l'étranger.
* **Simulation OCR (F-04 / F-08) :** Intégration d'une brique d'analyse documentaire pour pré-remplir les formulaires à partir des pièces téléversées (avec possibilité de correction manuelle).
* **Suivi et Notifications multicanaux (F-06) :** Système d'envoi automatique de notifications Email/SMS lors des changements de statut des dossiers.
* **Business Intelligence & Indicateurs (F-14 & F-15) :** Module de statistiques graphiques et tableaux de bord décisionnels pour analyser la répartition géographique et la pyramide des âges de la diaspora.
* **Résilience Réseau (NF-05) :** Mécanisme de mode hors-ligne partiel pour l'espace Agent consulaire avec synchronisation et résolution de conflits lors du rétablissement de la connexion.

---

## 4. Plan d'Action Immédiat et Améliorations en Cours

À la suite d'une revue interne du code, plusieurs optimisations de sécurité et d'ergonomie sont en cours de déploiement en parallèle des nouveaux modules :
1. **Sécurisation Anti-Surbooking :** Ajout d'une contrainte d'unicité stricte au niveau du serveur (`RendezVousRequest` et verrous de base de données) pour empêcher deux citoyens différents de réserver le même créneau dans le même consulat au même instant.
2. **Refactoring des Rôles :** Consolidation de la méthode `hasRole()` au niveau du modèle `User` pour s'aligner parfaitement avec le middleware de sécurité `CheckRole`.
3. **Cinématique de Connexion (UX) :** Implémentation d'une redirection dynamique après l'authentification (aiguiller automatiquement les agents vers le back-office et les citoyens vers leur dashboard personnel pour éviter les écrans vides).
4. **Persistance Historique :** Remplacement de la suppression physique des rendez-vous annulés par un système de **Soft Deletes** ou un changement d'état à `ANNULE` afin de conserver les métadonnées à des fins statistiques.
