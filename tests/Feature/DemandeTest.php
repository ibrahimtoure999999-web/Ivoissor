<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Demande;
use App\Models\Citoyen;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles
    foreach (RoleEnum::cases() as $role) {
        Role::firstOrCreate(
            ['name' => $role->value],
            ['description' => 'Rôle ' . $role->label()]
        );
    }

    // Création d'un utilisateur citoyen pour les tests de soumission de demandes. 
    // Cet utilisateur sera utilisé pour simuler les actions d'un citoyen authentifié dans les différents scénarios de test.

    $this->user = User::factory()->create();
    $roleCitoyen = Role::where('name', RoleEnum::CITOYEN->value)->first();
    $this->user->roles()->attach($roleCitoyen->id);
});

// Ces tests couvrent les scénarios de création de demandes par les citoyens,
// ainsi que les restrictions d'accès pour les utilisateurs non authentifiés et les agents/admins.

test('guest cannot access demand creation form or submit a demand', function () {
    $this->get(route('demandes.create'))->assertRedirect(route('login'));
    $this->post(route('demandes.store'), [])->assertRedirect(route('login'));
});

// Test de l'accès au formulaire de création de demande pour un utilisateur authentifié,
// et vérification que le formulaire contient les éléments attendus pour la création d'une demande de document.

test('auth user can view demand creation form', function () {
    $response = $this->actingAs($this->user)->get(route('demandes.create'));
    $response->assertStatus(200);
    $response->assertSee('Nouvelle Demande de Document');
});

// Test de la validation de la création de demande, en s'assurant que les données requises sont présentes 
// et que les fichiers sont correctement gérés selon le type de demande sélectionné.

test('demand creation requires citizen data and files based on type', function () {
    $this->actingAs($this->user);
    $response = $this->post(route('demandes.store'), [
        'type_demande' => 'PASSEPORT',
    ]);

    $response->assertSessionHasErrors();
});

// Test de la soumission réussie d'une demande de passeport par un citoyen, 
// en vérifiant que les données sont enregistrées en base,
// que les fichiers sont stockés correctement, et que les logs d'audit sont créés pour assurer la traçabilité de l'action.

test('citizen can submit a passport demand with valid files and details', function () {
    Storage::fake('local');

    $extrait = UploadedFile::fake()->create('extrait.pdf', 500, 'application/pdf');
    $nationalite = UploadedFile::fake()->create('nat.jpg', 600, 'image/jpeg');
    $domicile = UploadedFile::fake()->create('domicile.pdf', 300, 'application/pdf');
    $photo = UploadedFile::fake()->image('photo.png', 300, 300);

    $payload = [
        'nom' => 'Kouamé',
        'prenoms' => 'Jean Pascal',
        'date_naissance' => '1990-05-15',
        'lieu_naissance' => 'Bouaké',
        'genre' => 'M',
        'nni' => '1234567890',
        'pays_residence' => 'France',
        'adresse_residence' => '12 Rue de Paris, 75001 Paris',
        'telephone' => '+33612345678',
        'type_demande' => 'PASSEPORT',
        'extrait_naissance' => $extrait,
        'certificat_nationalite' => $nationalite,
        'justificatif_domicile' => $domicile,
        'photo' => $photo,
    ];

    $response = $this->actingAs($this->user)->post(route('demandes.store'), $payload);

    // Récupération de la demande créée pour vérifier les données et les fichiers associés.
    $demande = Demande::first();
    expect($demande)->not->toBeNull();
    $response->assertRedirect(route('demandes.show', $demande->id));
    $response->assertSessionHas('success', 'Votre demande de pré-enrôlement (Passeport & Carte d\'Identité) a été soumise avec succès.');

    // Vérification que les données du citoyen sont enregistrées correctement,
    // avec le nom en majuscules conformément à la logique de normalisation des données.

    $this->assertDatabaseHas('citoyens', [
        'nom' => 'KOUAMÉ', // uppercase
        'prenoms' => 'Jean Pascal',
        'nni' => '1234567890',
    ]);

    // Vérification que la demande est enregistrée avec les bonnes références à l'utilisateur et au citoyen,
    // et que le statut initial est "SOUMIS" comme prévu pour une nouvelle demande.
    
    $this->assertDatabaseHas('demandes', [
        'id' => $demande->id,
        'user_id' => $this->user->id,
        'type_demande' => 'PASSEPORT',
        'statut' => 'SOUMIS',
    ]);

    // Vérification que les enregistrements de documents sont créés
    $this->assertDatabaseHas('documents', [
        'demande_id' => $demande->id,
        'type_document' => 'extrait_naissance',
    ]);

    // Vérification que les fichiers existent dans le stockage fake
    $documents = $demande->documents;
    expect($documents->count())->toBe(4);
    foreach ($documents as $doc) {
        Storage::disk('local')->assertExists($doc->chemin_fichier);
    }

    // Vérification des logs d'audit
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $this->user->id,
        'action' => 'demande_creation',
    ]);
});


