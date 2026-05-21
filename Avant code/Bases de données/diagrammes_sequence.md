# Diagrammes de Séquence (DSE)

Ce document présente les diagrammes de séquence des scénarios principaux du système, conformément au Chapitre III (§II) du cours UML. Chaque diagramme illustre les interactions chronologiques entre les acteurs, les objets frontières (interfaces), les objets de contrôle et les objets entités.

---

## 1. Scénario : Soumettre une demande d'enrôlement

Ce diagramme décrit l'enchaînement nominal du cas d'utilisation « Soumettre une demande d'enrôlement ».

```mermaid
sequenceDiagram
    actor Citoyen
    participant IHM as Interface Web/Mobile
    participant Ctrl as ControleurDemande
    participant DemDAO as Demande
    participant CitDAO as Citoyen
    participant DocDAO as Document
    participant OCR as Service OCR
    participant Notif as Service Notification

    Citoyen->>IHM: accederNouvelledemande()
    IHM->>Ctrl: creerDemande(type="ENROLEMENT")
    Ctrl->>CitDAO: creerOuRecupererCitoyen(données biographiques)
    CitDAO-->>Ctrl: citoyen_id
    Ctrl->>DemDAO: new Demande(user_id, citoyen_id, type, statut="BROUILLON")
    DemDAO-->>Ctrl: demande_id
    Ctrl-->>IHM: formulairePréRempli(demande_id)
    IHM-->>Citoyen: afficherFormulaire()

    Citoyen->>IHM: saisirDonnéesBiographiques(nom, prénoms, date_naissance, ...)
    IHM->>Ctrl: mettreAJourCitoyen(citoyen_id, données)
    Ctrl->>CitDAO: update(données)
    CitDAO-->>Ctrl: ok

    Citoyen->>IHM: téléverserDocument(fichier, type="PASSEPORT")
    IHM->>Ctrl: ajouterDocument(demande_id, fichier, type)
    Ctrl->>DocDAO: new Document(demande_id, type, chemin_fichier)
    DocDAO-->>Ctrl: document_id
    Ctrl->>OCR: analyser(chemin_fichier)
    OCR-->>Ctrl: données_extraites (JSON)
    Ctrl->>DocDAO: update(ocr_data=données_extraites)
    Ctrl-->>IHM: champsPréRemplis(données_extraites)
    IHM-->>Citoyen: afficherDonnéesOCR()

    Citoyen->>IHM: soumettreDemande(demande_id)
    IHM->>Ctrl: changerStatut(demande_id, "SOUMIS")
    Ctrl->>DemDAO: update(statut="SOUMIS")
    DemDAO-->>Ctrl: ok
    Ctrl->>Notif: envoyerEmail(citoyen.email, "Demande soumise")
    Notif-->>Ctrl: envoyé
    Ctrl-->>IHM: confirmationSoumission()
    IHM-->>Citoyen: afficherConfirmation()
```

---

## 2. Scénario : Valider un dossier par l'Agent Consulaire

Ce diagramme décrit le scénario nominal de validation d'un dossier, incluant la synchronisation avec le RNPP.

```mermaid
sequenceDiagram
    actor Agent as Agent Consulaire
    participant IHM as Back-Office Web
    participant Ctrl as ControleurValidation
    participant DemDAO as Demande
    participant DocDAO as Document
    participant LogDAO as LogAudit
    participant RNPP as RNPP / ONECI
    participant Notif as Service Notification

    Agent->>IHM: accederListeDossiers(statut="SOUMIS")
    IHM->>Ctrl: listerDemandes(statut="SOUMIS")
    Ctrl->>DemDAO: findAll(statut="SOUMIS")
    DemDAO-->>Ctrl: listeDemandes[]
    Ctrl-->>IHM: listeDemandes[]
    IHM-->>Agent: afficherListe()

    Agent->>IHM: ouvrirDossier(demande_id)
    IHM->>Ctrl: getDétails(demande_id)
    Ctrl->>DemDAO: findById(demande_id)
    DemDAO-->>Ctrl: demande
    Ctrl->>DocDAO: findByDemande(demande_id)
    DocDAO-->>Ctrl: documents[]
    Ctrl-->>IHM: détailsDossier(demande, documents[])
    IHM-->>Agent: afficherDétails()

    Agent->>IHM: validerDocument(document_id)
    IHM->>Ctrl: changerStatutDocument(document_id, "VALIDE")
    Ctrl->>DocDAO: update(statut_validation="VALIDE")
    DocDAO-->>Ctrl: ok

    Agent->>IHM: approuverDossier(demande_id)
    IHM->>Ctrl: validerDemande(demande_id, agent_id)

    Ctrl->>DemDAO: update(statut="VALIDE")
    DemDAO-->>Ctrl: ok

    Ctrl->>RNPP: synchroniserDonnées(citoyen_data, demande_data)
    RNPP-->>Ctrl: nni_attribué

    Ctrl->>LogDAO: new LogAudit(agent_id, "APPROBATION_DOSSIER", ip, user_agent)
    LogDAO-->>Ctrl: log_id

    Ctrl->>Notif: envoyerEmail(citoyen.email, "Dossier validé, NNI attribué")
    Notif-->>Ctrl: envoyé

    Ctrl-->>IHM: confirmationValidation(nni)
    IHM-->>Agent: afficherConfirmation()
```

