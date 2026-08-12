<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRegistroMedicoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'              => 'required|string|max:255',
            'email'               => 'required|email|unique:usuarios,email',
            'password'            => 'required|string|min:8|confirmed',
            'curp'                => ['required', 'string', 'size:18', 'unique:usuarios,curp', 'regex:/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/'],
            'telefono'            => 'nullable|string|regex:/^\d{10}$/',
            'cedula_profesional'  => 'required|string|unique:perfiles_doctor,cedula_profesional|regex:/^\d{7,8}$/',
            'cedula_especialidad' => 'nullable|string|regex:/^\d{7,8}$/',
            'especialidades'      => 'nullable|array',
            'especialidades.*'    => 'exists:especialidades,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'             => 'El nombre completo es requerido.',
            'email.required'              => 'El correo electrónico es requerido.',
            'email.unique'                => 'Este correo electrónico ya está registrado.',
            'password.required'           => 'La contraseña es requerida.',
            'password.min'                => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'          => 'La confirmación de contraseña no coincide.',
            'curp.required'               => 'La CURP es requerida.',
            'curp.size'                   => 'La CURP debe tener exactamente 18 caracteres.',
            'curp.unique'                 => 'Esta CURP ya está registrada.',
            'curp.regex'                  => 'El formato de la CURP no es válido.',
            'cedula_profesional.required' => 'La cédula profesional es requerida.',
            'cedula_profesional.unique'   => 'Esta cédula profesional ya está registrada.',
            'cedula_profesional.regex'    => 'La cédula profesional debe contener de 7 a 8 dígitos numéricos.',
            'cedula_especialidad.regex'   => 'La cédula de especialidad debe contener de 7 a 8 dígitos numéricos.',
            'telefono.regex'              => 'El teléfono debe contener exactamente 10 dígitos numéricos.',
            'especialidades.*.exists'     => 'Una o más especialidades seleccionadas no son válidas.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Si la petición espera JSON (app móvil / API), devolver JSON
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'msj'    => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422));
        }

        // Para web SSR: redirigir de vuelta con errores en sesión
        throw new HttpResponseException(
            redirect()->back()->withErrors($validator)->withInput()
        );
    }
}
