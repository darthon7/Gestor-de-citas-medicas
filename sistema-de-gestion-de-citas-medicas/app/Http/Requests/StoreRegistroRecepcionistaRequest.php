<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRegistroRecepcionistaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'          => 'required|string|max:255',
            'email'           => 'required|email|unique:usuarios,email',
            'password'        => 'required|string|min:8|confirmed',
            'curp'            => 'nullable|string|size:18',
            'telefono'        => 'nullable|string|max:20',
            'numero_empleado' => 'nullable|string|max:50',
            'unidad_asignada' => 'nullable|string|max:255',
            'turno'           => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre completo es requerido.',
            'email.required'    => 'El correo electrónico es requerido.',
            'email.unique'      => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es requerida.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
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
