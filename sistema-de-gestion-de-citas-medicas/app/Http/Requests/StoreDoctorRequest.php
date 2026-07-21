<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'              => 'required|string|max:255',
            'email'               => 'required|email|unique:usuarios,email',
            'password'            => 'nullable|string|min:8',
            'curp'                => 'nullable|string|size:18',
            'telefono'            => 'nullable|string|max:20',
            'cedula_profesional'  => 'required|string|unique:perfiles_doctor,cedula_profesional',
            'cedula_especialidad' => 'nullable|string',
            'especialidades'      => 'nullable|array',
            'especialidades.*'    => 'exists:especialidades,id',
            'estado_validacion'   => 'nullable|in:pendiente,validado,rechazado',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'             => 'El nombre es requerido.',
            'email.required'              => 'El correo electrónico es requerido.',
            'email.unique'                => 'Este correo ya está registrado.',
            'cedula_profesional.required' => 'La cédula profesional es requerida.',
            'cedula_profesional.unique'   => 'Esta cédula profesional ya está registrada.',
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
