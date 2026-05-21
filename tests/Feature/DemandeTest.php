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

    // Create a default user
    $this->user = User::factory()->create();
    $roleCitoyen = Role::where('name', RoleEnum::CITOYEN->value)->first();
    $this->user->roles()->attach($roleCitoyen->id);
});

test('guest cannot access demand creation form or submit a demand', function () {
    $this->get(route('demandes.create'))->assertRedirect(route('login'));
    $this->post(route('demandes.store'), [])->assertRedirect(route('login'));
});

test('auth user can view demand creation form', function () {
    $response = $this->actingAs($this->user)->get(route('demandes.create'));
    $response->assertStatus(200);
    $response->assertSee('Nouvelle Demande de Document');
});

test('demand creation requires citizen data and files based on type', function () {
    $response = $this->actingAs($this->user)->post(route('demandes.store'), [
        'type_demande' => 'PASSEPORT',
    ]);

    $response->assertSessionHasErrors([
        'nom', 'prenoms', 'date_naissance', 'lieu_naissance', 'genre', 
        'pays_residence', 'adresse_residence', 'telephone', 'nni',
        'extrait_naissance', 'certificat_nationalite', 'justificatif_domicile', 'photo'
    ]);
});

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

    // Should redirect to demands show page
    $demande = Demande::first();
    expect($demande)->not->toBeNull();
    $response->assertRedirect(route('demandes.show', $demande->id));

    // Verify Citoyen is created
    $this->assertDatabaseHas('citoyens', [
        'nom' => 'KOUAMÉ', // uppercase
        'prenoms' => 'Jean Pascal',
        'nni' => '1234567890',
    ]);

    // Verify Demande is created
    $this->assertDatabaseHas('demandes', [
        'id' => $demande->id,
        'user_id' => $this->user->id,
        'type_demande' => 'PASSEPORT',
        'statut' => 'SOUMIS',
    ]);

    // Verify Documents records are created
    $this->assertDatabaseHas('documents', [
        'demande_id' => $demande->id,
        'type_document' => 'extrait_naissance',
    ]);

    // Verify files exist in fake storage
    $documents = $demande->documents;
    expect($documents->count())->toBe(4);
    foreach ($documents as $doc) {
        Storage::disk('local')->assertExists($doc->chemin_fichier);
    }

    // Verify AuditLog
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $this->user->id,
        'action' => 'demande_creation',
    ]);
});

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
