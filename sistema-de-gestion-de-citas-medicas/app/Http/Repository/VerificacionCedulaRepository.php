<?php

namespace App\Http\Repository;

use App\Models\VerificacionCedula;
use Exception;

class VerificacionCedulaRepository
{
    public function verificarCedula(string $numeroCedula, string $nombreTitular = null)
    {
        try {
            $cedula = VerificacionCedula::where('numero_cedula', $numeroCedula)->first();

            if (!$cedula) {
                return [
                    'mensaje'   => 'Cédula no encontrada en el registro de profesionistas.',
                    'es_valida' => false,
                ];
            }

            if (!$cedula->es_valida) {
                return [
                    'mensaje'   => 'La cédula está registrada como inválida o revocada.',
                    'es_valida' => false,
                ];
            }

            return [
                'mensaje'        => 'Cédula verificada correctamente',
                'es_valida'      => true,
                'nombre_titular' => $cedula->nombre_titular,
                'profesion'      => $cedula->profesion,
                'institucion'    => $cedula->institucion,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage(), 'es_valida' => false];
        }
    }
}
