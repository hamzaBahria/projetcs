<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordChangeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'],
        ];
    }

    public function messages()
    {
        return [
            'current_password.required' => 'L\'ancien mot de passe est requis.',
            'new_password.required' => 'Le nouveau mot de passe est requis.',
            'new_password.min' => 'Le nouveau mot de passe doit faire au moins 8 caractères.',
            'new_password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'new_password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&).',
        ];
    }
}
