<?php

namespace App\Http\Repository;

use App\Models\PerfilPaciente;
use App\Models\Usuario;
use Exception;

class PacientesRepository
{
    public function obtenerPacientes(array $filtros = [])
    {
        try {
            $query = Usuario::with('perfilPaciente')
                ->where('rol', 'paciente');

            if (!empty($filtros['buscar'])) {
                $buscar = $filtros['buscar'];
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%$buscar%")
                        ->orWhere('curp', 'like', "%$buscar%")
                        ->orWhereHas('perfilPaciente', function ($q2) use ($buscar) {
                            $q2->where('numero_expediente', 'like', "%$buscar%");
                        });
                });
            }

            if (!empty($filtros['estado'])) {
                $query->where('estado', $filtros['estado']);
            }

            $pacientes = $query->paginate($filtros['por_pagina'] ?? 15);

            return [
                'mensaje' => 'Pacientes obtenidos correctamente',
                'data'    => $pacientes,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarPaciente(array $data)
    {
        try {
            $usuario = Usuario::create([
                'nombre'   => $data['nombre'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
                'curp'     => strtoupper($data['curp']),
                'telefono' => $data['telefono'] ?? null,
                'rol'      => 'paciente',
                'estado'   => 'activo',
            ]);

            $numeroExpediente = 'EXP-' . now()->format('Ymd') . '-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT);

            PerfilPaciente::create([
                'usuario_id'                    => $usuario->id,
                'numero_expediente'             => $numeroExpediente,
                'fecha_nacimiento'              => $data['fecha_nacimiento'] ?? null,
                'sexo'                          => $data['sexo'] ?? null,
                'direccion'                     => $data['direccion'] ?? null,
                'contacto_emergencia_nombre'    => $data['contacto_emergencia_nombre'] ?? null,
                'contacto_emergencia_telefono'  => $data['contacto_emergencia_telefono'] ?? null,
                'nss'                           => $data['nss'] ?? null,
            ]);

            return [
                'mensaje' => 'Paciente registrado correctamente',
                'data'    => $usuario->load('perfilPaciente'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function obtenerPaciente(int $id)
    {
        try {
            $usuario = Usuario::with(['perfilPaciente.citas.notaConsulta', 'perfilPaciente.citas.perfilDoctor.usuario', 'perfilPaciente.citas.especialidad'])
                ->where('rol', 'paciente')
                ->find($id);

            if (!$usuario) {
                return ['mensaje' => 'Paciente no encontrado'];
            }

            return [
                'mensaje' => 'Paciente obtenido correctamente',
                'data'    => $usuario,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function actualizarPaciente(int $id, array $data)
    {
        try {
            $usuario = Usuario::where('rol', 'paciente')->find($id);
            if (!$usuario) {
                return ['mensaje' => 'Paciente no encontrado'];
            }

            $usuario->update([
                'nombre'   => $data['nombre']   ?? $usuario->nombre,
                'email'    => $data['email']     ?? $usuario->email,
                'telefono' => $data['telefono']  ?? $usuario->telefono,
                'curp'     => isset($data['curp']) ? strtoupper($data['curp']) : $usuario->curp,
            ]);

            if ($usuario->perfilPaciente) {
                $usuario->perfilPaciente->update([
                    'fecha_nacimiento'              => $data['fecha_nacimiento']             ?? $usuario->perfilPaciente->fecha_nacimiento,
                    'sexo'                          => $data['sexo']                         ?? $usuario->perfilPaciente->sexo,
                    'direccion'                     => $data['direccion']                    ?? $usuario->perfilPaciente->direccion,
                    'contacto_emergencia_nombre'    => $data['contacto_emergencia_nombre']   ?? $usuario->perfilPaciente->contacto_emergencia_nombre,
                    'contacto_emergencia_telefono'  => $data['contacto_emergencia_telefono'] ?? $usuario->perfilPaciente->contacto_emergencia_telefono,
                    'nss'                           => $data['nss']                          ?? $usuario->perfilPaciente->nss,
                ]);
            }

            return [
                'mensaje' => 'Paciente actualizado correctamente',
                'data'    => $usuario->load('perfilPaciente'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function desactivarPaciente(int $id)
    {
        try {
            // Buscar por id de Usuario o por id de PerfilPaciente
            $usuario = Usuario::where('rol', 'paciente')
                ->where(function ($q) use ($id) {
                    $q->where('id', $id)
                      ->orWhereHas('perfilPaciente', function ($qp) use ($id) {
                          $qp->where('id', $id);
                      });
                })->first();

            if (!$usuario) {
                return ['error' => true, 'mensaje' => 'Paciente no encontrado'];
            }

            $nuevoEstado = ($usuario->estado === 'activo') ? 'inactivo' : 'activo';

            // Si se va a desactivar, verificar citas activas pendientes
            if ($nuevoEstado === 'inactivo') {
                $citasActivas = $usuario->perfilPaciente?->citas()
                    ->whereIn('estado', ['agendada', 'confirmada', 'en_consulta'])
                    ->count();

                if ($citasActivas > 0) {
                    return ['error' => true, 'mensaje' => 'No se puede desactivar un paciente con citas activas pendientes.'];
                }
            }

            $usuario->update(['estado' => $nuevoEstado]);

            $msj = ($nuevoEstado === 'inactivo') 
                ? 'Paciente desactivado correctamente.' 
                : 'Paciente activado correctamente.';

            return ['error' => false, 'mensaje' => $msj];
        } catch (Exception $e) {
            return ['error' => true, 'mensaje' => $e->getMessage()];
        }
    }
}
