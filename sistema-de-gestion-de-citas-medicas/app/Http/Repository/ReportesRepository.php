<?php

namespace App\Http\Repository;

use App\Models\Cita;
use App\Models\PerfilDoctor;
use App\Models\Especialidad;
use App\Models\Usuario;
use Exception;

class ReportesRepository
{
    public function reporteCitas(array $filtros = [])
    {
        try {
            $query = Cita::query();

            if (!empty($filtros['fecha_inicio'])) {
                $query->where('fecha_cita', '>=', $filtros['fecha_inicio']);
            }
            if (!empty($filtros['fecha_fin'])) {
                $query->where('fecha_cita', '<=', $filtros['fecha_fin']);
            }
            if (!empty($filtros['doctor_id'])) {
                $query->where('perfil_doctor_id', $filtros['doctor_id']);
            }
            if (!empty($filtros['especialidad_id'])) {
                $query->where('especialidad_id', $filtros['especialidad_id']);
            }

            $total      = $query->count();
            $agendadas  = (clone $query)->where('estado', 'agendada')->count();
            $confirmadas = (clone $query)->where('estado', 'confirmada')->count();
            $completadas = (clone $query)->where('estado', 'completada')->count();
            $canceladas  = (clone $query)->where('estado', 'cancelada')->count();

            return [
                'mensaje' => 'Reporte de citas generado',
                'data'    => [
                    'total'      => $total,
                    'agendadas'  => $agendadas,
                    'confirmadas' => $confirmadas,
                    'completadas' => $completadas,
                    'canceladas'  => $canceladas,
                    'citas'       => $query->with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad'])
                        ->orderBy('fecha_cita')
                        ->get(),
                ],
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function reporteDoctores(array $filtros = [])
    {
        try {
            $query = PerfilDoctor::withCount([
                'citas as total_consultas' => function ($q) use ($filtros) {
                    $q->where('estado', 'completada');
                    if (!empty($filtros['fecha_inicio'])) {
                        $q->where('fecha_cita', '>=', $filtros['fecha_inicio']);
                    }
                    if (!empty($filtros['fecha_fin'])) {
                        $q->where('fecha_cita', '<=', $filtros['fecha_fin']);
                    }
                },
            ])->with(['usuario', 'especialidades'])
                ->orderBy('total_consultas', 'desc');

            return [
                'mensaje' => 'Reporte de doctores generado',
                'data'    => $query->get(),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function reporteEspecialidades(array $filtros = [])
    {
        try {
            $especialidades = Especialidad::withCount([
                'citas as total_citas' => function ($q) use ($filtros) {
                    if (!empty($filtros['fecha_inicio'])) {
                        $q->where('fecha_cita', '>=', $filtros['fecha_inicio']);
                    }
                    if (!empty($filtros['fecha_fin'])) {
                        $q->where('fecha_cita', '<=', $filtros['fecha_fin']);
                    }
                },
            ])->orderBy('total_citas', 'desc')->get();

            return [
                'mensaje' => 'Reporte de especialidades generado',
                'data'    => $especialidades,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function reportePacientes(array $filtros = [])
    {
        try {
            $pacientes = Usuario::where('rol', 'paciente')
                ->with('perfilPaciente')
                ->withCount([
                    'perfilPaciente as total_citas' => function ($q) use ($filtros) {
                        // Join via perfil_paciente -> citas
                    },
                ])->get();

            // Contar citas por paciente
            $resultado = $pacientes->map(function ($paciente) use ($filtros) {
                $query = $paciente->perfilPaciente?->citas()
                    ->where('estado', 'completada');

                if (!empty($filtros['fecha_inicio'])) {
                    $query?->where('fecha_cita', '>=', $filtros['fecha_inicio']);
                }
                if (!empty($filtros['fecha_fin'])) {
                    $query?->where('fecha_cita', '<=', $filtros['fecha_fin']);
                }

                return [
                    'id'               => $paciente->id,
                    'nombre'           => $paciente->nombre,
                    'expediente'       => $paciente->perfilPaciente?->numero_expediente,
                    'total_consultas'  => $query?->count() ?? 0,
                ];
            })->sortByDesc('total_consultas')->values();

            return [
                'mensaje' => 'Reporte de pacientes generado',
                'data'    => $resultado,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function resumenDiario(string $fecha)
    {
        try {
            $citas = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad'])
                ->where('fecha_cita', $fecha)
                ->orderBy('hora_cita')
                ->get();

            $resumen = [
                'fecha'       => $fecha,
                'total'       => $citas->count(),
                'agendadas'   => $citas->where('estado', 'agendada')->count(),
                'confirmadas' => $citas->where('estado', 'confirmada')->count(),
                'en_consulta' => $citas->where('estado', 'en_consulta')->count(),
                'completadas' => $citas->where('estado', 'completada')->count(),
                'canceladas'  => $citas->where('estado', 'cancelada')->count(),
                'citas'       => $citas,
            ];

            return [
                'mensaje' => 'Resumen diario generado',
                'data'    => $resumen,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
