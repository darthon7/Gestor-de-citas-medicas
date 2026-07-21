<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreHorarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'dia_semana'               => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora_inicio'              => 'required|date_format:H:i:s',
            'hora_fin'                 => 'required|date_format:H:i:s|after:hora_inicio',
            'duracion_consulta_minutos' => 'nullable|integer|min:10|max:120',
        ];
    }

    public function messages(): array
    {
        return [
            'dia_semana.required' => 'El día de la semana es requerido.',
            'dia_semana.in'       => 'El día de la semana no es válido.',
            'hora_inicio.required' => 'La hora de inicio es requerida.',
            'hora_fin.required'   => 'La hora de fin es requerida.',
            'hora_fin.after'      => 'La hora de fin debe ser posterior a la hora de inicio.',
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
