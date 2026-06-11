<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\NiveauEtude;
use App\Enums\Sexe;
use App\Enums\SituationMatrimoniale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Classe StoreRessortissantRequest
 * Gère la sécurité, l'autorisation et la validation stricte lors de la création d'un ressortissant.
 */
class StoreRessortissantRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur connecté est autorisé à soumettre ce formulaire.
     */
    public function authorize(): bool
    {
        // Seuls les agents connectés et authentifiés ont le droit d'enregistrer des ressortissants
        return auth()->check();
    }

    /**
     * Définit les règles de validation de chaque champ du formulaire.
     * Les clés étrangères (commune_id, village_id) sont nullables conformément au plan de flexibilité.
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'sexe' => ['required', Rule::enum(Sexe::class)],
            'date_naissance' => ['nullable', 'date', 'before_or_equal:today'],
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'situation_matrimoniale' => ['nullable', Rule::enum(SituationMatrimoniale::class)],
            'niveau_etude' => ['nullable', Rule::enum(NiveauEtude::class)],
            'famille' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'pays_residence' => ['nullable', 'string', 'max:255'],
            'ville_residence' => ['nullable', 'string', 'max:255'],
            'quartier_residence' => ['nullable', 'string', 'max:255'],
            'adresse_complete' => ['nullable', 'string'],
            'village_residence_id' => ['nullable', 'integer', 'exists:villages,id'],
            'commune_id' => ['nullable', 'integer', 'exists:communes,id'],
            'village_id' => ['nullable', 'integer', 'exists:villages,id'],
        ];
    }

    /**
     * Personnalise les messages d'erreur renvoyés en français à l'agent de saisie.
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de famille est obligatoire.',
            'prenoms.required' => 'Le ou les prénoms sont obligatoires.',
            'sexe.required' => 'Le sexe est obligatoire.',
            'sexe.enum' => 'Le sexe sélectionné est invalide.',
            'date_naissance.date' => 'La date de naissance doit être une date au format valide.',
            'date_naissance.before_or_equal' => 'La date de naissance ne peut pas être une date future.',
            'email.email' => 'L\'adresse email doit être une adresse électronique valide.',
            'commune_id.exists' => 'La commune administrative sélectionnée est introuvable.',
            'village_id.exists' => 'Le village coutumier sélectionné est introuvable.',
        ];
    }
}
