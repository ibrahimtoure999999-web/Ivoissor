<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Demande;
use App\Models\Citoyen;
use App\Models\RendezVous;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (RoleEnum::cases() as $role) {
        Role::firstOrCreate(
            ['name' => $role->value],
            ['description' => 'Rôle ' . $role->label()]
        );
    }

    $this->user = User::factory()->create();
    $roleCitoyen = Role::where('name', RoleEnum::CITOYEN->value)->first();
    $this->user->roles()->attach($roleCitoyen->id);

    // Citoyen et demande de base
    $this->citoyen = Citoyen::create([
        'nom'               => 'DIALLO',
        'prenoms'           => 'Bintou',
        'date_naissance'    => '1995-03-12',
        'lieu_naissance'    => 'Korhogo',
        'genre'             => 'F',
        'pays_residence'    => 'France',
        'adresse_residence' => '5 Rue des Lilas, Paris',
        'telephone'         => '+33611223344',
    ]);

    $this->demande = Demande::create([
        'user_id'     => $this->user->id,
        'citoyen_id'  => $this->citoyen->id,
        'type_demande'=> 'PASSEPORT',
        'statut'      => 'SOUMIS',
    ]);
});

// ── Helper pour un payload valide ──────────────────────────────────────────
function validRdvPayload(?string $date = null, string $creneau = '10:00'): array
{
    // Prochain lundi
    $ts = strtotime('next monday');
    $nextMonday = date('Y-m-d', $ts);

    return [
        'lieu'    => "Consulat Général de Côte d'Ivoire à Paris",
        'date'    => $date ?? $nextMonday,
        'creneau' => $creneau,
    ];
}

// ─────────────────────────────────────────────────────────────────────────────
// Accès et autorisation
// ─────────────────────────────────────────────────────────────────────────────

test('guest cannot access the rendez-vous booking form', function () {
    $this->get(route('rendezvous.create', $this->demande->id))
        ->assertRedirect(route('login'));
});

test('auth user can view the rendez-vous booking form for their own demand', function () {
    $this->actingAs($this->user)
        ->get(route('rendezvous.create', $this->demande->id))
        ->assertStatus(200)
        ->assertSee('Prendre Rendez-vous');
});

test('user cannot view the rendez-vous form for another user demand', function () {
    $otherUser = User::factory()->create();
    $citoyen2  = Citoyen::create([
        'nom' => 'AUTRE', 'prenoms' => 'User', 'date_naissance' => '1990-01-01',
        'lieu_naissance' => 'Abidjan', 'genre' => 'M',
        'pays_residence' => 'Belgique', 'adresse_residence' => 'Rue X', 'telephone' => '+3200000000',
    ]);
    $autresDemande = Demande::create([
        'user_id' => $otherUser->id, 'citoyen_id' => $citoyen2->id,
        'type_demande' => 'PASSEPORT', 'statut' => 'SOUMIS',
    ]);

    $this->actingAs($this->user)
        ->get(route('rendezvous.create', $autresDemande->id))
        ->assertStatus(403);
});

// ─────────────────────────────────────────────────────────────────────────────
// Validation du formulaire
// ─────────────────────────────────────────────────────────────────────────────

test('booking fails without required fields', function () {
    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), [])
        ->assertSessionHasErrors(['lieu', 'date', 'creneau']);
});

test('booking fails for an invalid/fantasy location', function () {
    $payload = validRdvPayload();
    $payload['lieu'] = "Consulat de la Lune";

    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload)
        ->assertSessionHasErrors(['lieu']);
});

test('booking fails for a past date', function () {
    $payload = validRdvPayload(date('Y-m-d', strtotime('-1 day')));

    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload)
        ->assertSessionHasErrors(['date']);
});

