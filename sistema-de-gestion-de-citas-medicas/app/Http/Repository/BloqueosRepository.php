<?php

namespace App\Http\Repository;

use App\Models\BloqueoHorario;
use App\Models\Cita;
use Exception;

class BloqueosRepository
{
    public function obtenerBloqueos(int $doctorId)
    {
        try {
            $bloqueos = BloqueoHorario::where('perfil_doctor_id', $doctorId)
                ->orderBy('fecha_bloqueo', 'desc')
                ->get();

            return [
                'mensaje' => 'Bloqueos obtenidos correctamente',
                'data'    => $bloqueos,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarBloqueo(int $doctorId, array $data, int $usuarioId)
    {
        try {
            // Verificar si hay citas en ese rango
            $citasAfectadas = Cita::where('perfil_doctor_id', $doctorId)
                ->where('fecha_cita', $data['fecha_bloqueo'])
                ->whereIn('estado', ['agendada', 'confirmada'])
                ->when(!empty($data['hora_inicio_bloqueo']) && !empty($data['hora_fin_bloqueo']), function ($q) use ($data) {
                    $q->whereBetween('hora_cita', [$data['hora_inicio_bloqueo'], $data['hora_fin_bloqueo']]);
                })
                ->count();

            $alerta = $citasAfectadas > 0
                ? "ALERTA: Hay $citasAfectadas cita(s) agendada(s) en este horario que serán afectadas."
                : null;

            $bloqueo = BloqueoHorario::create([
                'perfil_doctor_id'  => $doctorId,
                'fecha_bloqueo'     => $data['fecha_bloqueo'],
                'hora_inicio_bloqueo' => $data['hora_inicio_bloqueo'] ?? null,
                'hora_fin_bloqueo'  => $data['hora_fin_bloqueo'] ?? null,
                'motivo'            => $data['motivo'] ?? null,
                'creado_por'        => $usuarioId,
            ]);

            return [
                'mensaje' => 'Bloqueo registrado correctamente' . ($alerta ? '. ' . $alerta : ''),
                'data'    => $bloqueo,
                'alerta'  => $alerta,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function eliminarBloqueo(int $id)
    {
        try {
            $bloqueo = BloqueoHorario::find($id);
            if (!$bloqueo) {
                return ['mensaje' => 'Bloqueo no encontrado'];
            }
            $bloqueo->delete();
            return ['mensaje' => 'Bloqueo eliminado correctamente'];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
