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
            'dia_semana'                => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora_inicio'               => 'required|date_format:H:i:s',
            'hora_fin'                  => 'required|date_format:H:i:s|after:hora_inicio',
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

    protected function prepareForValidation()
    {
        foreach (['hora_inicio', 'hora_fin'] as $campo) {
            if ($this->has($campo) && preg_match('/^\d{2}:\d{2}$/', (string) $this->input($campo))) {
                $this->merge([$campo => $this->input($campo) . ':00']);
            }
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->first();
        throw new HttpResponseException(
            redirect()->back()->withInput()->with('error', $error ?: 'Datos de horario no válidos.')
        );
    }
}
