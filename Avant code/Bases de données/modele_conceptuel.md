# Modèle Conceptuel des Données (MCD) & Diagramme UML

Ce document présente la modélisation conceptuelle des données du système de centralisation des ressortissants ivoiriens. Il comprend la définition Merise des entités et des associations, le schéma Entité-Association (ERD), ainsi que le diagramme de classes UML équivalent.

---

## 1. Modélisation Conceptuelle (Méthode Merise)

### 1.1 Liste des Entités et Attributs

1.  **UTILISATEUR** : Représente le compte de connexion d'un citoyen ou d'un membre du personnel consulaire.
    *   *id_utilisateur* (Identifiant)
    *   email
    *   mot_de_passe
    *   date_creation
    *   date_modification

2.  **ROLE** : Rôle d'accès système pour la gestion RBAC.
    *   *id_role* (Identifiant)
    *   nom_role (ex: `'ADMIN'`, `'AGENT'`, `'CITOYEN'`)
    *   description

3.  **CITOYEN** : Représente l'identité physique du ressortissant.
    *   *id_citoyen* (Identifiant)
    *   nni (Numéro National d'Identification)
    *   nom
    *   prenoms
    *   date_naissance
    *   lieu_naissance
    *   genre
    *   pays_residence
    *   adresse_residence
    *   telephone

4.  **DEMANDE** : Dossier d'état civil ou d'enrôlement.
    *   *id_demande* (Identifiant)
    *   type_demande (ex: `'ENROLEMENT'`, `'TRANSCRIPTION_NAISSANCE'`)
    *   statut
    *   motif_rejet
    *   date_creation
    *   date_modification

5.  **DOCUMENT** : Pièce justificative numérisée rattachée à une demande.
    *   *id_document* (Identifiant)
    *   type_document
    *   chemin_fichier
    *   donnees_ocr
    *   statut_validation

6.  **RENDEZ_VOUS** : Plage horaire d'enrôlement biométrique consulaire.
    *   *id_rendez_vous* (Identifiant)
    *   date_heure
    *   lieu
    *   statut

7.  **PAIEMENT** : Preuve financière liée au traitement d'un dossier.
    *   *id_paiement* (Identifiant)
    *   reference_transaction
    *   montant
    *   devise
    *   statut
    *   moyen_paiement
    *   date_paiement

8.  **LOG_AUDIT** : Journal d'audit pour la sécurité des accès.
    *   *id_log* (Identifiant)
    *   action
    *   description
    *   adresse_ip
    *   user_agent
    *   date_creation

### 1.2 Liste des Associations et Cardinalités

*   **Avoir_Role** (UTILISATEUR <-> ROLE)
    *   `UTILISATEUR (1,N) - Avoir_Role - (1,N) ROLE`
    *   *Règle de gestion :* Un utilisateur possède au moins un rôle et peut en avoir plusieurs. Un rôle peut être attribué à plusieurs utilisateurs.
*   **Soumettre** (UTILISATEUR <-> DEMANDE)
    *   `UTILISATEUR (0,N) - Soumettre - (1,1) DEMANDE`
    *   *Règle de gestion :* Un utilisateur peut soumettre 0 ou plusieurs demandes. Une demande est soumise par un et un seul utilisateur.
*   **Concerner** (DEMANDE <-> CITOYEN)
    *   `DEMANDE (1,1) - Concerner - (0,N) CITOYEN`
    *   *Règle de gestion :* Une demande concerne exactement un citoyen (existant ou en cours de création). Un citoyen peut être concerné par 0 ou plusieurs demandes au cours de sa vie (ex: enrôlement initial, transcription de mariage, etc.).
*   **Lier_Document** (DEMANDE <-> DOCUMENT)
    *   `DEMANDE (0,N) - Lier_Document - (1,1) DOCUMENT`
    *   *Règle de gestion :* Une demande peut être étayée par 0 ou plusieurs documents justificatifs. Un document téléversé est obligatoirement lié à une et une seule demande.
*   **Planifier** (DEMANDE <-> RENDEZ_VOUS)
    *   `DEMANDE (0,1) - Planifier - (1,1) RENDEZ_VOUS`
    *   *Règle de gestion :* Une demande peut faire l'objet de 0 ou 1 rendez-vous physique de capture biométrique. Un rendez-vous est obligatoirement fixé pour une et une seule demande.
*   **Assigner_Agent** (RENDEZ_VOUS <-> UTILISATEUR)
    *   `RENDEZ_VOUS (0,1) - Assigner_Agent - (0,N) UTILISATEUR`
    *   *Règle de gestion :* Un rendez-vous peut être affecté à 0 ou 1 agent consulaire (utilisateur avec rôle `AGENT`). Un agent peut se voir attribuer 0 à plusieurs rendez-vous.
*   **Payer** (DEMANDE <-> PAIEMENT)
    *   `DEMANDE (0,N) - Payer - (1,1) PAIEMENT`
    *   *Règle de gestion :* Une demande peut faire l'objet de 0 à plusieurs tentatives de paiement (si des échecs surviennent). Un paiement enregistré correspond à une et une seule demande.
*   **Generer_Log** (UTILISATEUR <-> LOG_AUDIT)
    *   `UTILISATEUR (0,N) - Generer_Log - (0,1) LOG_AUDIT`
    *   *Règle de gestion :* Un utilisateur peut générer plusieurs logs d'audit. Un log d'audit peut être associé à 0 ou 1 utilisateur (il est anonyme si l'action a lieu hors session).

---

## 2. Schéma Entité-Relation (MCD Graphique)

Voici la représentation sous forme de diagramme d'entités-relations Mermaid :

```mermaid
erDiagram
    UTILISATEUR {
        int id_utilisateur PK
        string email
        string mot_de_passe
        datetime date_creation
        datetime date_modification
    }
    ROLE {
        int id_role PK
        string nom_role
        string description
    }
    CITOYEN {
        int id_citoyen PK
        string nni
        string nom
        string prenoms
        date date_naissance
        string lieu_naissance
        char genre
        string pays_residence
        string adresse_residence
        string telephone
    }
    DEMANDE {
        int id_demande PK
        string type_demande
        string statut
        text motif_rejet
        datetime date_creation
        datetime date_modification
    }
    DOCUMENT {
        int id_document PK
        string type_document
        string chemin_fichier
        json donnees_ocr
        string statut_validation
    }
    RENDEZ_VOUS {
        int id_rendez_vous PK
        datetime date_heure
        string lieu
        string statut
    }
    PAIEMENT {
        int id_paiement PK
        string reference_transaction
        decimal montant
        string devise
        string statut
        string moyen_paiement
        datetime date_paiement
    }
    LOG_AUDIT {
        int id_log PK
        string action
        text description
        string adresse_ip
        string user_agent
        datetime date_creation
    }

    UTILISATEUR ||--|{ ROLE : "avoir"
    UTILISATEUR ||--|{ DEMANDE : "soumettre"
    UTILISATEUR ||--|{ LOG_AUDIT : "generer"
    UTILISATEUR ||--|{ RENDEZ_VOUS : "traiter"
    DEMANDE ||--|| CITOYEN : "concerner"
    DEMANDE ||--|{ DOCUMENT : "lier"
    DEMANDE |o--|o RENDEZ_VOUS : "planifier"
    DEMANDE ||--|{ PAIEMENT : "payer"
```

---

## 3. Diagramme de Classes UML (Modèle Statique)

Ce diagramme corrigé respecte les conventions du cours UML :
*   **Visibilité** (Chap. II §2, p.24) : attributs en `-` (privé), opérations en `+` (public), héritées en `#` (protégé).
*   **Composition** (Chap. II §4, p.30-32) : losange plein `◆` pour les relations où la suppression du composé entraîne la suppression des composants (Demande → Document, Demande → RendezVous).
*   **Généralisation/Spécialisation** (Chap. II §6, p.34-36) : hiérarchie d'héritage entre `Utilisateur` (super-classe abstraite) et ses sous-classes (`UtilisateurCitoyen`, `UtilisateurAgent`, `UtilisateurAdmin`).
*   **Agrégation** (Chap. II §4, p.29) : losange vide `◇` pour la relation Demande → Paiement (un paiement peut exister indépendamment si archivé).

```mermaid
classDiagram
    direction TB

    class Utilisateur {
        <<abstract>>
        -id: UUID
        -email: string
        -password: string
        -createdAt: datetime
        -updatedAt: datetime
        +verifierAuthentification() bool
        +changerMotDePasse(ancien: string, nouveau: string) bool
    }
    class UtilisateurCitoyen {
        +accederPortailCitoyen() void
        +soumettreDemande(type: string) Demande
    }
    class UtilisateurAgent {
        -matricule: string
        -consulat: string
        +traiterDossier(demandeId: UUID) void
        +validerDocument(documentId: UUID) void
    }
    class UtilisateurAdmin {
        +gererComptes() void
        +gererRoles() void
        +consulterLogs() LogAudit[]
    }

    class Role {
        -id: int
        -name: string
        -description: string
        +getNom() string
    }
    class Citoyen {
        -id: UUID
        -nni: string
        -nom: string
        -prenoms: string
        -dateNaissance: date
        -lieuNaissance: string
        -genre: char
        -paysResidence: string
        -adresseResidence: string
        -telephone: string
        +attribuerNNI(nni: string) void
        +getNomComplet() string
    }
    class Demande {
        -id: UUID
        -typeDemande: string
        -statut: string
        -motifRejet: string
        -createdAt: datetime
        -updatedAt: datetime
        +soumettre() void
        +valider() void
        +rejeter(motif: string) void
        +changerStatut(statut: string) void
        +getStatut() string
    }
    class Document {
        -id: UUID
        -typeDocument: string
        -cheminFichier: string
        -ocrData: json
        -statutValidation: string
        +analyserOCR() json
        +modifierStatut(statut: string) void
        +getCheminFichier() string
    }
    class RendezVous {
        -id: UUID
        -dateHeure: datetime
        -lieu: string
        -statut: string
        +modifierStatut(statut: string) void
        +estDansLeFutur() bool
    }
    class Paiement {
        -id: UUID
        -referenceTransaction: string
        -montant: decimal
        -devise: string
        -statut: string
        -moyenPaiement: string
        -datePaiement: datetime
        +validerPaiement() void
        +estReussi() bool
    }
    class LogAudit {
        -id: UUID
        -action: string
        -description: string
        -ipAddress: string
        -userAgent: string
        -createdAt: datetime
    }

    %% Généralisation / Spécialisation (Héritage)
    Utilisateur <|-- UtilisateurCitoyen : hérite
    Utilisateur <|-- UtilisateurAgent : hérite
    Utilisateur <|-- UtilisateurAdmin : hérite

    %% Associations simples
    Utilisateur "1..*" -- "1..*" Role : Posseder
    Utilisateur "1" -- "0..*" Demande : Soumettre
    Utilisateur "0..1" -- "0..*" LogAudit : Generer
    UtilisateurAgent "0..1" -- "0..*" RendezVous : Assigner
    Demande "0..*" -- "1" Citoyen : Concerner

    %% Composition (losange plein ◆) : suppression en cascade
    Demande "1" *-- "0..*" Document : Contenir
    Demande "1" *-- "0..1" RendezVous : Planifier

    %% Agrégation (losange vide ◇) : lien faible
    Demande "1" o-- "0..*" Paiement : Payer
```

### Légende des relations UML

| Symbole | Type de relation | Signification | Exemple |
| :---: | :--- | :--- | :--- |
| `──` | Association | Lien sémantique entre deux classes | Utilisateur ── Demande |
| `◆──` (`*--`) | **Composition** | La suppression du composé entraîne la suppression des composants | Demande ◆── Document |
| `◇──` (`o--`) | **Agrégation** | Le composant peut exister indépendamment du composé | Demande ◇── Paiement |
| `◁──` (`<\|--`) | **Généralisation** | Héritage : la sous-classe hérite des attributs et opérations de la super-classe | Utilisateur ◁── UtilisateurCitoyen |

