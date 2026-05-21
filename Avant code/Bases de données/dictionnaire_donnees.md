# Dictionnaire de Données (Data Dictionary)

Ce document décrit de manière exhaustive toutes les informations élémentaires stockées et manipulées au sein du système de centralisation des données des ressortissants ivoiriens.

---

## 1. Entité : Utilisateur (`users`)
Représente les comptes d'accès à l'application (Citoyens et Personnels Administratifs/Consulaires).

| Code Attribut | Libellé / Description | Type de Donnée | Longueur / Format | Contraintes / Règles de gestion |
| :--- | :--- | :--- | :--- | :--- |
| `id` | Identifiant unique du compte | INT / UUID | Clé Primaire | Auto-incrémenté ou généré de manière unique |
| `email` | Adresse de messagerie électronique | VARCHAR | 255 | Unique, obligatoire, format email valide |
| `password` | Mot de passe de connexion | VARCHAR | 255 | Haché de manière sécurisée (ex: bcrypt) |
| `created_at` | Date et heure de création | DATETIME | AAAA-MM-JJ HH:MM:SS | Rempli automatiquement à la création |
| `updated_at` | Date et heure de dernière modification | DATETIME | AAAA-MM-JJ HH:MM:SS | Rempli à chaque modification |

---

## 2. Entité : Rôle (`roles` & `role_user`)
Permet de gérer le contrôle d'accès basé sur les rôles (RBAC).

| Code Attribut | Libellé / Description | Type de Donnée | Longueur / Format | Contraintes / Règles de gestion |
| :--- | :--- | :--- | :--- | :--- |
| `id` | Identifiant unique du rôle | INT | Clé Primaire | Auto-incrémenté |
| `name` | Nom du rôle | VARCHAR | 50 | Unique. Valeurs : `'ADMIN'`, `'AGENT'`, `'CITOYEN'` |
| `description`| Description textuelle du rôle | VARCHAR | 255 | Optionnel |

---

## 3. Entité : Citoyen (`citoyens`)
Représente l'état civil de la personne physique (ressortissant).

| Code Attribut | Libellé / Description | Type de Donnée | Longueur / Format | Contraintes / Règles de gestion |
| :--- | :--- | :--- | :--- | :--- |
| `id` | Identifiant unique du citoyen | INT / UUID | Clé Primaire | Clé Primaire |
| `nni` | Numéro National d'Identification | VARCHAR | 11 | Unique, optionnel (attribué après validation par l'ONECI) |
| `nom` | Nom de famille | VARCHAR | 100 | Obligatoire, en majuscules |
| `prenoms` | Prénoms | VARCHAR | 150 | Obligatoire |
| `date_naissance`| Date de naissance | DATE | AAAA-MM-JJ | Obligatoire |
| `lieu_naissance`| Lieu de naissance | VARCHAR | 100 | Obligatoire |
| `genre` | Sexe de la personne | CHAR | 1 | `'M'` (Masculin) ou `'F'` (Féminin) |
| `pays_residence`| Pays actuel de résidence | VARCHAR | 100 | Obligatoire |
| `adresse_residence`| Adresse physique à l'étranger | VARCHAR | 255 | Obligatoire |
| `telephone` | Numéro de téléphone | VARCHAR | 20 | Obligatoire, format international |

---

## 4. Entité : Demande (`demandes`)
Représente un dossier d'enrôlement ou de transcription soumis par un utilisateur.

| Code Attribut | Libellé / Description | Type de Donnée | Longueur / Format | Contraintes / Règles de gestion |
| :--- | :--- | :--- | :--- | :--- |
| `id` | Identifiant unique du dossier | INT / UUID | Clé Primaire | Clé Primaire |
| `user_id` | Identifiant de l'utilisateur requérant | INT / UUID | Clé Étrangère | Référence `users(id)`. Obligatoire |
| `citoyen_id` | Identifiant du citoyen concerné | INT / UUID | Clé Étrangère | Référence `citoyens(id)`. Nullable si nouveau citoyen |
| `type_demande`| Nature de la démarche | VARCHAR | 50 | Valeurs : `'ENROLEMENT'`, `'TRANSCRIPTION_NAISSANCE'`, `'TRANSCRIPTION_MARIAGE'`, `'TRANSCRIPTION_DECES'` |
| `statut` | État d'avancement du dossier | VARCHAR | 20 | Valeurs : `'BROUILLON'`, `'SOUMIS'`, `'EN_COURS'`, `'VALIDE'`, `'REJETE'` |
| `motif_rejet` | Cause du rejet éventuel du dossier | TEXT | - | Obligatoire si `statut` = `'REJETE'` |
| `created_at` | Date de soumission initiale | DATETIME | AAAA-MM-JJ HH:MM:SS | Rempli automatiquement |
| `updated_at` | Date de mise à jour du dossier | DATETIME | AAAA-MM-JJ HH:MM:SS | Rempli automatiquement |

---

