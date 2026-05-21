<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Valide la création ou la mise à jour d'un rendez-vous.
- Objectif (Le "Pourquoi") : Garantir que les rendez-vous sont pris dans les créneaux autorisés (en semaine, horaires spécifiques) et ne sont pas en doublon.
- Connexions et Dépendances : Utilisé par `RendezVousController`.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\RendezVous;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RendezVousRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lieu' => [
                'required',
                'string',
                Rule::in([
                    "Consulat Général de Côte d'Ivoire à Paris",
                    "Ambassade de Côte d'Ivoire à Bruxelles",
                    "Ambassade de Côte d'Ivoire à Dakar",
                    "Ambassade de Côte d'Ivoire à Rabat",
                    "Ambassade de Côte d'Ivoire à Ottawa",
                    "Ambassade de Côte d'Ivoire à Washington",
                    "Ambassade de Côte d'Ivoire à Abidjan (SNEDAI)"
                ])
            ],
            'date' => [
                'required',
                'date',
                'after:today',
                // Règle personnalisée pour empêcher les rendez-vous le week-end.
                function ($attribute, $value, $fail) {
                    $dayOfWeek = date('N', strtotime($value));
                    if ($dayOfWeek >= 6) { // 6 = samedi, 7 = dimanche
                        $fail("Les rendez-vous ne sont pas autorisés le week-end.");  
                    }
                }
            ],
            'creneau' => [
                'required',
                'string',
                Rule::in(['09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30']),
                // Règle personnalisée pour vérifier si le créneau est déjà pris.
                function ($attribute, $value, $fail) {
                    $date = $this->input('date');
                    $lieu = $this->input('lieu');
                    if (!$date || !$lieu) {
                        return;
                    }

                    $tz = RendezVous::getTimezoneForLieu($lieu);
                    try {
                        $dateHeureLocal = $date . ' ' . $value . ':00';
                        $dateHeureUtc = \Illuminate\Support\Carbon::createFromFormat('Y-m-d H:i:s', $dateHeureLocal, $tz)->setTimezone('UTC');
                    } catch (\Exception $e) {
                        return;
                    }

                    $exists = RendezVous::where('date_heure', $dateHeureUtc)
                        ->where('lieu', $lieu)
                        ->where('statut', '!=', 'ANNULE')
                        ->exists();

                    if ($exists) {
                        $fail("Ce créneau horaire est déjà réservé pour ce consulat/ambassade.");
                    }
                }
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'lieu.required' => 'Le lieu de rendez-vous est obligatoire.',
            'lieu.in' => 'Le lieu de rendez-vous sélectionné n\'est pas valide.',
            'date.required' => 'La date de rendez-vous est obligatoire.',
            'date.after' => 'Le rendez-vous doit être programmé à une date future.',
            'creneau.required' => 'Le créneau horaire est obligatoire.',
            'creneau.in' => 'Le créneau doit être entre 09:00-12:00 ou 14:00-16:30 (intervalles de 30 min).',
        ];
    }
}
