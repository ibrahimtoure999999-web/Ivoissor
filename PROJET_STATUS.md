# 🇨🇮 Ivoissor — État d'avancement du Projet

Ce document sert de journal de bord et récapitule l'ensemble des fonctionnalités implémentées, sécurisées, ou restant à développer pour le portail consulaire **Ivoissor**.

---

## 📊 Tableau de Bord des Fonctionnalités

| ID | Module / Fonctionnalité | Statut | Détails & Sécurité |
| :--- | :--- | :---: | :--- |
| **F-01** | **Authentification & Base de données** | 🟢 Fait | MySQL, Rate Limiting, Audit logs, HaveIBeenPwned API, Design System Premium |
| **F-02** | **Enrôlement & Demande de documents** | 🟢 Fait | Enregistrement Citoyen/Demande, Téléversement dynamique, Stockage Privé, Tests unitaires & de sécurité |
| **F-03** | **Gestion des Rendez-vous** | 🟢 Fait | Réservation de créneau consulaire, prévention doublons, annulation, API AJAX créneaux |
| **F-04** | **Espace Agent (Back-office)** | 🟢 Fait | Validation, instruction et rejet motivé des dossiers, filtrage, tableau de bord, statistiques |
| **F-05** | **Espace Administrateur** | 🟠 En cours | Gestion des rôles, statistiques globales et logs de sécurité (Filtrage des logs implémenté) |
| **F-06** | **Paiement en ligne** | 🔴 Non commencé | Simulation de paiement Mobile Money & Cartes Bancaires |

---

## 📚 Documentation & Maintenance

- **Documentation du Code (Skill Documentation)** : 🟢 Fait
  - Intégration du skill `Skills/Code-Documenter/SKILL.md` pour garantir une vulgarisation et une structure cohérente des commentaires.
  - L'intégralité des fichiers du dossier `app/` (Modèles, Services, Contrôleurs, Requests, Middlewares, Enums, Providers) a été revue et documentée pour faciliter la maintenance et la compréhension par des profils juniors/intermédiaires.

---

## 🚀 GitHub & Déploiement

- **Dépôt GitHub** : 🟢 Fait
  - Le projet est désormais hébergé et synchronisé sur GitHub : `https://github.com/ibrahimtoure999999-web/Ivoissor`.
  - Branche principale : `main`.

---

## 🛠️ Détail des Réalisations (Ce qui est FAIT)

### 1. Base de données & Infrastructure
- **Migration MySQL** : Passage réussi d'une base relationnelle MySQL.
- **Table `documents`** : Migration `2026_01_01_000006_create_documents_table.php` exécutée pour relier les pièces jointes à une demande.

### 2. Sécurité de l'Authentification (F-01)
- **Rate Limiting** : Limitation à 5 tentatives de connexion par minute.
- **Politique de mot de passe forte** : Validation temps réel contre les bases de mots de passe compromis (*HaveIBeenPwned*).
- **Journaux d'Audit (Audit Logs)** : Enregistrement en base de données de toutes les actions sensibles.

### 3. Enrôlement & Demande de documents (F-02)
- **Enums Typés** : Gestion propre via `DemandeTypeEnum` et `DemandeStatutEnum` (typage strict PHP).
- **Formulaire Dynamique d'Enrôlement** :
  - Saisie complète des informations civiles (nom, prénoms, NNI obligatoire pour passeport, adresse, contact).
  - Sélection interactive du type de demande avec ajustement en temps réel (via JS) des pièces justificatives requises (Passeport/CNI, État Civil, Carte Consulaire).
  - Intégration des règles officielles de la **Carte Consulaire** : possibilité de s'identifier par CNI/Passeport valide ou par Extrait de naissance + Certificat de nationalité, fourniture d'un justificatif de domicile local et du reçu de paiement des droits de chancellerie (10 €).
- **Stockage Sécurisé & Privé** : Stockage des documents d'identité dans `storage/app/private/documents/` pour éviter toute exposition publique directe sur internet.
- **Transaction SQL Robuste** : Utilisation de `DB::transaction` avec nettoyage automatique des fichiers physiques en cas d'erreur lors du processus de soumission.
- **Tests de Sécurité (Pest)** : Suite complète de 13 tests rédigés dans `tests/Feature/DemandeTest.php` couvrant les droits d'accès croisés, la validation des types de fichiers, la limite de taille à 5 Mo et l'enregistrement BDD.

