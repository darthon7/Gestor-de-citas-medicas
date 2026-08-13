<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'password.required' => 'La contraseña es requerida.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Para web SSR: redirigir de vuelta con errores en sesión; API/app móvil: JSON 422
        // (se usa is('api/*') porque el cliente Volley de Android no envía Accept: application/json)
        if (! $this->is('api/*') && ! $this->expectsJson()) {
            throw new HttpResponseException(
                redirect()->back()->withErrors($validator)->withInput()
            );
        }

        throw new HttpResponseException(response()->json([
            'mensaje' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}
