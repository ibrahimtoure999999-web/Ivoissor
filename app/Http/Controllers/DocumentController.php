<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Gère le téléchargement sécurisé des pièces justificatives privées.
- Objectif (Le "Pourquoi") : Empêcher le vol de données ou l'accès non autorisé aux documents d'autres citoyens (IDOR), tout en permettant aux agents/admins et au propriétaire du dossier de les télécharger.
- Connexions et Dépendances : Modèle `Document`, système de stockage `Storage`, journal d'audit `AuditLog`.
*/

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Télécharge un document de manière sécurisée après validation des droits d'accès.
     */
    public function download(Document $document, Request $request): StreamedResponse
    {
        $user = auth()->user();
        $isOwner = $user && $user->id === $document->demande->user_id;
        $isStaff = $user instanceof \App\Models\User && ($user->hasRole('AGENT') || $user->hasRole('ADMIN'));

        abort_unless($isOwner || $isStaff, 403, "Vous n'êtes pas autorisé à télécharger ce document.");

        if (!Storage::exists($document->chemin_fichier)) {
            abort(404, "Le fichier demandé n'existe pas sur le serveur.");
        }

        // Journalisation de l'action pour des raisons de sécurité
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'document_telechargement',
            'description' => "Téléchargement sécurisé du document {$document->type_document} (ID: {$document->id}) pour la demande ID: {$document->demande_id}.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => substr($request->userAgent() ?? 'N/A', 0, 255),
        ]);

        return Storage::download($document->chemin_fichier);
    }
}
