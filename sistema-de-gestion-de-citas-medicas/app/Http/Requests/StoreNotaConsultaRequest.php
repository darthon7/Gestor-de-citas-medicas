<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNotaConsultaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'diagnostico'       => 'required|string',
            'tratamiento'       => 'required|string',
            'notas_adicionales' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'diagnostico.required' => 'El diagnóstico es requerido.',
            'tratamiento.required' => 'El tratamiento es requerido.',
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