// Tests d'accès aux détails d'une demande pour différents rôles (citoyen, agent, admin),
// en s'assurant que les citoyens ne peuvent voir que leurs propres demandes, 
// tandis que les agents et les admins peuvent voir tous les dossiers, 
// conformément aux règles de sécurité et de confidentialité des données.


test('citizen cannot view another user\'s demand details', function () {
    $otherUser = User::factory()->create();
    $citoyen = Citoyen::create([
        'nom' => 'KOUASSI',
        'prenoms' => 'Aya',
        'date_naissance' => '1995-02-10',
        'lieu_naissance' => 'Abidjan',
        'genre' => 'F',
        'pays_residence' => 'Belgique',
        'adresse_residence' => 'Rue Royale, Bruxelles',
        'telephone' => '+3223456789',
    ]);
    
    $demande = Demande::create([
        'user_id' => $otherUser->id,
        'citoyen_id' => $citoyen->id,
        'type_demande' => 'CARTE_CONSULAIRE',
        'statut' => 'SOUMIS',
    ]);

    $this->actingAs($this->user)
        ->get(route('demandes.show', $demande->id))
        ->assertStatus(403);
});

test('citizen can view their own demand details', function () {
    $citoyen = Citoyen::create([
        'nom' => 'KOUASSI',
        'prenoms' => 'Aya',
        'date_naissance' => '1995-02-10',
        'lieu_naissance' => 'Abidjan',
        'genre' => 'F',
        'pays_residence' => 'Belgique',
        'adresse_residence' => 'Rue Royale, Bruxelles',
        'telephone' => '+3223456789',
    ]);
    
    $demande = Demande::create([
        'user_id' => $this->user->id,
        'citoyen_id' => $citoyen->id,
        'type_demande' => 'CARTE_CONSULAIRE',
        'statut' => 'SOUMIS',
    ]);

    $this->actingAs($this->user)
        ->get(route('demandes.show', $demande->id))
        ->assertStatus(200)
        ->assertSee('Détails du Dossier')
        ->assertSee('KOUASSI Aya');
});

test('citizen can submit a consular card demand with cni or passport', function () {
    Storage::fake('local');

    $cniPassport = UploadedFile::fake()->create('cni.pdf', 500, 'application/pdf');
    $domicile = UploadedFile::fake()->create('domicile.pdf', 300, 'application/pdf');
    $photo = UploadedFile::fake()->image('photo.png', 300, 300);
    $recu = UploadedFile::fake()->create('recu.pdf', 200, 'application/pdf');

    $payload = [
        'nom' => 'Koffi',
        'prenoms' => 'Awa',
        'date_naissance' => '1992-08-20',
        'lieu_naissance' => 'Yamoussoukro',
        'genre' => 'F',
        'pays_residence' => 'Canada',
        'adresse_residence' => 'Rue de la Gauchetière, Montréal',
        'telephone' => '+15141234567',
        'type_demande' => 'CARTE_CONSULAIRE',
        'mode_identification' => 'cni_passport',
        'cni_ou_passeport' => $cniPassport,
        'justificatif_domicile' => $domicile,
        'photo' => $photo,
        'recu_paiement' => $recu,
    ];

    $response = $this->actingAs($this->user)->post(route('demandes.store'), $payload);

    $demande = Demande::orderBy('created_at', 'desc')->first();
    expect($demande)->not->toBeNull();
    $response->assertRedirect(route('demandes.show', $demande->id));

    $this->assertDatabaseHas('citoyens', [
        'nom' => 'KOFFI',
        'pays_residence' => 'Canada',
    ]);

    $this->assertDatabaseHas('documents', [
        'demande_id' => $demande->id,
        'type_document' => 'cni_ou_passeport',
    ]);

    $this->assertDatabaseHas('documents', [
        'demande_id' => $demande->id,
        'type_document' => 'recu_paiement',
    ]);
});