---

## 3. Scénario : Payer les droits de chancellerie

Ce diagramme illustre le flux de paiement en ligne, incluant l'interaction avec le système bancaire externe.

```mermaid
sequenceDiagram
    actor Citoyen
    participant IHM as Interface Web/Mobile
    participant Ctrl as ControleurPaiement
    participant PaiDAO as Paiement
    participant DemDAO as Demande
    participant BNK as Système Bancaire
    participant Notif as Service Notification

    Citoyen->>IHM: accederPaiement(demande_id)
    IHM->>Ctrl: getMontant(demande_id)
    Ctrl->>DemDAO: findById(demande_id)
    DemDAO-->>Ctrl: demande(type_demande)
    Ctrl-->>IHM: montant_calculé, devise="XOF"
    IHM-->>Citoyen: afficherMontantEtMoyens()

    Citoyen->>IHM: choisirMoyenPaiement("MOBILE_MONEY")
    Citoyen->>IHM: saisirInfosPaiement(numéro_mobile)
    IHM->>Ctrl: initierPaiement(demande_id, montant, devise, moyen, infos)

    Ctrl->>PaiDAO: new Paiement(demande_id, montant, devise, moyen, statut="EN_ATTENTE")
    PaiDAO-->>Ctrl: paiement_id

    Ctrl->>BNK: demanderTransaction(montant, devise, moyen, infos)
    
    alt Transaction réussie
        BNK-->>Ctrl: {statut: "REUSSI", reference: "TXN-2026-ABC123"}
        Ctrl->>PaiDAO: update(statut="REUSSI", reference_transaction="TXN-2026-ABC123")
        PaiDAO-->>Ctrl: ok
        Ctrl->>Notif: envoyerEmail(citoyen.email, "Paiement confirmé", reçu)
        Notif-->>Ctrl: envoyé
        Ctrl-->>IHM: confirmationPaiement(reference)
        IHM-->>Citoyen: afficherReçu()
    else Transaction échouée
        BNK-->>Ctrl: {statut: "ECHOUE", motif: "Solde insuffisant"}
        Ctrl->>PaiDAO: update(statut="ECHOUE")
        PaiDAO-->>Ctrl: ok
        Ctrl-->>IHM: erreurPaiement(motif)
        IHM-->>Citoyen: afficherErreur("Solde insuffisant. Veuillez réessayer.")
    end
```

---

## 4. Correspondance Messages / Opérations du Diagramme de Classes

Conformément au cours UML (Chap. III §II.6, p.51), les messages synchrones correspondent à des opérations dans le diagramme de classes. Voici la correspondance :

| Message du diagramme de séquence | Classe propriétaire | Opération correspondante |
| :--- | :--- | :--- |
| `creerDemande(type)` | ControleurDemande | `+creerDemande(type: string): Demande` |
| `creerOuRecupererCitoyen(données)` | Citoyen | `+creerOuRecuperer(données: dict): Citoyen` |
| `ajouterDocument(demande_id, fichier, type)` | ControleurDemande | `+ajouterDocument(demandeId: UUID, fichier: File, type: string): Document` |
| `analyser(chemin_fichier)` | ServiceOCR | `+analyser(chemin: string): JSON` |
| `changerStatut(demande_id, statut)` | Demande | `+changerStatut(statut: string): void` |
| `validerDemande(demande_id, agent_id)` | ControleurValidation | `+validerDemande(demandeId: UUID, agentId: UUID): void` |
| `synchroniserDonnées(citoyen_data, demande_data)` | InterfaceRNPP | `+synchroniser(citoyen: Citoyen, demande: Demande): string` |
| `initierPaiement(demande_id, montant, devise, moyen, infos)` | ControleurPaiement | `+initierPaiement(demandeId: UUID, montant: Decimal, ...): Paiement` |
| `demanderTransaction(montant, devise, moyen, infos)` | InterfaceBancaire | `+demanderTransaction(montant: Decimal, ...): TransactionResult` |
