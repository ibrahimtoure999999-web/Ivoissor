<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\District;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // le Chemin d'accès vers notre fichier de données JSON
       $path = database_path('data/districts.json');

       // on Vérifie que le fichier existe bien avant de tenter de le lire
        if (!File::exists($path)) {
            return;
        }

        // Lecture du contenu du fichier JSON
        $json = File::get($path);

        // Transformer un texte au format JSON en une liste PHP
        $districts = json_decode($json, true);

        // On boucle sur chaque district pour l'ajouter ou le mettre à jour
        foreach ($districts as $district) {
            District::query()->updateOrCreate(
                // Si ce code existe déjà en base, on modifie la ligne. Sinon, on en crée une nouvelle.
                ['code_district' => $district['code_district']],

                // Voici les données à enregistrer
                [
                    'nom_district' => $district['nom_district'],
                    'annee'        => $district['annee'] ?? null,
                ]
            );
        }
    }
}
