# Modèle Logique & Physique des Données (MLD / MPD)

Ce document formalise le passage du modèle conceptuel à la structure relationnelle logique (MLD) puis à sa mise en œuvre physique (MPD) via des scripts DDL SQL et des migrations Laravel.

---

## 1. Modèle Logique des Données (MLD Relationnel)

La traduction du MCD Merise en Modèle Logique se fait selon les règles de normalisation relationnelle :
*   Les entités deviennent des relations (tables).
*   Les associations de cardinalité maximum `(1,1)` ou `(0,1)` se traduisent par la migration de la clé primaire de l'autre entité en tant que clé étrangère (FK).
*   Les associations de cardinalité `(1,N)` à `(1,N)` ou `(0,N)` à `(0,N)` (comme `Avoir_Role`) se traduisent par la création d'une table de jointure.

### Schéma des Relations

1.  **roles** ( **id_role** : INT, *nom_role* : VARCHAR, *description* : VARCHAR )
    *   *Clé primaire :* `id_role`

2.  **users** ( **id_user** : INT/UUID, *email* : VARCHAR, *password* : VARCHAR, *created_at* : DATETIME, *updated_at* : DATETIME )
    *   *Clé primaire :* `id_user`
    *   *Contrainte :* `email` unique

3.  **role_user** ( **#user_id** : INT/UUID, **#role_id** : INT )
    *   *Clé primaire composite :* `(user_id, role_id)`
    *   *Clés étrangères :*
        *   `user_id` référence `users(id_user)`
        *   `role_id` référence `roles(id_role)`

4.  **citoyens** ( **id_citoyen** : INT/UUID, *nni* : VARCHAR, *nom* : VARCHAR, *prenoms* : VARCHAR, *date_naissance* : DATE, *lieu_naissance* : VARCHAR, *genre* : CHAR, *pays_residence* : VARCHAR, *adresse_residence* : VARCHAR, *telephone* : VARCHAR )
    *   *Clé primaire :* `id_citoyen`
    *   *Contrainte :* `nni` unique et nullable

5.  **demandes** ( **id_demande** : INT/UUID, *#user_id* : INT/UUID, *#citoyen_id* : INT/UUID, *type_demande* : VARCHAR, *statut* : VARCHAR, *motif_rejet* : TEXT, *created_at* : DATETIME, *updated_at* : DATETIME )
    *   *Clé primaire :* `id_demande`
    *   *Clés étrangères :*
        *   `user_id` référence `users(id_user)`
        *   `citoyen_id` référence `citoyens(id_citoyen)` (nullable)

6.  **documents** ( **id_document** : INT/UUID, *#demande_id* : INT/UUID, *type_document* : VARCHAR, *chemin_fichier* : VARCHAR, *ocr_data* : JSON, *statut_validation* : VARCHAR )
    *   *Clé primaire :* `id_document`
    *   *Clé étrangère :* `demande_id` référence `demandes(id_demande)`

7.  **rendez_vous** ( **id_rendez_vous** : INT/UUID, *#demande_id* : INT/UUID, *date_heure* : DATETIME, *lieu* : VARCHAR, *statut* : VARCHAR, *#agent_id* : INT/UUID )
    *   *Clé primaire :* `id_rendez_vous`
    *   *Contrainte :* `demande_id` unique (relation 1:1 entre Demande et RDV)
    *   *Clés étrangères :*
        *   `demande_id` référence `demandes(id_demande)`
        *   `agent_id` référence `users(id_user)` (nullable)

8.  **paiements** ( **id_paiement** : INT/UUID, *#demande_id* : INT/UUID, *reference_transaction* : VARCHAR, *montant* : DECIMAL, *devise* : VARCHAR, *statut* : VARCHAR, *moyen_paiement* : VARCHAR, *created_at* : DATETIME )
    *   *Clé primaire :* `id_paiement`
    *   *Contrainte :* `reference_transaction` unique
    *   *Clé étrangère :* `demande_id` référence `demandes(id_demande)`

9.  **audit_logs** ( **id_log** : INT/UUID, *#user_id* : INT/UUID, *action* : VARCHAR, *description* : TEXT, *ip_address* : VARCHAR, *user_agent* : VARCHAR, *created_at* : DATETIME )
    *   *Clé primaire :* `id_log`
    *   *Clé étrangère :* `user_id` référence `users(id_user)` (nullable)

---

## 2. Modèle Physique des Données (MPD - Scripts SQL DDL)

Script SQL de création des tables compatible avec PostgreSQL et MySQL (en utilisant les types standards).

```sql
-- Suppression des tables si elles existent (pour les tests de réinitialisation)
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS paiements;
DROP TABLE IF EXISTS rendez_vous;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS demandes;
DROP TABLE IF EXISTS citoyens;
DROP TABLE IF EXISTS role_user;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

-- 1. Table : roles
CREATE TABLE roles (
    id_role INT AUTO_INCREMENT,
    nom_role VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    CONSTRAINT pk_roles PRIMARY KEY (id_role)
);

-- 2. Table : users
CREATE TABLE users (
    id_user CHAR(36) NOT NULL, -- UUID format
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT pk_users PRIMARY KEY (id_user)
);

-- 3. Table : role_user (Table de liaison RBAC N:N)
CREATE TABLE role_user (
    user_id CHAR(36) NOT NULL,
    role_id INT NOT NULL,
    CONSTRAINT pk_role_user PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_role_user_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_role_user_role FOREIGN KEY (role_id) REFERENCES roles(id_role) ON DELETE CASCADE
);

-- 4. Table : citoyens
CREATE TABLE citoyens (
    id_citoyen CHAR(36) NOT NULL,
    nni VARCHAR(11) NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    prenoms VARCHAR(150) NOT NULL,
    date_naissance DATE NOT NULL,
    lieu_naissance VARCHAR(100) NOT NULL,
    genre CHAR(1) NOT NULL CHECK (genre IN ('M', 'F')),
    pays_residence VARCHAR(100) NOT NULL,
    adresse_residence VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    CONSTRAINT pk_citoyens PRIMARY KEY (id_citoyen)
);

-- 5. Table : demandes
CREATE TABLE demandes (
    id_demande CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    citoyen_id CHAR(36) NULL,
    type_demande VARCHAR(50) NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'BROUILLON',
    motif_rejet TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT pk_demandes PRIMARY KEY (id_demande),
    CONSTRAINT fk_demandes_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE RESTRICT,
    CONSTRAINT fk_demandes_citoyen FOREIGN KEY (citoyen_id) REFERENCES citoyens(id_citoyen) ON DELETE SET NULL
);

-- 6. Table : documents
CREATE TABLE documents (
    id_document CHAR(36) NOT NULL,
    demande_id CHAR(36) NOT NULL,
    type_document VARCHAR(50) NOT NULL,
    chemin_fichier VARCHAR(255) NOT NULL,
    ocr_data JSON NULL,
    statut_validation VARCHAR(20) NOT NULL DEFAULT 'EN_ATTENTE',
    CONSTRAINT pk_documents PRIMARY KEY (id_document),
    CONSTRAINT fk_documents_demande FOREIGN KEY (demande_id) REFERENCES demandes(id_demande) ON DELETE CASCADE
);

-- 7. Table : rendez_vous
CREATE TABLE rendez_vous (
    id_rendez_vous CHAR(36) NOT NULL,
    demande_id CHAR(36) NOT NULL UNIQUE,
    date_heure DATETIME NOT NULL,
    lieu VARCHAR(150) NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'PLANIFIE',
    agent_id CHAR(36) NULL,
    CONSTRAINT pk_rendez_vous PRIMARY KEY (id_rendez_vous),
    CONSTRAINT fk_rendez_vous_demande FOREIGN KEY (demande_id) REFERENCES demandes(id_demande) ON DELETE CASCADE,
    CONSTRAINT fk_rendez_vous_agent FOREIGN KEY (agent_id) REFERENCES users(id_user) ON DELETE SET NULL
);

-- 8. Table : paiements
CREATE TABLE paiements (
    id_paiement CHAR(36) NOT NULL,
    demande_id CHAR(36) NOT NULL,
    reference_transaction VARCHAR(100) NOT NULL UNIQUE,
    montant DECIMAL(10,2) NOT NULL,
    devise VARCHAR(3) NOT NULL DEFAULT 'XOF',
    statut VARCHAR(20) NOT NULL DEFAULT 'EN_ATTENTE',
    moyen_paiement VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_paiements PRIMARY KEY (id_paiement),
    CONSTRAINT fk_paiements_demande FOREIGN KEY (demande_id) REFERENCES demandes(id_demande) ON DELETE RESTRICT
);

-- 9. Table : audit_logs
CREATE TABLE audit_logs (
    id_log CHAR(36) NOT NULL,
    user_id CHAR(36) NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_audit_logs PRIMARY KEY (id_log),
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE SET NULL
);

-- Index pour optimiser les performances des filtres fréquents
CREATE INDEX idx_demandes_statut ON demandes(statut);
CREATE INDEX idx_demandes_user ON demandes(user_id);
CREATE INDEX idx_rendez_vous_date ON rendez_vous(date_heure);
CREATE INDEX idx_audit_logs_created ON audit_logs(created_at);
```

---

## 3. Implémentation Physique : Migrations Laravel

Pour faciliter l'implémentation dans un environnement Laravel, voici la traduction des schémas de table sous forme de fichiers de migration Eloquent (exemples simplifiés).

### 3.1 Migration Users & Roles
```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('email')->unique();
    $table->string('password');
    $table->timestamps();
});

Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique(); // ADMIN, AGENT, CITOYEN
    $table->string('description')->nullable();
});

Schema::create('role_user', function (Blueprint $table) {
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('role_id')->constrained()->cascadeOnDelete();
    $table->primary(['user_id', 'role_id']);
});
```

### 3.2 Migration Citoyens & Demandes
```php
Schema::create('citoyens', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('nni', 11)->nullable()->unique();
    $table->string('nom', 100);
    $table->string('prenoms', 150);
    $table->date('date_naissance');
    $table->string('lieu_naissance', 100);
    $table->char('genre', 1);
    $table->string('pays_residence', 100);
    $table->string('adresse_residence', 255);
    $table->string('telephone', 20);
});

Schema::create('demandes', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
    $table->foreignUuid('citoyen_id')->nullable()->constrained('citoyens')->nullOnDelete();
    $table->string('type_demande', 50);
    $table->string('statut', 20)->default('BROUILLON');
    $table->text('motif_rejet')->nullable();
    $table->timestamps();
});
```

### 3.3 Migration Documents & Services associés
```php
Schema::create('documents', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('demande_id')->constrained()->cascadeOnDelete();
    $table->string('type_document', 50);
    $table->string('chemin_fichier', 255);
    $table->json('ocr_data')->nullable();
    $table->string('statut_validation', 20)->default('EN_ATTENTE');
});

Schema::create('rendez_vous', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('demande_id')->unique()->constrained()->cascadeOnDelete();
    $table->dateTime('date_heure');
    $table->string('lieu', 150);
    $table->string('statut', 20)->default('PLANIFIE');
    $table->foreignUuid('agent_id')->nullable()->constrained('users')->nullOnDelete();
});

Schema::create('paiements', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('demande_id')->constrained()->restrictOnDelete();
    $table->string('reference_transaction', 100)->unique();
    $table->decimal('montant', 10, 2);
    $table->string('devise', 3)->default('XOF');
    $table->string('statut', 20)->default('EN_ATTENTE');
    $table->string('moyen_paiement', 50);
    $table->timestamp('created_at')->useCurrent();
});
```

### 3.4 Migration Logs d'Audit
```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('action', 100);
    $table->text('description')->nullable();
    $table->string('ip_address', 45);
    $table->string('user_agent', 255);
    $table->timestamp('created_at')->useCurrent();
});
```
