<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ressortissant;
use Illuminate\Support\Facades\DB;

/**
 * Classe RessortissantService
 * Centralise l'ensemble des règles métiers et des écritures liées aux ressortissants.
 */
class RessortissantService
{
    /**
     * Enregistre un nouveau ressortissant avec isolation transactionnelle et matricule séquentiel unique.
     *
     * @param  array<string, mixed>  $data  Les données nettoyées provenant de la Form Request
     */
    public function createRessortissant(array $data): Ressortissant
    {
        // Utilisation d'une transaction SQL pour blinder l'atomicité de l'opération
        return DB::transaction(function () use ($data) {
            // Génération dynamique et sécurisée du matricule unique pour le citoyen
            $data['matricule'] = $this->generateUniqueMatricule();

            // Insertion en base de données et retour du modèle hydraté
            return Ressortissant::create($data);
        });
    }

    /**
     * Calcule le prochain matricule séquentiel disponible pour l'année courante (ex: RES-2026-0001).
     */
    private function generateUniqueMatricule(): string
    {
        $currentYear = date('Y');

        // Recherche du dernier ressortissant enregistré possédant un matricule pour l'année actuelle
        $lastRessortissant = Ressortissant::query()
            ->where('matricule', 'like', "RES-{$currentYear}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRessortissant) {
            // Extraction du numéro séquentiel de fin (ex: de "RES-2026-0012" on extrait "0012" -> 12)
            $parts = explode('-', $lastRessortissant->matricule);
            $sequence = (int) end($parts) + 1;
        } else {
            // S'il s'agit du tout premier ressortissant de l'année
            $sequence = 1;
        }

        // Formatage de la séquence sur 4 chiffres avec complétion de zéros à gauche (ex: 1 devient "0001")
        $paddedSequence = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return "RES-{$currentYear}-{$paddedSequence}";
    }
}