test('citizen can submit a consular card demand with birth certificate and nationality certificate', function () {
    Storage::fake('local');

    $extrait = UploadedFile::fake()->create('extrait.pdf', 500, 'application/pdf');
    $nationalite = UploadedFile::fake()->create('nat.pdf', 500, 'application/pdf');
    $domicile = UploadedFile::fake()->create('domicile.pdf', 300, 'application/pdf');
    $photo = UploadedFile::fake()->image('photo.png', 300, 300);
    $recu = UploadedFile::fake()->create('recu.pdf', 200, 'application/pdf');

    $payload = [
        'nom' => 'Koffi',
        'prenoms' => 'Awa',
        'date_naissance' => '1992-08-20',
        'lieu_naissance' => 'Yamoussoukro',
        'genre' => 'F',
        'pays_residence' => 'Canada',
        'adresse_residence' => 'Rue de la Gauchetière, Montréal',
        'telephone' => '+15141234567',
        'type_demande' => 'CARTE_CONSULAIRE',
        'mode_identification' => 'extrait_nationalite',
        'extrait_naissance' => $extrait,
        'certificat_nationalite' => $nationalite,
        'justificatif_domicile' => $domicile,
        'photo' => $photo,
        'recu_paiement' => $recu,
    ];

    $response = $this->actingAs($this->user)->post(route('demandes.store'), $payload);

    $demande = Demande::orderBy('created_at', 'desc')->first();
    expect($demande)->not->toBeNull();
    $response->assertRedirect(route('demandes.show', $demande->id));

    $this->assertDatabaseHas('documents', [
        'demande_id' => $demande->id,
        'type_document' => 'extrait_naissance',
    ]);

    $this->assertDatabaseHas('documents', [
        'demande_id' => $demande->id,
        'type_document' => 'certificat_nationalite',
    ]);
});

test('citizen can submit a transcription of naissance demand with required files', function () {
    Storage::fake('local');

    $acte = UploadedFile::fake()->create('acte.pdf', 500, 'application/pdf');
    $identiteParents = UploadedFile::fake()->create('parents.pdf', 500, 'application/pdf');
    $demandeEcrite = UploadedFile::fake()->create('demande.pdf', 300, 'application/pdf');

    $payload = [
        'nom' => 'Kouadio',
        'prenoms' => 'Marc',
        'date_naissance' => '2020-01-01',
        'lieu_naissance' => 'Dakar',
        'genre' => 'M',
        'pays_residence' => 'Sénégal',
        'adresse_residence' => 'Plateau, Dakar',
        'telephone' => '+221770000000',
        'type_demande' => 'ETAT_CIVIL',
        'sous_type' => 'NAISSANCE',
        'acte_etranger' => $acte,
        'piece_identite_parents' => $identiteParents,
        'demande_ecrite' => $demandeEcrite,
    ];

    $response = $this->actingAs($this->user)->post(route('demandes.store'), $payload);

    $demande = Demande::orderBy('created_at', 'desc')->first();
    expect($demande)->not->toBeNull();
    expect($demande->sous_type)->toBe('NAISSANCE');
    $response->assertRedirect(route('demandes.show', $demande->id));
    $response->assertSessionHas('success', 'Votre demande de transcription d\'état civil (NAISSANCE) a été soumise avec succès.');
});

