<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Centralise la création d'une nouvelle demande d'enrôlement, incluant la gestion des informations personnelles du citoyen, le stockage sécurisé des documents justificatifs et la traçabilité de l'action.
- Objectif (Le "Pourquoi") : Assurer que la création d'une demande est une opération fiable et sécurisée. Si une étape échoue (comme l'enregistrement en base de données ou le téléchargement d'un fichier), l'ensemble du processus est annulé pour éviter des données incomplètes ou orphelines.
- Connexions et Dépendances : Interagit avec les modèles `Citoyen`, `Demande`, `Document`, `User` et `AuditLog` via Eloquent. Utilise les transactions de base de données (DB), le système de stockage de fichiers (Storage) et journalise les actions de l'utilisateur.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Services;

use App\Models\Citoyen;
use App\Models\Demande;
use App\Models\Document;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class DemandeService
{
    /**
     * Crée une nouvelle demande d'enrôlement avec les justificatifs associés.
     *
     * @param array $data
     * @param array $files
     * @param User $user
     * @return Demande
     * @throws Exception
     */
    public function createDemande(array $data, array $files, User $user): Demande
    {
        // On commence une "transaction" : imagine cela comme un brouillon géant.
        // Si tout se passe bien, on valide. Si une étape échoue, on annule tout pour ne rien laisser de bancal.
        DB::beginTransaction();

        try {
            // Création de l'identité du citoyen dans notre registre
            $citoyen = Citoyen::create([
                'nni' => $data['nni'] ?? null,
                'nom' => mb_strtoupper($data['nom'], 'UTF-8'),
                'prenoms' => $data['prenoms'],
                'date_naissance' => $data['date_naissance'],
                'lieu_naissance' => $data['lieu_naissance'],
                'genre' => $data['genre'],
                'pays_residence' => $data['pays_residence'],
                'adresse_residence' => $data['adresse_residence'],
                'telephone' => $data['telephone'],
            ]);

            // Lien entre l'utilisateur qui fait la demande et le citoyen concerné
            $demande = Demande::create([
                'user_id' => $user->id,
                'citoyen_id' => $citoyen->id,
                'type_demande' => $data['type_demande'],
                'sous_type' => $data['sous_type'] ?? null,
                'statut' => 'SOUMIS',
            ]);

            $uploadedPaths = [];

            // Gestion des fichiers joints : on les sauvegarde un par un dans un coffre-fort numérique
            foreach ($files as $key => $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    // On place le fichier dans un dossier privé, non accessible directement depuis Internet
                    $path = $file->store('private/documents');

                    if ($path === false) {
                        throw new Exception("Échec du téléversement du fichier : {$key}");
                    }

                    $uploadedPaths[] = $path;

                    // On enregistre une référence vers ce fichier dans notre base de données
                    Document::create([
                        'demande_id' => $demande->id,
                        'type_document' => $key,
                        'chemin_fichier' => $path,
                        'statut_validation' => 'PENDING',
                    ]);
                }
            }

            // On garde une trace de cette action dans notre journal d'audit (pour savoir qui a fait quoi)
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'demande_creation',
                'description' => "Création de la demande {$demande->type_demande} pour le citoyen {$citoyen->nom} {$citoyen->prenoms}. ID Dossier: {$demande->id}",
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => substr(request()->userAgent() ?? 'N/A', 0, 255),
            ]);

            // Tout est en ordre, on valide officiellement les changements dans la base de données
            DB::commit();

            return $demande;

        } catch (Exception $e) {
            // Une erreur est survenue, on annule tout ce qui a été fait dans le "brouillon" (transaction)
            DB::rollBack();
            
            // On nettoie les fichiers qui auraient pu être sauvés pour ne pas encombrer le serveur inutilement
            foreach ($uploadedPaths as $uploadedPath) {
                if (Storage::exists($uploadedPath)) {
                    Storage::delete($uploadedPath);
                }
            }

            throw $e;
        }
    }
}

/*
📖 Glossaire des notions clés
- Transaction (DB) : Un ensemble d'opérations regroupées qui doivent toutes réussir pour être acceptées ; sinon, tout est annulé comme si rien ne s'était passé.
- Audit Log : Un journal de bord qui enregistre toutes les actions importantes effectuées sur le système, utile pour la sécurité et le suivi.
- Eager Loading : (Note : bien que non utilisé ici, c'est une technique Laravel courante) Le fait de charger les données liées en même temps que les données principales pour éviter de faire trop de requêtes à la base.
- Instance : Une version concrète d'un objet définie par une classe.
*/
