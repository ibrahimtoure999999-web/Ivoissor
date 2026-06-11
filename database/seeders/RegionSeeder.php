<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RegionSeeder extends Seeder
{
    /**
     * Alimentation de la table des régions avec liaison dynamique.
     */
    public function run(): void
    {
        $path = database_path('data/regions.json');

        if (! File::exists($path)) {
            return;
        }

        $regions = json_decode(File::get($path), true);

        foreach ($regions as $regionData) {
            // Extraction dynamique de l'ID parent grâce au code unique ANStat du district
            $district = District::query()
                ->where('code_district', $regionData['code_district'])
                ->first();

            if ($district) {
                Region::query()->updateOrCreate(
                    ['cod_reg' => $regionData['cod_reg']], // Clé d'idempotence
                    [
                        'district_id' => $district->id, // Liaison physique sécurisée
                        'nom_reg' => $regionData['nom_reg'],
                        'annee' => $regionData['annee'] ?? null,
                        'latitude' => $regionData['latitude'] ?? null,
                        'longitude' => $regionData['longitude'] ?? null,
                        'population' => $regionData['population'] ?? null,
                    ]
                );
            }
        }
    }
}
