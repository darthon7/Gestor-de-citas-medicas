<?php

namespace App\Http\Repository;

use App\Models\Especialidad;
use Exception;

class EspecialidadesRepository
{
    public function obtenerEspecialidades()
    {
        try {
            $especialidades = Especialidad::where('activa', true)->orderBy('nombre')->get();
            return [
                'mensaje' => 'Especialidades obtenidas correctamente',
                'data'    => $especialidades,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarEspecialidad(array $data)
    {
        try {
            $especialidad = Especialidad::create([
                'nombre'      => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'activa'      => true,
            ]);
            return [
                'mensaje' => 'Especialidad registrada correctamente',
                'data'    => $especialidad,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function obtenerEspecialidad(int $id)
    {
        try {
            $especialidad = Especialidad::find($id);
            if (!$especialidad) {
                return ['mensaje' => 'Especialidad no encontrada'];
            }
            return [
                'mensaje' => 'Especialidad obtenida correctamente',
                'data'    => $especialidad,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
