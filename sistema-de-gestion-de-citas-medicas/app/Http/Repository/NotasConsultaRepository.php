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

            if ($cita->estado !== 'en_consulta' && $cita->estado !== 'completada') {
                return ['mensaje' => 'Solo se pueden registrar notas en citas en consulta o completadas.'];
            }

            // Verificar que no exista ya una nota
            if ($cita->notaConsulta) {
                return ['mensaje' => 'Esta cita ya tiene una nota de consulta registrada.'];
            }

            $nota = NotaConsulta::create([
                'cita_id'           => $citaId,
                'diagnostico'       => $data['diagnostico'],
                'tratamiento'       => $data['tratamiento'],
                'notas_adicionales' => $data['notas_adicionales'] ?? null,
                'creado_por'        => $doctorUsuarioId,
            ]);

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
