<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Valide les identifiants de connexion.
- Objectif (Le "Pourquoi") : Garantir que l'e-mail est au bon format et que le mot de passe est présent avant de tenter l'authentification.
- Connexions et Dépendances : Utilisé par `LoginController`.

💻 Code Commenté
*/


declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
