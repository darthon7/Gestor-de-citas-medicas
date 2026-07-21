<?php

namespace App\Http\Repository;

use App\Models\Cita;
use App\Models\HorarioDoctor;
use Exception;

class HorariosRepository
{
    public function obtenerHorarios(int $doctorId)
    {
        try {
            $horarios = HorarioDoctor::where('perfil_doctor_id', $doctorId)
                ->orderByRaw("FIELD(dia_semana, 'lunes','martes','miercoles','jueves','viernes','sabado','domingo')")
                ->get();

            return [
                'mensaje' => 'Horarios obtenidos correctamente',
                'data'    => $horarios,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarHorario(int $doctorId, array $data)
    {
        try {
            // Verificar solapamiento
            $solapamiento = $this->verificarSolapamiento($doctorId, $data['dia_semana'], $data['hora_inicio'], $data['hora_fin']);
            if ($solapamiento) {
                return ['mensaje' => 'Ya existe un horario solapado para este doctor en ese día y horario.'];
            }

            $horario = HorarioDoctor::create([
                'perfil_doctor_id'         => $doctorId,
                'dia_semana'               => $data['dia_semana'],
                'hora_inicio'              => $data['hora_inicio'],
                'hora_fin'                 => $data['hora_fin'],
                'duracion_consulta_minutos' => $data['duracion_consulta_minutos'] ?? 30,
                'activo'                   => true,
            ]);

            return [
                'mensaje' => 'Horario registrado correctamente',
                'data'    => $horario,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function actualizarHorario(int $id, array $data)
    {
        try {
            $horario = HorarioDoctor::find($id);
            if (!$horario) {
                return ['mensaje' => 'Horario no encontrado'];
            }

            $diaFinal   = $data['dia_semana']  ?? $horario->dia_semana;
            $inicioFinal = $data['hora_inicio'] ?? $horario->hora_inicio;
            $finFinal    = $data['hora_fin']    ?? $horario->hora_fin;

            // Verificar solapamiento excluyendo el horario actual
            $solapamiento = HorarioDoctor::where('perfil_doctor_id', $horario->perfil_doctor_id)
                ->where('dia_semana', $diaFinal)
                ->where('id', '!=', $id)
                ->where('activo', true)
                ->where(function ($q) use ($inicioFinal, $finFinal) {
                    $q->whereBetween('hora_inicio', [$inicioFinal, $finFinal])
                        ->orWhereBetween('hora_fin', [$inicioFinal, $finFinal])
                        ->orWhere(function ($q2) use ($inicioFinal, $finFinal) {
                            $q2->where('hora_inicio', '<=', $inicioFinal)
                                ->where('hora_fin', '>=', $finFinal);
                        });
                })->exists();

            if ($solapamiento) {
                return ['mensaje' => 'El horario actualizado se solaparía con otro existente.'];
            }

            $horario->update([
                'dia_semana'               => $diaFinal,
                'hora_inicio'              => $inicioFinal,
                'hora_fin'                 => $finFinal,
                'duracion_consulta_minutos' => $data['duracion_consulta_minutos'] ?? $horario->duracion_consulta_minutos,
                'activo'                   => $data['activo'] ?? $horario->activo,
            ]);

            return [
                'mensaje' => 'Horario actualizado correctamente',
                'data'    => $horario,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function eliminarHorario(int $id)
    {
        try {
            $horario = HorarioDoctor::find($id);
            if (!$horario) {
                return ['mensaje' => 'Horario no encontrado'];
            }
            $horario->delete();
            return ['mensaje' => 'Horario eliminado correctamente'];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function verificarSolapamiento(int $doctorId, string $dia, string $inicio, string $fin, int $excluirId = null): bool
    {
        $query = HorarioDoctor::where('perfil_doctor_id', $doctorId)
            ->where('dia_semana', $dia)
            ->where('activo', true)
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('hora_inicio', [$inicio, $fin])
                    ->orWhereBetween('hora_fin', [$inicio, $fin])
                    ->orWhere(function ($q2) use ($inicio, $fin) {
                        $q2->where('hora_inicio', '<=', $inicio)
                            ->where('hora_fin', '>=', $fin);
                    });
            });

        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }
}
