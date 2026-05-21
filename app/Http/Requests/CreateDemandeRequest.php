<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Valide les données soumises lors de la création d'une demande.
- Objectif (Le "Pourquoi") : Assurer que toutes les informations nécessaires (identité, type de demande, fichiers justificatifs) sont présentes, au bon format et respectent les règles métier avant que le système ne commence à traiter la demande.
- Connexions et Dépendances : Utilisé par `DemandeController`.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDemandeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Seuls les utilisateurs connectés peuvent envoyer une demande.
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Règles communes à toutes les demandes.
        $rules = [
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:255',
            'genre' => 'required|in:M,F',
            'pays_residence' => 'required|string|max:255',
            'adresse_residence' => 'required|string|max:255',
            'telephone' => 'required|string|max:50',
            'type_demande' => 'required|in:PASSEPORT,ETAT_CIVIL,CARTE_CONSULAIRE',     
        ];

        $type = $this->input('type_demande');

        // Selon le type de demande, on ajoute des règles spécifiques (fichiers obligatoires).
        if ($type === 'PASSEPORT') {
            $rules['nni'] = 'required|string|max:50';
            $rules['extrait_naissance'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['certificat_nationalite'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['justificatif_domicile'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['photo'] = 'required|file|mimes:jpg,jpeg,png|max:5120';
        } elseif ($type === 'ETAT_CIVIL') {
            $rules['nni'] = 'nullable|string|max:50';
            $rules['acte_etranger'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'; 
            $rules['piece_identite_parents'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['demande_ecrite'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
        } elseif ($type === 'CARTE_CONSULAIRE') {
            $rules['nni'] = 'nullable|string|max:50';
            $rules['mode_identification'] = 'required|in:cni_passport,extrait_nationalite';
            $rules['cni_ou_passeport'] = 'required_if:mode_identification,cni_passport|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['extrait_naissance'] = 'required_if:mode_identification,extrait_nationalite|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['certificat_nationalite'] = 'required_if:mode_identification,extrait_nationalite|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['justificatif_domicile'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['photo'] = 'required|file|mimes:jpg,jpeg,png|max:5120';
            $rules['recu_paiement'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'; 
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        // Messages d'erreur explicites pour aider l'utilisateur à corriger son formulaire.
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenoms.required' => 'Le prénom est obligatoire.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',      
            'date_naissance.before' => 'La date de naissance doit être dans le passé.',
            'lieu_naissance.required' => 'Le lieu de naissance est obligatoire.',      
            'genre.required' => 'Le genre est obligatoire.',
            'genre.in' => 'Le genre doit être M ou F.',
            'pays_residence.required' => 'Le pays de résidence est obligatoire.',     
            'adresse_residence.required' => 'L\'adresse de résidence est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',      
            'type_demande.required' => 'Le type de demande est obligatoire.',
            'type_demande.in' => 'Le type de demande sélectionné est invalide.',     
            'nni.required_if' => 'Le NNI est obligatoire pour une demande de passeport.',

            // Messages pour les documents
            'extrait_naissance.required' => 'L\'extrait de naissance est requis.',     
            'certificat_nationalite.required' => 'Le certificat de nationalité est requis.',
            'justificatif_domicile.required' => 'Le justificatif de domicile est requis.',
            'photo.required' => 'La photo d\'identité est requise.',
            'acte_etranger.required' => 'La copie intégrale de l\'acte étranger est requise.',
            'piece_identite_parents.required' => 'La pièce d\'identité des parents est requise.',
            'demande_ecrite.required' => 'La demande écrite signée est requise.',    
            'mode_identification.required' => 'Le choix du mode d\'identification ivoirienne est obligatoire.',
            'recu_paiement.required' => 'Le reçu de paiement des droits de chancellerie (10 €) est requis.',

            // Messages pour les formats de fichiers
            '*.file' => 'Le fichier doit être valide.',
            '*.mimes' => 'Format de fichier accepté : PDF, JPG, JPEG, PNG.',
            '*.max' => 'La taille maximale autorisée est de 5 Mo.',
        ];
    }
}
