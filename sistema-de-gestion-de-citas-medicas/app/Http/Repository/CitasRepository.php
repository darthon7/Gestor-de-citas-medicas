<?php

namespace App\Http\Repository;

use App\Models\Cita;
use Carbon\Carbon;
use Exception;

class CitasRepository
{
    protected DisponibilidadRepository $disponibilidadRepo;

    public function __construct(DisponibilidadRepository $disponibilidadRepo)
    {
        $this->disponibilidadRepo = $disponibilidadRepo;
    }

    public function obtenerCitas(array $filtros = [])
    {
        try {
            $query = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad']);

            if (!empty($filtros['estado'])) {
                $query->where('estado', $filtros['estado']);
            }

            if (!empty($filtros['doctor_id'])) {
                $query->where('perfil_doctor_id', $filtros['doctor_id']);
            }

            if (!empty($filtros['paciente_id'])) {
                $query->where('perfil_paciente_id', $filtros['paciente_id']);
            }

            if (!empty($filtros['fecha'])) {
                $query->where('fecha_cita', $filtros['fecha']);
            }

            if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
                $query->whereBetween('fecha_cita', [$filtros['fecha_inicio'], $filtros['fecha_fin']]);
            }

            $citas = $query->orderBy('fecha_cita')->orderBy('hora_cita')
                ->paginate($filtros['por_pagina'] ?? 15);

            return [
                'mensaje' => 'Citas obtenidas correctamente',
                'data'    => $citas,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarCita(array $data)
    {
        try {
            // Verificar primero si el slot ya está ocupado
            $ocupado = Cita::where('perfil_doctor_id', $data['perfil_doctor_id'])
                ->whereDate('fecha_cita', $data['fecha_cita'])
                ->where('hora_cita', $data['hora_cita'])
                ->whereIn('estado', ['agendada', 'confirmada', 'en_consulta'])
                ->exists();

            if ($ocupado) {
                return ['mensaje' => 'Ya existe una cita agendada para este doctor en ese horario.'];
            }

            // Verificar disponibilidad (horario del doctor y bloqueos)
            $disponible = $this->disponibilidadRepo->verificarDisponibilidad(
                $data['perfil_doctor_id'],
                $data['fecha_cita'],
                $data['hora_cita']
            );

            if (!$disponible) {
                return ['mensaje' => 'El horario seleccionado no está disponible para este doctor.'];
            }

            $codigoReferencia = 'CITA-' . strtoupper(substr(uniqid(), -6));

            $cita = Cita::create([
                'perfil_paciente_id' => $data['perfil_paciente_id'],
                'perfil_doctor_id'   => $data['perfil_doctor_id'],
                'especialidad_id'    => $data['especialidad_id'],
                'codigo_referencia'  => $codigoReferencia,
                'fecha_cita'         => $data['fecha_cita'],
                'hora_cita'          => $data['hora_cita'],
                'duracion_minutos'   => $data['duracion_minutos'] ?? 30,
                'motivo_consulta'    => $data['motivo_consulta'] ?? null,
                'estado'             => 'agendada',
            ]);

            return [
                'mensaje' => 'Cita registrada correctamente',
                'data'    => $cita->load(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }


    public function obtenerCita(int $id)
    {
        try {
            $cita = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad', 'notaConsulta.creadoPor'])->find($id);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }
            return [
                'mensaje' => 'Cita obtenida correctamente',
                'data'    => $cita,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function obtenerCitaPaciente(int $id, int $pacienteId)
    {
        try {
            $cita = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad', 'notaConsulta.creadoPor'])
                ->where('id', $id)
                ->where('perfil_paciente_id', $pacienteId)
                ->first();

            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }
            return [
                'mensaje' => 'Cita obtenida correctamente',
                'data'    => $cita,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }


    public function reprogramarCita(int $id, array $data)
    {
        try {
            $cita = Cita::find($id);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if (in_array($cita->estado, ['completada', 'cancelada'])) {
                return ['mensaje' => 'No se puede reprogramar una cita completada o cancelada.'];
            }

            $disponible = $this->disponibilidadRepo->verificarDisponibilidad(
                $cita->perfil_doctor_id,
                $data['fecha_cita'],
                $data['hora_cita']
            );

            if (!$disponible) {
                return ['mensaje' => 'El nuevo horario no está disponible para este doctor.'];
            }

            $ocupado = Cita::where('perfil_doctor_id', $cita->perfil_doctor_id)
                ->whereDate('fecha_cita', $data['fecha_cita'])
                ->where('hora_cita', $data['hora_cita'])
                ->where('id', '!=', $id)
                ->whereIn('estado', ['agendada', 'confirmada', 'en_consulta'])
                ->exists();

            if ($ocupado) {
                return ['mensaje' => 'Ya existe una cita en el nuevo horario seleccionado.'];
            }

            $cita->update([
                'fecha_cita' => $data['fecha_cita'],
                'hora_cita'  => $data['hora_cita'],
                'estado'     => 'agendada',
            ]);

            return [
                'mensaje' => 'Cita reprogramada correctamente',
                'data'    => $cita->load(['perfilPaciente.usuario', 'perfilDoctor.usuario']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function cancelarCita(int $id, array $data, int $usuarioId)
    {
        try {
            $cita = Cita::find($id);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if (in_array($cita->estado, ['completada', 'cancelada'])) {
                return ['mensaje' => 'La cita ya está completada o cancelada.'];
            }

            $cita->update([
                'estado'             => 'cancelada',
                'motivo_cancelacion' => $data['motivo_cancelacion'] ?? null,
                'cancelado_por'      => $usuarioId,
                'cancelado_en'       => now(),
            ]);

            return [
                'mensaje' => 'Cita cancelada correctamente',
                'data'    => $cita,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function checkInCita(int $id, int $usuarioId)
    {
        try {
            $cita = Cita::find($id);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if (!in_array($cita->estado, ['agendada', 'confirmada'])) {
                return ['mensaje' => 'Solo se puede hacer check-in a citas agendadas o confirmadas.'];
            }

            $cita->update([
                'estado'     => 'confirmada',
                'checkin_en' => now(),
                'checkin_por' => $usuarioId,
            ]);

            return [
                'mensaje' => 'Check-in registrado correctamente',
                'data'    => $cita,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function iniciarConsulta(int $id)
    {
        try {
            $cita = Cita::find($id);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if (!in_array($cita->estado, ['agendada', 'confirmada', 'en_consulta'])) {
                return ['mensaje' => 'Solo se puede iniciar consulta en citas agendadas o confirmadas.'];
            }

            if ($cita->estado !== 'en_consulta') {
                $cita->update(['estado' => 'en_consulta']);
            }

            return [
                'mensaje' => 'Consulta iniciada',
                'data'    => $cita,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function completarCita(int $id)
    {
        try {
            $cita = Cita::find($id);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if ($cita->estado === 'cancelada') {
                return ['mensaje' => 'No se puede completar una cita cancelada.'];
            }

            $cita->update(['estado' => 'completada']);

            return [
                'mensaje' => 'Cita completada correctamente',
                'data'    => $cita,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    // Para pacientes móviles
    public function registrarCitaPaciente(array $data, int $pacienteId)
    {
        try {
            // Un paciente no puede tener más de una cita activa con el mismo doctor el mismo día
            $duplicada = Cita::where('perfil_paciente_id', $pacienteId)
                ->where('perfil_doctor_id', $data['perfil_doctor_id'])
                ->whereDate('fecha_cita', $data['fecha_cita'])
                ->whereIn('estado', ['agendada', 'confirmada', 'en_consulta'])
                ->exists();

            if ($duplicada) {
                return ['mensaje' => 'Ya tienes una cita activa con este médico en esa fecha.'];
            }

            $data['perfil_paciente_id'] = $pacienteId;
            return $this->registrarCita($data);
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function cancelarCitaPaciente(int $id, array $data, int $pacienteId, int $usuarioId)
    {
        try {
            $cita = Cita::where('id', $id)
                ->where('perfil_paciente_id', $pacienteId)
                ->first();

            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if (in_array($cita->estado, ['completada', 'cancelada'])) {
                return ['mensaje' => 'La cita ya está completada o cancelada.'];
            }

            // Restricción: solo cancelar con al menos 2 horas de anticipación
            $horaLimite = Carbon::parse($cita->fecha_cita->format('Y-m-d') . ' ' . $cita->hora_cita)->subHours(2);
            if (now()->greaterThan($horaLimite)) {
                return ['mensaje' => 'Solo puedes cancelar con al menos 2 horas de anticipación a la cita.'];
            }

            $cita->update([
                'estado'             => 'cancelada',
                'motivo_cancelacion' => $data['motivo_cancelacion'] ?? 'Cancelada por el paciente',
                'cancelado_por'      => $usuarioId, // usuario_id, consistente con cancelarCita() del admin
                'cancelado_en'       => now(),
            ]);

            return [
                'mensaje' => 'Cita cancelada correctamente',
                'data'    => $cita,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
