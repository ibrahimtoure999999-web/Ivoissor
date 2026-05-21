# Diagrammes Comportementaux Complémentaires

Ce document contient les diagrammes d'activité et d'état-transition, conformément aux Chapitres III (§III et §V) du cours UML.

---

## 1. Diagramme d'Activité (DAC) — Processus Consulaire Complet

Ce diagramme représente le flux de travail global du processus d'enrôlement consulaire, depuis la soumission de la demande par le citoyen jusqu'à la synchronisation finale avec le RNPP. Les **couloirs d'activités** (swimlanes, p.63-64 du cours UML) sont utilisés pour montrer la responsabilité de chaque acteur.

```mermaid
flowchart TD
    subgraph Citoyen ["🧑 Citoyen"]
        A1["Créer un compte / S'authentifier"]
        A2["Remplir le formulaire biographique"]
        A3["Téléverser les documents justificatifs"]
        A4["Vérifier / corriger les données OCR"]
        A5{"Soumettre maintenant ?"}
        A6["Enregistrer en brouillon"]
        A7["Soumettre la demande"]
        A8{"Payer maintenant ?"}
        A9["Effectuer le paiement en ligne"]
        A10["Reporter le paiement"]
        A11["Prendre rendez-vous biométrique"]
        A12["Se rendre au consulat (capture biométrique)"]
        A13["Recevoir notification de validation"]
    end

    subgraph Systeme ["⚙️ Système"]
        S1["Analyser les documents par OCR"]
        S2["Envoyer notification 'Demande soumise'"]
        S3["Transmettre au système bancaire"]
        S4{"Transaction réussie ?"}
        S5["Enregistrer paiement REUSSI"]
        S6["Enregistrer paiement ECHOUE"]
        S7["Envoyer notification 'Dossier validé / NNI attribué'"]
        S8["Synchroniser avec le RNPP"]
    end

    subgraph Agent ["👤 Agent Consulaire"]
        B1["Consulter la liste des dossiers soumis"]
        B2["Vérifier les documents du dossier"]
        B3{"Documents conformes ?"}
        B4["Rejeter le dossier (saisir motif)"]
        B5["Valider le dossier"]
        B6["Capturer les données biométriques"]
    end

    A1 --> A2
    A2 --> A3
    A3 --> S1
    S1 --> A4
    A4 --> A5
    A5 -- Non --> A6
    A5 -- Oui --> A7
    A6 -.-> A7
    A7 --> S2
    S2 --> A8
    A8 -- Oui --> A9
    A8 -- Non --> A10
    A9 --> S3
    S3 --> S4
    S4 -- Oui --> S5
    S4 -- Non --> S6
    S6 -.-> A9
    S5 --> A11
    A10 -.-> A11
    A11 --> B1

    B1 --> B2
    B2 --> B3
    B3 -- Non --> B4
    B4 --> S2
    B3 -- Oui --> B5
    B5 --> A12
    A12 --> B6
    B6 --> S8
    S8 --> S7
    S7 --> A13
```

### Lecture du diagramme d'activité

1. **Phase Citoyen** : Le citoyen s'inscrit, remplit ses données biographiques, téléverse ses documents (OCR automatique), puis choisit de soumettre immédiatement ou de sauvegarder en brouillon.
2. **Phase Paiement** : Après soumission, le citoyen peut payer en ligne (via le système bancaire) ou reporter le paiement.
3. **Phase Agent** : L'agent consulaire consulte les dossiers soumis, vérifie les documents, et décide de valider ou rejeter le dossier.
4. **Phase Biométrique** : Si le dossier est validé, le citoyen se rend physiquement au consulat pour la capture biométrique.
5. **Phase Finale** : Les données sont synchronisées avec le RNPP, un NNI est attribué, et le citoyen est notifié.

---

## 2. Diagramme d'État-Transition (DET) — Cycle de vie de l'objet Demande

