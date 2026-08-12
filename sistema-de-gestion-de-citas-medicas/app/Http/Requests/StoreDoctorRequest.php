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
            'curp'                => 'nullable|string|size:18|regex:/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/',
            'telefono'            => 'nullable|string|regex:/^\d{10}$/',
            'cedula_profesional'  => 'required|string|unique:perfiles_doctor,cedula_profesional|regex:/^\d{7,8}$/',
            'cedula_especialidad' => 'nullable|string|regex:/^\d{7,8}$/',
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
            'cedula_profesional.regex'    => 'La cédula profesional debe contener de 7 a 8 dígitos numéricos.',
            'cedula_especialidad.regex'   => 'La cédula de especialidad debe contener de 7 a 8 dígitos numéricos.',
            'telefono.regex'              => 'El teléfono debe contener exactamente 10 dígitos numéricos.',
            'curp.regex'                  => 'El formato de la CURP no es válido.',
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