test('booking fails for a weekend date', function () {
    $ts = strtotime('next saturday');
    $saturday = date('Y-m-d', $ts);

    $payload = validRdvPayload($saturday);

    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload)
        ->assertSessionHasErrors(['date']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Création de rendez-vous
// ─────────────────────────────────────────────────────────────────────────────

test('citizen can book a rendez-vous successfully', function () {
    $payload = validRdvPayload();

    $response = $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    $response->assertRedirect(route('demandes.show', $this->demande->id));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('rendez_vous', [
        'demande_id' => $this->demande->id,
        'lieu'       => $payload['lieu'],
        'statut'     => 'PLANIFIE',
    ]);
});

test('citizen cannot book a second rendez-vous on the same demand', function () {
    $payload = validRdvPayload();

    // Premier rendez-vous
    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    // Tentative de double réservation
    $this->actingAs($this->user)
        ->get(route('rendezvous.create', $this->demande->id))
        ->assertStatus(400);
});

test('duplicate slot is rejected (same date, time and lieu)', function () {
    // Un autre utilisateur prend le même créneau
    $otherUser = User::factory()->create();
    $roleCitoyen = Role::where('name', RoleEnum::CITOYEN->value)->first();
    $otherUser->roles()->attach($roleCitoyen->id);

    $citoyen2 = Citoyen::create([
        'nom' => 'YAPI', 'prenoms' => 'Konan', 'date_naissance' => '1988-07-10',
        'lieu_naissance' => 'Divo', 'genre' => 'M',
        'pays_residence' => 'France', 'adresse_residence' => 'Rue Y, Paris', 'telephone' => '+33622334455',
    ]);
    $demande2 = Demande::create([
        'user_id' => $otherUser->id, 'citoyen_id' => $citoyen2->id,
        'type_demande' => 'PASSEPORT', 'statut' => 'SOUMIS',
    ]);

    $payload = validRdvPayload();

    // Le premier utilisateur réserve
    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    // Le second tente de prendre le même créneau
    $this->actingAs($otherUser)
        ->post(route('rendezvous.store', $demande2->id), $payload)
        ->assertSessionHasErrors(['creneau']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Annulation
// ─────────────────────────────────────────────────────────────────────────────

test('citizen can cancel their rendez-vous and free the slot', function () {
    $payload = validRdvPayload();

    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    $rdv = RendezVous::where('demande_id', $this->demande->id)->first();
    expect($rdv)->not->toBeNull();

    $this->actingAs($this->user)
        ->delete(route('rendezvous.destroy', $rdv->id))
        ->assertRedirect(route('demandes.show', $this->demande->id))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('rendez_vous', [
        'id' => $rdv->id,
        'statut' => 'ANNULE',
    ]);
});

test('user cannot cancel another user rendez-vous', function () {
    $payload = validRdvPayload();

    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    $rdv = RendezVous::where('demande_id', $this->demande->id)->first();

    $otherUser = User::factory()->create();
    $this->actingAs($otherUser)
        ->delete(route('rendezvous.destroy', $rdv->id))
        ->assertStatus(403);
});

// ─────────────────────────────────────────────────────────────────────────────
// API créneaux occupés
// ─────────────────────────────────────────────────────────────────────────────

test('occupied slots endpoint returns booked times as JSON', function () {
    $payload = validRdvPayload();

    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    $response = $this->actingAs($this->user)->getJson(
        route('rendezvous.occupied-slots') . '?date=' . $payload['date'] . '&lieu=' . urlencode($payload['lieu'])
    );

    $response->assertOk();
    $response->assertJsonFragment([$payload['creneau']]);
});

test('citizen can view the list of their rendez-vous', function () {
    $payload = validRdvPayload();
    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    $response = $this->actingAs($this->user)
        ->get(route('rendezvous.index'))
        ->assertStatus(200)
        ->assertSee('Mes Rendez-vous Consulaires')
        ->assertSee($payload['lieu']);
});

test('booking is blocked (400) if demand is not in SOUMIS or INSTRUCTION status', function () {
    $this->demande->update(['statut' => 'VALIDE']);

    $payload = validRdvPayload();
    
    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload)
        ->assertStatus(400);

    $this->actingAs($this->user)
        ->get(route('rendezvous.create', $this->demande->id))
        ->assertStatus(400);
});

test('cancellation is blocked (400) if rendezvous status is not PLANIFIE', function () {
    $payload = validRdvPayload();
    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    $rdv = RendezVous::where('demande_id', $this->demande->id)->first();
    expect($rdv)->not->toBeNull();

    // Simuler le passage à CONFIRME par un agent
    $rdv->update(['statut' => 'CONFIRME']);

    $this->actingAs($this->user)
        ->delete(route('rendezvous.destroy', $rdv->id))
        ->assertStatus(400);
});

test('booking Washington consulate stores in UTC and reads in Eastern Time', function () {
    $ts = strtotime('next monday');
    $nextMonday = date('Y-m-d', $ts);

    $payload = [
        'lieu'    => "Ambassade de Côte d'Ivoire à Washington",
        'date'    => $nextMonday,
        'creneau' => '09:00',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    $response->assertRedirect(route('demandes.show', $this->demande->id));

    // Retrieve from DB directly to check raw UTC format (which is stored in UTC)
    $rawRdv = \Illuminate\Support\Facades\DB::table('rendez_vous')->where('demande_id', $this->demande->id)->first();
    
    $expectedUtcTime = \Illuminate\Support\Carbon::createFromFormat('Y-m-d H:i:s', $nextMonday . ' 09:00:00', 'America/New_York')->setTimezone('UTC');
    
    expect($rawRdv->date_heure)->toBe($expectedUtcTime->format('Y-m-d H:i:s'));

    // Retrieve via Eloquent model and assert that date_heure returns the local time 09:00
    $rdv = RendezVous::where('demande_id', $this->demande->id)->first();
    expect($rdv->date_heure->format('H:i'))->toBe('09:00');
    expect($rdv->date_heure->timezone->getName())->toBe('America/New_York');
});

test('occupied slots endpoint converts raw date range to UTC and returns local slot times', function () {
    $ts = strtotime('next monday');
    $nextMonday = date('Y-m-d', $ts);

    $payload = [
        'lieu'    => "Ambassade de Côte d'Ivoire à Washington",
        'date'    => $nextMonday,
        'creneau' => '09:00',
    ];

    $this->actingAs($this->user)
        ->post(route('rendezvous.store', $this->demande->id), $payload);

    $response = $this->actingAs($this->user)->getJson(
        route('rendezvous.occupied-slots') . '?date=' . $nextMonday . '&lieu=' . urlencode($payload['lieu'])
    );

    $response->assertOk();
    $response->assertExactJson(['09:00']);
});