## 5. Entité : Document (`documents`)
Fichiers justificatifs téléversés pour appuyer une demande.

| Code Attribut | Libellé / Description | Type de Donnée | Longueur / Format | Contraintes / Règles de gestion |
| :--- | :--- | :--- | :--- | :--- |
| `id` | Identifiant unique du document | INT / UUID | Clé Primaire | Clé Primaire |
| `demande_id` | Identifiant de la demande liée | INT / UUID | Clé Étrangère | Référence `demandes(id)`. Obligatoire |
| `type_document`| Type de pièce justificative | VARCHAR | 50 | Valeurs : `'PASSEPORT'`, `'ACTE_NAISSANCE'`, `'JUSTIFICATIF_DOMICILE'`, `'PIECE_IDENTITE'` |
| `chemin_fichier`| Chemin d'accès relatif du fichier sur le serveur | VARCHAR | 255 | Obligatoire |
| `ocr_data` | Données textuelles extraites par OCR | JSON | - | Optionnel (stockage du texte brut ou structuré lu par l'OCR) |
| `statut_validation`| Résultat de la vérification de la pièce | VARCHAR | 20 | Valeurs : `'EN_ATTENTE'`, `'VALIDE'`, `'REFUSE'` |

---

## 6. Entité : Rendez-vous (`rendez_vous`)
Plannings d'enrôlement biométrique consulaire.

| Code Attribut | Libellé / Description | Type de Donnée | Longueur / Format | Contraintes / Règles de gestion |
| :--- | :--- | :--- | :--- | :--- |
| `id` | Identifiant unique du rendez-vous | INT / UUID | Clé Primaire | Clé Primaire |
| `demande_id` | Identifiant de la demande liée | INT / UUID | Clé Étrangère | Référence `demandes(id)`. Obligatoire, Unique (1 RDV max / demande) |
| `date_heure` | Date et heure planifiées | DATETIME | AAAA-MM-JJ HH:MM | Doit être dans le futur |
| `lieu` | Ambassade/Consulat de réception | VARCHAR | 150 | Obligatoire (ex: 'Consulat Général Paris') |
| `statut` | État du rendez-vous | VARCHAR | 20 | Valeurs : `'PLANIFIE'`, `'HONORE'`, `'ANNULE'` |
| `agent_id` | Identifiant de l'agent d'accueil | INT / UUID | Clé Étrangère | Référence `users(id)`. Optionnel |

---

## 7. Entité : Paiement (`paiements`)
Traces financières associées aux frais d'enrôlement ou d'état civil.

| Code Attribut | Libellé / Description | Type de Donnée | Longueur / Format | Contraintes / Règles de gestion |
| :--- | :--- | :--- | :--- | :--- |
| `id` | Identifiant unique du paiement | INT / UUID | Clé Primaire | Clé Primaire |
| `demande_id` | Identifiant de la demande liée | INT / UUID | Clé Étrangère | Référence `demandes(id)`. Obligatoire |
| `reference_transaction`| Code de transaction unique de la passerelle | VARCHAR | 100 | Unique, obligatoire |
| `montant` | Montant payé | DECIMAL | 10,2 | Supérieur à 0 |
| `devise` | Devise monétaire | VARCHAR | 3 | Valeurs standardisées (ex: `'XOF'`, `'EUR'`, `'USD'`) |
| `statut` | Résultat de la transaction | VARCHAR | 20 | Valeurs : `'EN_ATTENTE'`, `'REUSSI'`, `'ECHOUE'` |
| `moyen_paiement`| Méthode de versement | VARCHAR | 50 | Valeurs : `'CARTE'`, `'MOBILE_MONEY'` |
| `created_at` | Date et heure de la transaction | DATETIME | AAAA-MM-JJ HH:MM:SS | Rempli automatiquement |

---

## 8. Entité : Log d'Audit (`audit_logs`)
Table d'audit immuable traçant les actions administratives sensibles.

| Code Attribut | Libellé / Description | Type de Donnée | Longueur / Format | Contraintes / Règles de gestion |
| :--- | :--- | :--- | :--- | :--- |
| `id` | Identifiant unique du log | INT / UUID | Clé Primaire | Clé Primaire |
| `user_id` | Identifiant de l'auteur de l'action | INT / UUID | Clé Étrangère | Référence `users(id)`. Nullable (ex: si visiteur anonyme) |
| `action` | Type de transaction ou opération | VARCHAR | 100 | Obligatoire (ex: `'APPROBATION_DOSSIER'`) |
| `description`| Détails lisibles de l'action | TEXT | - | Optionnel |
| `ip_address` | Adresse IP source | VARCHAR | 45 | IPv4 ou IPv6. Obligatoire |
| `user_agent` | Signature du navigateur/client | VARCHAR | 255 | Obligatoire |
| `created_at` | Date et heure de l'événement | DATETIME | AAAA-MM-JJ HH:MM:SS | Immuable (aucune mise à jour possible) |
