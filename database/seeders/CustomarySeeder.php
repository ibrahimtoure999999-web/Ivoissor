<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GroupeEthnique;
use App\Models\Canton;
use App\Models\Tribu;
use App\Models\Village;
use Illuminate\Database\Seeder;

class CustomarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hierarchy = [
            'Akan' => [
                'Akyé' => [
                    'region_code' => '30', // La Mé
                    'tribus' => [
                        'Tribu Akyé du Sud' => [
                            'Angré',
                            'Djibi',
                            'Alépé'
                        ]
                    ]
                ],
                'Baoulé' => [
                    'region_code' => '21', // Bélier
                    'tribus' => [
                        'Tribu Baoulé de Yamoussoukro' => [
                            'Kossou',
                            'Kami',
                            'N\'Gokro'
                        ]
                    ]
                ]
            ],
            'Krou' => [
                'Bété' => [
                    'region_code' => '07', // Haut-Sassandra
                    'tribus' => [
                        'Tribu Bété de Daloa' => [
                            'Gadouan',
                            'Gboguhe',
                            'Zaïbo'
                        ]
                    ]
                ]
            ],
            'Gour' => [
                'Sénoufo' => [
                    'region_code' => '28', // Poro
                    'tribus' => [
                        'Tribu Sénoufo de Korhogo' => [
                            'Nafoun',
                            'Komborodougou',
                            'Lataha'
                        ]
                    ]
                ]
            ],
            'Mandé' => [
                'Malinké' => [
                    'region_code' => '24', // Kabadougou
                    'tribus' => [
                        'Tribu Malinké d\'Odienné' => [
                            'Gbéléban',
                            'Kani',
                            'Seydougou'
                        ]
                    ]
                ]
            ]
        ];

        foreach ($hierarchy as $groupName => $cantons) {
            $group = GroupeEthnique::query()->updateOrCreate(
                ['nom' => $groupName]
            );

            foreach ($cantons as $cantonName => $cantonData) {
                // Find matching region
                $region = \App\Models\Region::query()
                    ->where('code_reg', $cantonData['region_code'])
                    ->first();

                $canton = Canton::query()->updateOrCreate(
                    [
                        'nom' => $cantonName,
                        'groupe_ethnique_id' => $group->id
                    ],
                    [
                        'region_id' => $region ? $region->id : null
                    ]
                );

                foreach ($cantonData['tribus'] as $tribuName => $villages) {
                    $tribu = Tribu::query()->updateOrCreate(
                        [
                            'nom' => $tribuName,
                            'canton_id' => $canton->id
                        ]
                    );

                    foreach ($villages as $villageName) {
                        Village::query()->updateOrCreate(
                            [
                                'nom' => $villageName,
                                'tribu_id' => $tribu->id
                            ]
                        );
                    }
                }
            }
        }
    }
}
