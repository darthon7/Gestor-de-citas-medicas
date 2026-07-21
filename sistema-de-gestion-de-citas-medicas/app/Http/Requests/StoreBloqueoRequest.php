<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBloqueoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'fecha_bloqueo'       => 'required|date',
            'hora_inicio_bloqueo' => 'nullable|date_format:H:i:s',
            'hora_fin_bloqueo'    => 'nullable|date_format:H:i:s|after:hora_inicio_bloqueo',
            'motivo'              => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_bloqueo.required'    => 'La fecha del bloqueo es requerida.',
            'fecha_bloqueo.date'        => 'El formato de la fecha no es válido.',
            'hora_fin_bloqueo.after'    => 'La hora de fin debe ser posterior a la hora de inicio.',
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
