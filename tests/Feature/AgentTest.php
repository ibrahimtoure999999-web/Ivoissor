<?php

use App\Models\AuditLog;
use App\Models\Citoyen;
use App\Models\Demande;
use App\Models\Role;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\DemandeTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Créer les rôles de base
    $this->roleAgent = Role::create(['name' => RoleEnum::AGENT->value, 'description' => 'Agent']);
    $this->roleCitoyen = Role::create(['name' => RoleEnum::CITOYEN->value, 'description' => 'Citoyen']);

    // Créer un utilisateur Agent
    $this->agent = User::factory()->create();
    $this->agent->roles()->attach($this->roleAgent->id);

    // Créer un utilisateur Citoyen normal
    $this->citoyenUser = User::factory()->create();
    $this->citoyenUser->roles()->attach($this->roleCitoyen->id);

    // Créer une demande de test
    $citoyen = Citoyen::create([
        'nom' => 'TEST',
        'prenoms' => 'Citoyen',
        'date_naissance' => '1990-01-01',
        'lieu_naissance' => 'Abidjan',
        'genre' => 'M',
        'pays_residence' => 'France',
        'adresse_residence' => 'Paris',
        'telephone' => '0102030405'
    ]);

    $this->demande = Demande::create([
        'user_id' => $this->citoyenUser->id,
        'citoyen_id' => $citoyen->id,
        'type_demande' => DemandeTypeEnum::PASSEPORT->value,
        'statut' => 'SOUMIS'
    ]);
});

it('interdit l\'acces a l\'espace agent aux citoyens', function () {
    actingAs($this->citoyenUser)
        ->get(route('agent.dashboard'))
        ->assertForbidden();
});

it('autorise l\'acces a l\'espace agent aux agents', function () {
    actingAs($this->agent)
        ->get(route('agent.dashboard'))
        ->assertOk()
        ->assertViewHas('stats')
        ->assertViewHas('dossiersPrioritaires');
});

it('affiche la liste des demandes pour un agent', function () {
    actingAs($this->agent)
        ->get(route('agent.demandes.index'))
        ->assertOk()
        ->assertViewHas('demandes')
        ->assertSee($this->demande->citoyen->nom);
});

it('permet a un agent de mettre un dossier en instruction', function () {
    actingAs($this->agent)
        ->post(route('agent.demandes.instruire', $this->demande->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->demande->fresh()->statut)->toBe('INSTRUCTION');

    // Vérifier l'audit log
    expect(AuditLog::where('action', 'demande_instruction')->exists())->toBeTrue();
});

it('permet a un agent de valider un dossier', function () {
    $this->demande->update(['statut' => 'INSTRUCTION']);

    actingAs($this->agent)
        ->post(route('agent.demandes.valider', $this->demande->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->demande->fresh()->statut)->toBe('VALIDE');

    // Vérifier l'audit log
    expect(AuditLog::where('action', 'demande_validation')->exists())->toBeTrue();
});

it('empeche le rejet d\'un dossier sans motif valide', function () {
    actingAs($this->agent)
        ->post(route('agent.demandes.rejeter', $this->demande->id), [
            'motif_rejet' => 'trop court' // Moins de 10 chars
        ])
        ->assertSessionHasErrors(['motif_rejet']);

    expect($this->demande->fresh()->statut)->toBe('SOUMIS');
});

it('permet a un agent de rejeter un dossier avec un motif valide', function () {
    actingAs($this->agent)
        ->post(route('agent.demandes.rejeter', $this->demande->id), [
            'motif_rejet' => 'Votre photo n\'est pas conforme aux normes.'
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $demandeRafraichie = $this->demande->fresh();
    expect($demandeRafraichie->statut)->toBe('REJETE');
    expect($demandeRafraichie->motif_rejet)->toBe('Votre photo n\'est pas conforme aux normes.');

    // Vérifier l'audit log
    expect(AuditLog::where('action', 'demande_rejet')->exists())->toBeTrue();
});
