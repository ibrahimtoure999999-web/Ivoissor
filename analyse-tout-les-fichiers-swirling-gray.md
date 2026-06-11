# Plan d'implémentation — Ivoissor (Enregistrement des Ressortissants Ivoiriens)

## Contexte

Le projet `Projet_Stage` est une **installation Laravel 13 vierge** (PHP 8.3, SQLite, Blade + Tailwind CSS 4, aucun modèle hormis `User`, aucune route hormis `/`). L'objectif (spécification fonctionnelle PDF + organigramme) est de bâtir l'application **« Ivorssor »** permettant à des agents d'enregistrer des ressortissants ivoiriens, rattachés à deux hiérarchies **indépendantes** :

- **Structure administrative officielle (ANStat)** — arbre strict.
- **Structure coutumière / traditionnelle** — arbre strict séparé.

Les deux arbres ne sont pas liés entre eux (l'organigramme indique « liens possibles mais non hiérarchiques ») ; ils convergent uniquement sur l'entité centrale **Ressortissant**.

Le fichier `.cursorrules` impose les conventions « Laravel Expert » : `declare(strict_types=1)`, typage strict des retours, **Form Requests obligatoires** (jamais `$request->validate()`), **contrôleurs fins** + **Services** pour la logique métier, **eager loading** systématique, Clean Code.

### Décisions validées avec l'utilisateur
1. **Authentification** : oui, via **Laravel Breeze** (stack Blade). Accès aux pages métier réservé aux agents connectés.
2. **Hiérarchie administrative à 5 niveaux** : ajout de **Commune / Ville** sous Sous-préfecture (comme l'organigramme).
3. **Hiérarchie coutumière à 4 niveaux** : **Groupe ethnique** est un **niveau hiérarchique séparé** au-dessus du Canton.
4. **Référentiels** : **CRUD d'administration complet** (créer/modifier/supprimer chaque référentiel dans l'app), en plus du préchargement par seeders.

---

## 1. Schéma de base de données

### Principe de normalisation (à valoriser dans le rapport)
- Chaque table conserve la **PK technique Laravel `id`** (`bigIncrements`) ET les **codes métier ANStat** (`code_district`, `cod_reg`…) en colonnes `unique` indexées (traçabilité). Les FK pointent vers `id`.
- Le **Ressortissant ne stocke que les FK feuilles** : `commune_id` (admin) et `village_id` (coutumier). Les niveaux supérieurs se déduisent par remontée des relations (3NF respectée, pas de redondance). Affichage du chemin complet via eager loading.
- **Résidence** et **Autres informations** = colonnes inline sur `ressortissants` (relation 1‑1 stricte, aucune cardinalité multiple → pas de table séparée).

### Ordre des migrations (dépendances FK — parents avant enfants)
Préfixes timestamp croissants, après les migrations système `0001_01_01_*` :

1. `create_districts_table`
2. `create_regions_table`
3. `create_departements_table`
4. `create_sous_prefectures_table`
5. `create_communes_table`
6. `create_groupes_ethniques_table`
7. `create_cantons_table`
8. `create_tribus_table`
9. `create_villages_table`
10. `create_ressortissants_table`

Toutes dans `database/migrations/`.

### Colonnes

**`districts`**
```
id  bigIncrements
code_district  string unique
nom_district   string
annee          unsignedSmallInteger nullable
timestamps
```
**`regions`** : `id`, `cod_reg` (unique), `nom_reg`, `district_id` (FK→districts, restrictOnDelete), `annee` (nullable), timestamps.
**`departements`** : `id`, `cod_dep` (unique), `nom_dep`, `region_id` (FK→regions, restrictOnDelete), `annee` (nullable), timestamps.
**`sous_prefectures`** : `id`, `cod_sp` (unique), `nom_sp`, `departement_id` (FK→departements, restrictOnDelete), `annee` (nullable), timestamps.
**`communes`** : `id`, `code_commune` (unique, nullable), `nom_commune`, `sous_prefecture_id` (FK→sous_prefectures, restrictOnDelete), `annee` (nullable), timestamps.
**`groupes_ethniques`** : `id`, `nom` (unique), timestamps. *(ex. Akan)*
**`cantons`** : `id`, `nom`, `groupe_ethnique_id` (FK→groupes_ethniques, restrictOnDelete), timestamps.
**`tribus`** : `id`, `nom`, `canton_id` (FK→cantons, restrictOnDelete), timestamps.
**`villages`** : `id`, `nom`, `tribu_id` (FK→tribus, restrictOnDelete), timestamps.

**`ressortissants`**
```
id  bigIncrements
// Identité
nom                    string
prenoms                string
telephone              string nullable
sexe                   string(1)            // 'M' / 'F' (cast enum Sexe)
date_naissance         date nullable
lieu_naissance         string nullable
famille                string nullable      // nom de famille / groupe familial
// Rattachement administratif (FK feuille = Commune)
commune_id             foreignId→communes  nullable nullOnDelete
// Rattachement coutumier (FK feuille = Village)
village_id             foreignId→villages  nullable nullOnDelete
// Résidence (1-1 inline)
pays_residence         string nullable
ville_residence        string nullable
quartier_residence     string nullable
adresse_complete       text   nullable
village_residence_id   foreignId→villages  nullable nullOnDelete   // distinct de village_id
// Autres informations (optionnel)
profession                    string nullable
situation_matrimoniale        string nullable      // cast enum
niveau_etude                  string nullable      // cast enum
informations_complementaires  text   nullable
timestamps
```
Notes : FK feuilles **nullable** (`nullOnDelete`) — un ressortissant peut être saisi sans rattachement complet. `village_residence_id` ≠ `village_id`.

### Enums PHP — `app/Enums/`
`Sexe.php`, `SituationMatrimoniale.php`, `NiveauEtude.php` (backed enums `string`). Servent à la fois aux `casts()` des modèles, aux `<option>` des selects, et à la validation (`Rule::enum()`).

---

## 2. Modèles Eloquent — `app/Models/`

Tous : `declare(strict_types=1)`, `$fillable` explicite, `casts()` typé, relations avec type de retour.

- `District` → `hasMany(Region)`
- `Region` → `belongsTo(District)`, `hasMany(Departement)`
- `Departement` → `belongsTo(Region)`, `hasMany(SousPrefecture)`
- `SousPrefecture` → `belongsTo(Departement)`, `hasMany(Commune)`
- `Commune` → `belongsTo(SousPrefecture)`, `hasMany(Ressortissant)`
- `GroupeEthnique` → `hasMany(Canton)`
- `Canton` → `belongsTo(GroupeEthnique)`, `hasMany(Tribu)`
- `Tribu` → `belongsTo(Canton)`, `hasMany(Village)`
- `Village` → `belongsTo(Tribu)`, `hasMany(Ressortissant)`
- `Ressortissant` :
  - `belongsTo(Commune)`
  - `belongsTo(Village)` (rattachement coutumier)
  - `villageResidence()` → `belongsTo(Village, 'village_residence_id')`
  - `casts()` : `date_naissance => date`, `sexe => Sexe::class`, `situation_matrimoniale => SituationMatrimoniale::class`, `niveau_etude => NiveauEtude::class`
  - Accesseurs optionnels : `nom_complet`, `chemin_administratif`, `chemin_coutumier`.

---

## 3. Seeders — `database/seeders/`

Données de référence **réelles**, pas du Faker.

- **Admin** : alimenter depuis des fichiers JSON embarqués (`database/data/*.json`) lus via `File::get` + `json_decode`. Cibler les **14 districts + ~33 régions complets** (découpage CI 2021, dont districts autonomes Abidjan & Yamoussoukro), puis un **échantillon** documenté de départements/sous-préfectures/communes (ex. District des Lagunes / Abidjan complet) pour ne pas bloquer la livraison.
- **Coutumier** : échantillon réaliste documenté — Groupe ethnique **Akan** → Canton **Akyé** → Tribu → Village **d'Angré**, + 2‑3 autres exemples.
- Idempotence : `updateOrCreate` sur les codes/noms uniques (re-seed sans doublon).
- Fichiers : `DistrictSeeder`, `RegionSeeder`, `DepartementSeeder`, `SousPrefectureSeeder`, `CommuneSeeder`, `GroupeEthniqueSeeder`, `CantonSeeder`, `TribuSeeder`, `VillageSeeder`, `RessortissantSeeder` (démo optionnel), + un **AgentSeeder** (compte agent de connexion).
- `DatabaseSeeder` : `$this->call([...])` dans l'ordre des dépendances ; conserver/adapter le `User::factory()` existant.
- Optionnel : `database/factories/RessortissantFactory.php` (Faker locale `fr_FR`).

---

## 4. Contrôleurs — `app/Http/Controllers/`

Contrôleurs **fins** ; logique de persistance déléguée aux **Services**.

- `RessortissantController` (resource complet : index/create/store/show/edit/update/destroy). `index` avec eager loading `with(['commune.sousPrefecture.departement.region.district', 'village.tribu.canton.groupeEthnique'])`, pagination, recherche (nom/prénoms/téléphone).
- **CRUD référentiels** (resource controllers, lecture+écriture) : `DistrictController`, `RegionController`, `DepartementController`, `SousPrefectureController`, `CommuneController`, `GroupeEthniqueController`, `CantonController`, `TribuController`, `VillageController`. Regroupés sous un préfixe `admin/` (ou `referentiels/`).
- `Api/LocalisationController` — endpoints AJAX pour selects en cascade (JSON, retours `JsonResponse`) :
  - `regions(District)`, `departements(Region)`, `sousPrefectures(Departement)`, `communes(SousPrefecture)`
  - `cantons(GroupeEthnique)`, `tribus(Canton)`, `villages(Tribu)`

- `app/Services/RessortissantService.php` : `creer(array $data): Ressortissant`, `mettreAJour(Ressortissant $r, array $data): Ressortissant`. (Services référentiels optionnels — CRUD simple peut rester dans les contrôleurs via Form Requests.)

---

## 5. Form Requests — `app/Http/Requests/`

Obligatoires (`.cursorrules`). Messages/attributs **en français**.

- `StoreRessortissantRequest` / `UpdateRessortissantRequest` (règles partagées via méthode commune ou trait) :
```
nom, prenoms               required|string|max:255
telephone                  nullable|string|max:30
sexe                       required + Rule::enum(Sexe::class)
date_naissance             nullable|date|before:today
lieu_naissance, famille    nullable|string|max:255
commune_id                 nullable|exists:communes,id
village_id                 nullable|exists:villages,id
pays/ville/quartier_residence  nullable|string|max:255
adresse_complete           nullable|string
village_residence_id       nullable|exists:villages,id
profession                 nullable|string|max:255
situation_matrimoniale     nullable + Rule::enum(...)
niveau_etude               nullable + Rule::enum(...)
informations_complementaires  nullable|string
```
- Form Requests Store/Update pour **chaque référentiel** (ex. `StoreRegionRequest` : `cod_reg` unique, `nom_reg` required, `district_id` exists…).
- `authorize()` : `return true;` (autorisation par middleware `auth`).
- Localisation FR : `lang/fr/validation.php` + `messages()`/`attributes()`.
- Bonus optionnel : `withValidator` vérifiant la cohérence de la chaîne (le village choisi appartient bien à la tribu/canton sélectionnés).

---

## 6. Routes — `routes/web.php`

```php
Route::get('/', fn () => redirect()->route('ressortissants.index'));

Route::middleware('auth')->group(function () {
    Route::resource('ressortissants', RessortissantController::class);

    // CRUD référentiels
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::resource('districts', DistrictController::class);
        Route::resource('regions', RegionController::class);
        // ... departements, sous-prefectures, communes,
        //     groupes-ethniques, cantons, tribus, villages
    });

    // Endpoints AJAX cascade (GET, route model binding par id)
    Route::prefix('localisation')->name('localisation.')->group(function () {
        Route::get('regions/{district}', [LocalisationController::class, 'regions'])->name('regions');
        Route::get('departements/{region}', ...)->name('departements');
        Route::get('sous-prefectures/{departement}', ...)->name('sousPrefectures');
        Route::get('communes/{sousPrefecture}', ...)->name('communes');
        Route::get('cantons/{groupeEthnique}', ...)->name('cantons');
        Route::get('tribus/{canton}', ...)->name('tribus');
        Route::get('villages/{tribu}', ...)->name('villages');
    });
});
```
`routes/auth.php` (Breeze) inclus automatiquement.

---

## 7. Vues Blade + selects en cascade — `resources/views/`

### Layout & assets
- `layouts/app.blade.php` — `<html lang="fr">`, `@vite(['resources/css/app.css','resources/js/app.js'])`, nav FR (Ressortissants, Référentiels, Déconnexion).
- Composants partagés `resources/views/components/` : `input`, `select`, `alert` (messages flash succès/erreur en français).

### Ressortissants
- `ressortissants/index.blade.php` — tableau Tailwind (Nom, Prénoms, Téléphone, Commune, Village, actions), recherche, pagination, bouton « Nouvel enregistrement ».
- `ressortissants/_form.blade.php` (partial partagé create/edit) en **fieldsets** reflétant la spec : Identité · Rattachement administratif · Rattachement coutumier · Résidence · Autres informations.
- `ressortissants/create.blade.php`, `edit.blade.php` (incluent `_form`), `show.blade.php` (fiche lecture + chemins admin/coutumier complets).

### Référentiels
- Sous-dossiers `referentiels/{districts,regions,...}/` avec `index` + `_form` (create/edit). Le `_form` d'un référentiel enfant utilise les mêmes selects en cascade pour choisir le parent.

### Selects en cascade — AJAX + vanilla JS (recommandé)
Deux chaînes **indépendantes** :
- Admin : District → Région → Département → Sous-préfecture → Commune
- Coutumier : Groupe ethnique → Canton → Tribu → Village

`resources/js/cascade.js` (importé dans `resources/js/app.js`) :
- Au `change` d'un select parent → `fetch('/localisation/...')` → vider + repeupler le `<select>` enfant, désactiver/réinitialiser les selects en aval.
- Endpoints en **GET** (pas de CSRF requis).
- **Mode edit + `old()`** (point délicat) : passer les valeurs initiales via attributs `data-*` sur chaque `<select>` ; au `DOMContentLoaded`, déclencher en séquence les fetchs pour pré-remplir et présélectionner chaque niveau.

### Tailwind 4
Pas de `tailwind.config.js` ; classes utilitaires directement dans le Blade. Vérifier que les vues sont scannées — ajouter `@source '../views';` dans `resources/css/app.css` au besoin.

### Auth (Breeze)
`resources/views/auth/*` — traduire login/register/reset en français.

---

## 8. Authentification — Laravel Breeze

- `composer require laravel/breeze --dev` puis `php artisan breeze:install blade`.
- Publie `routes/auth.php`, contrôleurs/vues auth, applique le middleware `auth`.
- Modèle `User` existant = compte **agent**. Créer un agent de démo via `AgentSeeder` (ou Tinker). Option : retirer la route `register` publique (création d'agents en interne).
- Envelopper toutes les routes métier dans `middleware('auth')`.

---

## Plan d'exécution phasé

**Phase 0 — Config**
- `.env` : `APP_NAME=Ivoissor`, `APP_LOCALE=fr`, `APP_FAKER_LOCALE=fr_FR`.
- `lang/fr/validation.php` (traductions FR).
- `<html lang="fr">` dans le layout.

**Phase 1 — Référentiels (BDD)**
- Migrations 1→9 (districts → villages) dans l'ordre FK.
- Modèles + relations des 9 référentiels.
- Enums PHP (`Sexe`, `SituationMatrimoniale`, `NiveauEtude`).
- Données JSON + seeders référentiels + branchement `DatabaseSeeder`.
- `php artisan migrate:fresh --seed` pour valider.

**Phase 2 — Entité centrale**
- Migration `ressortissants` (10) + modèle `Ressortissant` (relations, casts).
- Factory + seeder démo (optionnel).

**Phase 3 — Authentification**
- Installer Breeze, traduire les vues auth, `AgentSeeder`.

**Phase 4 — CRUD Ressortissant**
- `RessortissantService`, Form Requests Store/Update (messages FR).
- `RessortissantController` (resource, eager loading), `LocalisationController` (AJAX).
- Routes (resource + groupes `referentiels`/`localisation` sous `auth`).

**Phase 5 — CRUD Référentiels**
- 9 resource controllers + Form Requests + vues index/_form (avec cascade pour le parent).

**Phase 6 — Vues & UX**
- Layout + nav FR, `index`/`_form`/`create`/`edit`/`show` ressortissants.
- `cascade.js` (selects dépendants, gestion `old()` et mode edit).
- Messages flash FR (composant `alert`).

**Phase 7 — Finitions**
- Tests feature basiques (création ressortissant, validation, cascade).
- `php artisan pint` (déjà en dev dependency).

---

## Points de vigilance
- **Ordre des migrations / FK SQLite** : parents avant enfants (10 tables).
- **Cascade JS en mode edit + `old()`** : partie la plus délicate — initialiser via `data-*` + fetchs au chargement.
- **`village_id` vs `village_residence_id`** : deux champs distincts (origine coutumière vs résidence actuelle).
- **Volume ANStat** : districts + régions complets ; départements/sous‑préfectures/communes en échantillon documenté.
- **Deux arbres indépendants** : admin et coutumier ne se référencent jamais ; ils ne se croisent que sur `Ressortissant`.
- **Tailwind 4** : vérifier le scan de `resources/views`.

## Fichiers critiques
- `database/migrations/` (10 migrations, surtout `create_ressortissants_table`)
- `app/Models/Ressortissant.php`
- `app/Http/Controllers/RessortissantController.php` (+ `Api/LocalisationController.php`)
- `app/Services/RessortissantService.php`
- `app/Http/Requests/StoreRessortissantRequest.php` / `UpdateRessortissantRequest.php`
- `resources/views/ressortissants/_form.blade.php` + `resources/js/cascade.js`
- `routes/web.php`

## Vérification (end-to-end)
1. `php artisan migrate:fresh --seed` → 10 tables créées, référentiels + agent de démo chargés sans erreur FK.
2. `php artisan serve` (ou Herd) → `/` redirige vers `/login` (auth) puis `/ressortissants`.
3. Se connecter avec l'agent de démo.
4. Créer un ressortissant : vérifier les cascades admin (District→…→Commune) et coutumière (Groupe ethnique→…→Village) qui se filtrent en AJAX.
5. Soumettre avec un champ requis manquant → messages d'erreur **en français**.
6. Éditer le ressortissant → vérifier que les selects en cascade se **pré-remplissent** correctement.
7. Tester un CRUD référentiel (ex. créer une Région en choisissant son District).
8. `show` → chemins administratif et coutumier complets affichés.
9. `php artisan test` (tests feature) + `php artisan pint`.
