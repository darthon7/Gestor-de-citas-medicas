<?php

namespace App\Http\Repository;

use App\Models\Cita;
use App\Models\NotaConsulta;
use Exception;

class NotasConsultaRepository
{
    public function registrarNota(int $citaId, array $data, int $doctorUsuarioId)
    {
        try {
            $cita = Cita::find($citaId);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if (!in_array($cita->estado, ['agendada', 'confirmada', 'en_consulta', 'completada'])) {
                return ['mensaje' => 'Solo se pueden registrar notas en citas agendadas, confirmadas, en consulta o completadas.'];
            }

            // Si ya existe nota de consulta, la actualizamos
            $nota = NotaConsulta::where('cita_id', $citaId)->first();
            if ($nota) {
                $nota->update([
                    'presion_arterial'    => $data['presion_arterial'] ?? $nota->presion_arterial,
                    'frecuencia_cardiaca' => $data['frecuencia_cardiaca'] ?? $nota->frecuencia_cardiaca,
                    'temperatura'         => $data['temperatura'] ?? $nota->temperatura,
                    'peso'                => $data['peso'] ?? $nota->peso,
                    'diagnostico'         => $data['diagnostico'],
                    'tratamiento'         => $data['tratamiento'],
                    'notas_adicionales'   => $data['notas_adicionales'] ?? null,
                ]);
            } else {
                $nota = NotaConsulta::create([
                    'cita_id'             => $citaId,
                    'presion_arterial'    => $data['presion_arterial'] ?? null,
                    'frecuencia_cardiaca' => $data['frecuencia_cardiaca'] ?? null,
                    'temperatura'         => $data['temperatura'] ?? null,
                    'peso'                => $data['peso'] ?? null,
                    'diagnostico'         => $data['diagnostico'],
                    'tratamiento'         => $data['tratamiento'],
                    'notas_adicionales'   => $data['notas_adicionales'] ?? null,
                    'creado_por'          => $doctorUsuarioId,
                ]);
            }

            // Marcar cita como completada
            $cita->update(['estado' => 'completada']);

            return [
                'mensaje' => 'Nota de consulta registrada correctamente',
                'data'    => $nota->load('cita'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function obtenerNotas(int $citaId)
    {
        try {
            $nota = NotaConsulta::with('cita.perfilPaciente.usuario')
                ->where('cita_id', $citaId)
                ->first();

            if (!$nota) {
                return ['mensaje' => 'No hay notas para esta cita'];
            }

            return [
                'mensaje' => 'Nota de consulta obtenida correctamente',
                'data'    => $nota,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
