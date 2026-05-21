<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Valide les données lors de l'inscription d'un citoyen.
- Objectif (Le "Pourquoi") : S'assurer que l'e-mail est unique et que le mot de passe respecte des règles de sécurité robustes (complexité, non compromis).
- Connexions et Dépendances : Utilisé par `RegisterController`.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterCitoyenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed', // Vérifie que le champ 'password_confirmation' correspond.
                // Règle de sécurité robuste : doit contenir des lettres, chiffres, symboles et être assez long.
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(), // Vérifie que le mot de passe n'a pas été vu dans des fuites de données connues.
            ],
        ];
    }
}
