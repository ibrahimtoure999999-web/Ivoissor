<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DepartementSeeder extends Seeder
{
    /**
     * Alimentation de la table des départements avec liaison dynamique.
     */
    public function run(): void
    {
        $path = database_path('data/departements.json');

        if (! File::exists($path)) {
            return;
        }

        $departements = json_decode(File::get($path), true);

        foreach ($departements as $depData) {
            // Extraction dynamique de l'ID parent via le code unique ANStat de la région
            $region = Region::query()
                ->where('code_reg', $depData['cod_reg'])
                ->first();

            if ($region) {
                Departement::query()->updateOrCreate(
                    ['code_dep' => $depData['cod_dep']], // Clé d'idempotence
                    [
                        'region_id' => $region->id, // Liaison physique sécurisée
                        'nom_dep' => $depData['nom_dep'],
                        'annee' => $depData['annee'] ?? null,
                        'latitude' => $depData['latitude'] ?? null,
                        'longitude' => $depData['longitude'] ?? null,
                        'population' => $depData['population'] ?? null,
                    ]
                );
            }
        }
    }
}