### 4. Gestion des Rendez-vous Consulaires (F-03)
- **Formulaire de Réservation** : Sélection du lieu consulaire (pré-sélectionné selon le pays de résidence), choix de la date (jours ouvrés uniquement) et créneau horaire via une grille interactive de badges cliquables.
- **Anti-doublons** : Validation serveur empêchant deux réservations sur le même créneau horaire au même consulat.
- **API AJAX Créneaux** : Endpoint `GET /api/rendezvous/creneaux-occupes` retournant en JSON les créneaux déjà pris pour une date et un lieu donnés, permettant de griser dynamiquement les créneaux indisponibles côté client.
- **Annulation & Libération** : Le citoyen peut annuler son rendez-vous depuis la page de suivi — le créneau est immédiatement libéré (suppression physique).
- **Intégration dans le Suivi** : La vue `show.blade.php` affiche désormais une carte du rendez-vous actif (date, heure, lieu, statut) ou un encart d'invitation à prendre rendez-vous si le dossier est au statut `SOUMIS` ou `INSTRUCTION`.
- **Journaux d'Audit** : Chaque prise et annulation de rendez-vous est tracée dans la table `audit_logs`.

### 5. Design System & Interfaces Premium
- **Séparation CSS/HTML** : Tous les styles sont déportés dans les fichiers CSS sous `public/css/` (`variables.css`, `auth.css`, `dashboard.css`, `welcome.css`).
- **Page d'Accueil Interactive** : Tracker interactif en JS d'inspiration *e-justice.ci* fonctionnel en démo publique.
- **Dashboard Citoyen Dynamique** : Affiche les statistiques réelles de l'utilisateur, son tracker d'avancement pour son dernier dossier actif, et l'historique complet de ses demandes.

### 6. Espace Agent (Back-office) (F-04)
- **Tableau de Bord** : Vue d'ensemble avec statistiques globales (`SOUMIS`, `INSTRUCTION`, `VALIDE`, `REJETE`) et une liste des 10 dossiers prioritaires (plus anciens en attente d'action).
- **Gestion des Demandes** : Interface paginée avec filtres avancés (par statut, type de demande, recherche par Nom/Prénom/NNI).
- **Validation & Instruction** : Les agents peuvent passer un dossier `SOUMIS` en `INSTRUCTION`, puis le `VALIDER`. Chaque action est tracée dans l'Audit Log.
- **Rejet motivé** : Obligation pour l'agent de fournir une explication détaillée (minimum 10 caractères) justifiant le rejet d'un dossier.
- **Middleware & Sécurité** : Les routes sont protégées via le middleware `role:AGENT` empêchant l'accès par les simples citoyens. Utilisateur de test intégré dans l'outil de seeding (`agent@ivoissor.ci`).

### 7. Espace Administrateur (F-05)
- **Logs d'Audit** : Interface de visualisation des logs avec filtrage par action (connexion, création, modification, rejet, etc.) et recherche multicritère (IP, description, utilisateur).
- **Pagination & Traçabilité** : Navigation fluide dans les journaux système pour assurer la transparence des actions des agents et administrateurs.

---

## 📋 Ce qui reste à faire (Prochaines Étapes)

### 1. Espace Administrateur (F-05)
- Gestion et assignation des rôles (Citoyen, Agent, Admin) via l'interface.
- Statistiques globales avancées.

### 2. Module de Paiement (F-06)
- Page de paiement sécurisée simulant les interfaces de paiement mobile courantes en Côte d'Ivoire (Wave, Orange, MTN, Moov) et cartes de crédit.

---

## 🔐 Principes d'ingénierie appliqués
- **Simplicité & Clarté** : Code minimaliste, séparé par responsabilité.
- **Sécurité Native** : Aucun mot de passe en clair, protection CSRF active, rate limiters natifs, isolation des fichiers privés.
- **Zéro Hallucination** : Utilisation exclusive des routes et fonctions réelles de Laravel et des spécifications requises.
