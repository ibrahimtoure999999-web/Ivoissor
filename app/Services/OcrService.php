<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OcrService
{
    /**
     * Analyse un fichier (image ou PDF) pour extraire des données d'identité.
     * Mode simulation intelligente basé sur le nom du fichier ou des mots-clés.
     */
    public function analyze(UploadedFile $file): array
    {
        // Utilisation de Str::ascii et strtolower pour gérer proprement les caractères accentués
        $filename = strtolower(Str::ascii($file->getClientOriginalName()));
        
        // Simulation intelligente
        $data = $this->getSimulatedData($filename);

        // Sécurisation contre les injections XSS stockées dans le log d'audit
        $safeName = strip_tags($file->getClientOriginalName());

        // Enregistrement dans l'audit log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'OCR_SCAN',
            'description' => "Analyse OCR effectuée sur le fichier : {$safeName}. Résultat : " . ($data['found'] ? 'Succès' : 'Partiel'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $data;
    }

    /**
     * Retourne des données simulées selon le type de document détecté.
     */
    protected function getSimulatedData(string $filename): array
    {
        // Cas 1 : CNI Kouadio (Démo)
        if (Str::contains($filename, 'kouadio') || Str::contains($filename, 'cni_test')) {
            return [
                'found' => true,
                'nom' => 'KOUADIO',
                'prenoms' => 'Marc Koffi',
                'date_naissance' => '1990-05-15',
                'lieu_naissance' => 'Abidjan',
                'genre' => 'M',
                'nni' => '123456789012',
                'message' => 'Carte Nationale d\'Identité détectée (Simulé)'
            ];
        }

        // Cas 2 : Passeport Traoré (Démo)
        if (Str::contains($filename, 'traore') || Str::contains($filename, 'passeport_test')) {
            return [
                'found' => true,
                'nom' => 'TRAORÉ',
                'prenoms' => 'Mariam',
                'date_naissance' => '1995-11-20',
                'lieu_naissance' => 'Bouaké',
                'genre' => 'F',
                'nni' => '987654321098',
                'message' => 'Passeport détecté (Simulé)'
            ];
        }

        // Cas 3 : Générique CNI
        if (Str::contains($filename, 'cni')) {
            return [
                'found' => true,
                'nom' => 'KONAN',
                'prenoms' => 'Jean-Baptiste',
                'date_naissance' => '1988-03-12',
                'lieu_naissance' => 'Yamoussoukro',
                'genre' => 'M',
                'nni' => '556677889900',
                'message' => 'Pièce d\'identité générique détectée (Simulé)'
            ];
        }

        // Cas 4 : Inconnu
        return [
            'found' => false,
            'message' => 'Analyse terminée. Certaines données n\'ont pas pu être extraites automatiquement.',
            'nom' => '',
            'prenoms' => '',
            'date_naissance' => '',
            'lieu_naissance' => '',
            'genre' => 'M',
            'nni' => ''
        ];
    }
}
