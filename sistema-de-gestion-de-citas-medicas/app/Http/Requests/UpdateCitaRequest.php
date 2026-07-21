<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCitaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'fecha_cita' => 'required|date|after_or_equal:today',
            'hora_cita'  => 'required|date_format:H:i:s',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_cita.required'       => 'La nueva fecha de la cita es requerida.',
            'fecha_cita.after_or_equal' => 'La nueva fecha no puede ser en el pasado.',
            'hora_cita.required'        => 'La nueva hora de la cita es requerida.',
            'hora_cita.date_format'     => 'El formato de la hora debe ser HH:MM:SS.',
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
