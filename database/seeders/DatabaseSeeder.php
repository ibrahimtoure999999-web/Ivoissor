<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed administrative hierarchy
        $this->call([
            DistrictSeeder::class,
            RegionSeeder::class,
            DepartementSeeder::class,
            SousPrefectureSeeder::class,
            CommuneSeeder::class,
        ]);

        // 2. Seed customary/traditional hierarchy
        $this->call([
            CustomarySeeder::class,
        ]);

        // 3. Seed default agent for login
        User::query()->updateOrCreate(
            ['email' => 'agent@ivoissor.ci'],
            [
                'name' => 'Agent Ivoirien',
                'password' => bcrypt('password'),
            ]
        );
    }
}
