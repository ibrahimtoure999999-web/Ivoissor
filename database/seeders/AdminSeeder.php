<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@ivoissor.ci'],
            [
                'name' => 'Administrateur Système',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $roleAdmin = Role::where('name', RoleEnum::ADMIN->value)->first();
        if ($roleAdmin && !$admin->roles()->where('role_id', $roleAdmin->id)->exists()) {
            $admin->roles()->attach($roleAdmin->id);
        }

        // Création de l'Agent de test
        $agent = User::firstOrCreate(
            ['email' => 'agent@ivoissor.ci'],
            [
                'name' => 'Agent Consulaire Test',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $roleAgent = Role::where('name', RoleEnum::AGENT->value)->first();
        if ($roleAgent && !$agent->roles()->where('role_id', $roleAgent->id)->exists()) {
            $agent->roles()->attach($roleAgent->id);
        }
    }
}
