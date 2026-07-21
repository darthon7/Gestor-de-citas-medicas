<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePacienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'                        => 'required|string|max:255',
            'email'                         => 'required|email|unique:usuarios,email',
            'password'                      => 'required|string|min:8',
            'curp'                          => ['required', 'string', 'size:18', 'unique:usuarios,curp', 'regex:/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/'],
            'telefono'                      => 'nullable|string|max:20',
            'fecha_nacimiento'              => 'nullable|date',
            'sexo'                          => 'nullable|in:M,F',
            'direccion'                     => 'nullable|string',
            'contacto_emergencia_nombre'    => 'nullable|string|max:255',
            'contacto_emergencia_telefono'  => 'nullable|string|max:20',
            'nss'                           => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre es requerido.',
            'email.required'    => 'El correo es requerido.',
            'email.unique'      => 'Este correo ya está registrado.',
            'curp.required'     => 'La CURP es requerida.',
            'curp.unique'       => 'Esta CURP ya está registrada.',
            'curp.regex'        => 'El formato de CURP no es válido.',
            'password.required' => 'La contraseña es requerida.',
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
