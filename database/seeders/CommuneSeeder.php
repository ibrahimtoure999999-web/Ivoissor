<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SousPrefecture;
use App\Models\Commune;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CommuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('data/communes.json');

        if (!File::exists($path)) {
            return;
        }

        $communes = json_decode(File::get($path), true);

        foreach ($communes as $comData) {
            $sousPrefecture = SousPrefecture::query()
                ->where('cod_sp', $comData['cod_sp'])
                ->first();

            if ($sousPrefecture) {
                Commune::query()->updateOrCreate(
                    ['code_commune' => $comData['code_commune']],
                    [
                        'sous_prefecture_id' => $sousPrefecture->id,
                        'nom_commune'        => $comData['nom_commune'],
                        'annee'               => $comData['annee'] ?? null,
                        'latitude'            => $comData['latitude'] ?? null,
                        'longitude'           => $comData['longitude'] ?? null,
                        'population'          => $comData['population'] ?? null,
                    ]
                );
            }
        }
    }
}