Ce diagramme modélise les **états successifs** d'un objet `Demande` et les **événements déclencheurs** de chaque transition, conformément au Chapitre III (§III, p.57-59) du cours UML.

```mermaid
stateDiagram-v2
    [*] --> BROUILLON : Création de la demande

    BROUILLON --> SOUMIS : Le citoyen soumet la demande
    BROUILLON --> BROUILLON : Modification des données / ajout de documents

    SOUMIS --> EN_COURS : L'agent ouvre le dossier pour vérification
    SOUMIS --> SOUMIS : Notification envoyée au citoyen

    EN_COURS --> VALIDE : L'agent approuve le dossier\n(tous documents conformes)
    EN_COURS --> REJETE : L'agent rejette le dossier\n(motif de rejet obligatoire)
    EN_COURS --> EN_COURS : L'agent valide un document individuel

    REJETE --> BROUILLON : Le citoyen corrige et re-soumet
    REJETE --> [*] : Le citoyen abandonne la demande

    VALIDE --> SYNCHRONISE : Données synchronisées avec le RNPP\n(NNI attribué)

    SYNCHRONISE --> [*] : Processus terminé
```

### Description des états

| État | Description | Actions internes (activités) |
| :--- | :--- | :--- |
| **BROUILLON** | Demande créée mais non encore soumise. Le citoyen peut modifier ses données et ajouter/retirer des documents. | `do/ sauvegarderDonnées()` |
| **SOUMIS** | Demande transmise pour traitement. Le citoyen ne peut plus la modifier directement. | `entry/ envoyerNotification("Demande soumise")` |
| **EN_COURS** | Un agent consulaire examine le dossier et vérifie les documents un par un. | `do/ vérifierDocuments()` |
| **VALIDE** | Tous les documents sont conformes et le dossier est approuvé par l'agent. | `entry/ enregistrerLogAudit("APPROBATION")` |
| **REJETE** | Le dossier ne satisfait pas aux exigences. Un motif de rejet est obligatoirement renseigné. | `entry/ envoyerNotification("Dossier rejeté", motif)` |
| **SYNCHRONISE** | Les données d'état civil ont été transmises au RNPP et un NNI a été attribué. État terminal. | `entry/ synchroniserRNPP()`, `entry/ envoyerNotification("NNI attribué")` |

### Description des transitions

| Transition | Événement déclencheur | Garde (condition) | Action |
| :--- | :--- | :--- | :--- |
| BROUILLON → SOUMIS | `soumettre()` | Données biographiques complètes | `changerStatut("SOUMIS")` |
| SOUMIS → EN_COURS | `ouvrirDossier(agent_id)` | Agent authentifié avec rôle `AGENT` | `changerStatut("EN_COURS")` |
| EN_COURS → VALIDE | `valider()` | Tous les documents ont `statut_validation = "VALIDE"` | `changerStatut("VALIDE")` |
| EN_COURS → REJETE | `rejeter(motif)` | `motif` non vide | `changerStatut("REJETE")`, `setMotifRejet(motif)` |
| REJETE → BROUILLON | `corriger()` | Le citoyen modifie ses données | `changerStatut("BROUILLON")`, `resetMotifRejet()` |
| VALIDE → SYNCHRONISE | `synchroniserRNPP()` | Connexion au RNPP active | `attribuerNNI(nni)` |

---

## 3. Diagramme d'État-Transition — Cycle de vie de l'objet Document

```mermaid
stateDiagram-v2
    [*] --> EN_ATTENTE : Téléversement du fichier

    EN_ATTENTE --> EN_ATTENTE : Analyse OCR en cours\n(extraction des données)
    EN_ATTENTE --> VALIDE : L'agent valide le document\n(conforme à l'original)
    EN_ATTENTE --> REFUSE : L'agent refuse le document\n(illisible, non conforme, expiré)

    REFUSE --> EN_ATTENTE : Le citoyen téléverse une nouvelle version

    VALIDE --> [*] : Document archivé
    REFUSE --> [*] : Le citoyen abandonne
```
