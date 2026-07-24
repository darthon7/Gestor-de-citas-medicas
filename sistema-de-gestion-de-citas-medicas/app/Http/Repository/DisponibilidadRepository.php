<?php

namespace App\Http\Repository;

use App\Models\BloqueoHorario;
use App\Models\Cita;
use App\Models\HorarioDoctor;
use Carbon\Carbon;
use Exception;

class DisponibilidadRepository
{
    public function obtenerSlotsDisponibles(int $doctorId, string $fecha)
    {
        try {
            $fechaCarbon = Carbon::parse($fecha);
            $diaSemana   = $this->traducirDia($fechaCarbon->dayOfWeek);

            // Obtener horario del doctor para ese día
            $horario = HorarioDoctor::where('perfil_doctor_id', $doctorId)
                ->where('dia_semana', $diaSemana)
                ->where('activo', true)
                ->first();

            if (!$horario) {
                return [
                    'mensaje' => 'El doctor no tiene horario configurado para ese día.',
                    'data'    => [],
                ];
            }

            // Obtener bloqueos del día
            $bloqueos = BloqueoHorario::where('perfil_doctor_id', $doctorId)
                ->where('fecha_bloqueo', $fecha)
                ->get();

            // Obtener citas ya agendadas ese día
            $citasOcupadas = Cita::where('perfil_doctor_id', $doctorId)
                ->whereDate('fecha_cita', $fecha)
                ->whereIn('estado', ['agendada', 'confirmada', 'en_consulta'])
                ->pluck('hora_cita')
                ->map(fn($h) => substr($h, 0, 8)) // normalizar a H:i:s
                ->toArray();

            // Generar slots
            $slots       = [];
            $duracion    = $horario->duracion_consulta_minutos;
            $inicio      = Carbon::createFromTimeString($horario->hora_inicio);
            $fin         = Carbon::createFromTimeString($horario->hora_fin);

            while ($inicio->copy()->addMinutes($duracion) <= $fin) {
                $horaSlot   = $inicio->format('H:i:s');
                $disponible = !in_array($horaSlot, $citasOcupadas) && !$this->estaBloquedo($horaSlot, $bloqueos);

                $slots[] = [
                    'hora'        => $horaSlot,
                    'disponible'  => $disponible,
                ];

                $inicio->addMinutes($duracion);
            }

            return [
                'mensaje'      => 'Disponibilidad obtenida correctamente',
                'fecha'        => $fecha,
                'doctor_id'    => $doctorId,
                'duracion_min' => $duracion,
                'data'         => $slots,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function verificarDisponibilidad(int $doctorId, string $fecha, string $hora): bool
    {
        $resultado = $this->obtenerSlotsDisponibles($doctorId, $fecha);
        if (!isset($resultado['data'])) {
            return false;
        }

        foreach ($resultado['data'] as $slot) {
            if ($slot['hora'] === $hora && $slot['disponible']) {
                return true;
            }
        }

        return false;
    }

    private function estaBloquedo(string $hora, $bloqueos): bool
    {
        foreach ($bloqueos as $bloqueo) {
            if ($bloqueo->hora_inicio_bloqueo && $bloqueo->hora_fin_bloqueo) {
                if ($hora >= $bloqueo->hora_inicio_bloqueo && $hora < $bloqueo->hora_fin_bloqueo) {
                    return true;
                }
            } else {
                // Bloqueo de todo el día
                return true;
            }
        }
        return false;
    }

    private function traducirDia(int $dayOfWeek): string
    {
        $dias = [
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
        ];
        return $dias[$dayOfWeek] ?? 'lunes';
    }
}
