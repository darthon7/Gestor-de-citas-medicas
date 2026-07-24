<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRestablecerPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => 'required|email|exists:usuarios,email',
            'codigo'   => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'     => 'El correo electrónico es requerido.',
            'email.email'        => 'Ingresa un correo electrónico válido.',
            'email.exists'       => 'El correo electrónico no se encuentra registrado.',
            'codigo.required'    => 'El código de verificación es requerido.',
            'codigo.size'        => 'El código de verificación debe ser de 6 dígitos.',
            'password.required'  => 'La nueva contraseña es requerida.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'mensaje' => $validator->errors()->first(),
            'errors'  => $validator->errors()
        ], 422));
    }
}
