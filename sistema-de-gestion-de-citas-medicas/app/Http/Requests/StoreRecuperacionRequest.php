<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRecuperacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:usuarios,email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es requerido.',
            'email.email'    => 'Ingresa un correo electrónico válido.',
            'email.exists'   => 'El correo electrónico no se encuentra registrado en el sistema.',
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
