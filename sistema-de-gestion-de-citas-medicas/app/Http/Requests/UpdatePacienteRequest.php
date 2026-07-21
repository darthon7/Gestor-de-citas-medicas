<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePacienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'                        => 'sometimes|string|max:255',
            'telefono'                      => 'sometimes|nullable|string|max:20',
            'direccion'                     => 'sometimes|nullable|string',
            'contacto_emergencia_nombre'    => 'sometimes|nullable|string|max:255',
            'contacto_emergencia_telefono'  => 'sometimes|nullable|string|max:20',
            'fecha_nacimiento'              => 'sometimes|nullable|date',
            'sexo'                          => 'sometimes|nullable|in:M,F',
            'nss'                           => 'sometimes|nullable|string|max:20',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'msj'    => 'Error de validación',
            'errors' => $validator->errors(),
        ], 422));
    }
}
