<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNotaConsultaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'presion_arterial'    => 'nullable|string|max:20',
            'frecuencia_cardiaca' => 'nullable|integer|min:20|max:300',
            'temperatura'         => 'nullable|string|max:10',
            'peso'                => 'nullable|string|max:10',
            'diagnostico'         => 'required|string',
            'tratamiento'         => 'required|string',
            'notas_adicionales'   => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'diagnostico.required' => 'El diagnóstico es requerido.',
            'tratamiento.required' => 'El tratamiento es requerido.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'msj'    => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422));
        }

        $error = $validator->errors()->first();
        throw new HttpResponseException(
            redirect()->back()->withInput()->with('error', $error ?: 'Datos de la nota no válidos.')
        );
    }
}