test('citizen can submit a transcription of mariage demand with required files', function () {
    Storage::fake('local');

    $acte = UploadedFile::fake()->create('acte.pdf', 500, 'application/pdf');
    $identiteEpoux = UploadedFile::fake()->create('epoux.pdf', 500, 'application/pdf');
    $identiteConjoint = UploadedFile::fake()->create('conjoint.pdf', 500, 'application/pdf');
    $demandeEcrite = UploadedFile::fake()->create('demande.pdf', 300, 'application/pdf');

    $payload = [
        'nom' => 'Kouadio',
        'prenoms' => 'Marc',
        'date_naissance' => '1990-01-01',
        'lieu_naissance' => 'Dakar',
        'genre' => 'M',
        'pays_residence' => 'Sénégal',
        'adresse_residence' => 'Plateau, Dakar',
        'telephone' => '+221770000000',
        'type_demande' => 'ETAT_CIVIL',
        'sous_type' => 'MARIAGE',
        'acte_etranger' => $acte,
        'piece_identite_epoux_ivoirien' => $identiteEpoux,
        'piece_identite_conjoint' => $identiteConjoint,
        'demande_ecrite' => $demandeEcrite,
    ];

    $response = $this->actingAs($this->user)->post(route('demandes.store'), $payload);

    $demande = Demande::orderBy('created_at', 'desc')->first();
    expect($demande)->not->toBeNull();
    expect($demande->sous_type)->toBe('MARIAGE');
    $response->assertRedirect(route('demandes.show', $demande->id));
    $response->assertSessionHas('success', 'Votre demande de transcription d\'état civil (MARIAGE) a été soumise avec succès.');
});

test('agent can view any user\'s demand details', function () {
    $citoyen = Citoyen::create([
        'nom' => 'KOUASSI',
        'prenoms' => 'Aya',
        'date_naissance' => '1995-02-10',
        'lieu_naissance' => 'Abidjan',
        'genre' => 'F',
        'pays_residence' => 'Belgique',
        'adresse_residence' => 'Rue Royale, Bruxelles',
        'telephone' => '+3223456789',
    ]);
    
    $demande = Demande::create([
        'user_id' => $this->user->id,
        'citoyen_id' => $citoyen->id,
        'type_demande' => 'CARTE_CONSULAIRE',
        'statut' => 'SOUMIS',
    ]);

    $agent = User::factory()->create();
    $roleAgent = Role::where('name', RoleEnum::AGENT->value)->first();
    $agent->roles()->attach($roleAgent->id);

    $this->actingAs($agent)
        ->get(route('demandes.show', $demande->id))
        ->assertStatus(200)
        ->assertSee('Détails du Dossier')
        ->assertSee('KOUASSI Aya');
});

test('admin can view any user\'s demand details', function () {
    $citoyen = Citoyen::create([
        'nom' => 'KOUASSI',
        'prenoms' => 'Aya',
        'date_naissance' => '1995-02-10',
        'lieu_naissance' => 'Abidjan',
        'genre' => 'F',
        'pays_residence' => 'Belgique',
        'adresse_residence' => 'Rue Royale, Bruxelles',
        'telephone' => '+3223456789',
    ]);
    
    $demande = Demande::create([
        'user_id' => $this->user->id,
        'citoyen_id' => $citoyen->id,
        'type_demande' => 'CARTE_CONSULAIRE',
        'statut' => 'SOUMIS',
    ]);

    $admin = User::factory()->create();
    $roleAdmin = Role::where('name', RoleEnum::ADMIN->value)->first();
    $admin->roles()->attach($roleAdmin->id);

    $this->actingAs($admin)
        ->get(route('demandes.show', $demande->id))
        ->assertStatus(200)
        ->assertSee('Détails du Dossier')
        ->assertSee('KOUASSI Aya');
});
