<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'perfil_paciente_id' => 'nullable|exists:perfiles_paciente,id',
            'perfil_doctor_id'   => 'required|exists:perfiles_doctor,id',
            'especialidad_id'    => 'required|exists:especialidades,id',
            'fecha_cita'         => 'required|date|after_or_equal:today',
            'hora_cita'          => 'required|date_format:H:i:s',
            'duracion_minutos'   => 'nullable|integer|min:10|max:120',
            'motivo_consulta'    => [
                'nullable', 'string', 'max:200',
                Rule::requiredIf(!$this->is('api/*')),
                'not_in:__otro__',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'perfil_paciente_id.required' => 'El paciente es requerido.',
            'perfil_paciente_id.exists'   => 'El paciente seleccionado no existe.',
            'perfil_doctor_id.required'   => 'El doctor es requerido.',
            'perfil_doctor_id.exists'     => 'El doctor seleccionado no existe.',
            'especialidad_id.required'    => 'La especialidad es requerida.',
            'especialidad_id.exists'      => 'La especialidad seleccionada no existe.',
            'fecha_cita.required'         => 'La fecha de la cita es requerida.',
            'fecha_cita.after_or_equal'   => 'La fecha de la cita no puede ser en el pasado.',
            'hora_cita.required'          => 'La hora de la cita es requerida.',
            'hora_cita.date_format'       => 'El formato de la hora debe ser HH:MM:SS.',
            'motivo_consulta.required'    => 'El motivo de la consulta es requerido.',
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
