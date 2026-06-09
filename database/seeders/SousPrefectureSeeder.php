<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\SousPrefecture;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SousPrefectureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('data/sous_prefectures.json');

        if (!File::exists($path)) {
            return;
        }

        $sousPrefectures = json_decode(File::get($path), true);

        foreach ($sousPrefectures as $spData) {
            $departement = Departement::query()
                ->where('code_dep', $spData['cod_dep'])
                ->first();

            if ($departement) {
                SousPrefecture::query()->updateOrCreate(
                    ['cod_sp' => $spData['cod_sp']],
                    [
                        'departement_id' => $departement->id,
                        'nom_sp'         => $spData['nom_sp'],
                        'annee'          => $spData['annee'] ?? null,
                        'latitude'     => $spData['latitude'] ?? null,
                        'longitude'    => $spData['longitude'] ?? null,
                        'population'   => $spData['population'] ?? null,
                    ]
                );
            }
        }
    }
}
