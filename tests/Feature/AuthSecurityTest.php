<?php

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles since they are required for registration
    foreach (RoleEnum::cases() as $role) {
        Role::firstOrCreate(
            ['name' => $role->value],
            ['description' => 'Rôle ' . $role->label()]
        );
    }
});

test('registration rejects weak password', function () {
    $response = $this->post('/register', [
        'email' => 'test@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseMissing('users', [
        'email' => 'test@example.com',
    ]);
});

test('registration accepts strong password and assigns citoyen role and logs audit', function () {
    $response = $this->post('/register', [
        'email' => 'citoyen@ivoissor.ci',
        'password' => 'P@ssw0rd_For_Ivoissor_2026_Test!',
        'password_confirmation' => 'P@ssw0rd_For_Ivoissor_2026_Test!',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('users', [
        'email' => 'citoyen@ivoissor.ci',
    ]);

    $user = User::where('email', 'citoyen@ivoissor.ci')->first();
    expect($user->roles->first()->name)->toBe(RoleEnum::CITOYEN->value);

    // Verify audit log
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'register',
    ]);
});

test('login rate limits after 5 failed attempts', function () {
    $email = 'bruteforce@example.com';
    
    // Simulate 5 failed login attempts
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors('email');
    }

    // 6th attempt should trigger rate limiting
    $response = $this->post('/login', [
        'email' => $email,
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('email');
    $errors = session('errors')->get('email');
    expect($errors[0])->toContain('Trop de tentatives de connexion');

    // Check that we logged the blocking
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'login_blocked',
    ]);
});
