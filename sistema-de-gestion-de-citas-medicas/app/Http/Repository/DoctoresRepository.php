<?php

namespace App\Http\Repository;

use App\Models\PerfilDoctor;
use App\Models\Usuario;
use Exception;

class DoctoresRepository
{
    public function obtenerDoctores(array $filtros = [])
    {
        try {
            $query = PerfilDoctor::with(['usuario', 'especialidades']);

            if (!empty($filtros['especialidad_id'])) {
                $query->whereHas('especialidades', function ($q) use ($filtros) {
                    $q->where('especialidades.id', $filtros['especialidad_id']);
                });
            }

            if (!empty($filtros['estado_validacion'])) {
                $query->where('estado_validacion', $filtros['estado_validacion']);
            }

            if (!empty($filtros['buscar'])) {
                $buscar = $filtros['buscar'];
                $query->whereHas('usuario', function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%$buscar%");
                });
            }

            $doctores = $query->paginate($filtros['por_pagina'] ?? 15);

            return [
                'mensaje' => 'Doctores obtenidos correctamente',
                'data'    => $doctores,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarDoctor(array $data)
    {
        try {
            $usuario = Usuario::create([
                'nombre'   => $data['nombre'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password'] ?? 'Doctor1234!'),
                'curp'     => isset($data['curp']) ? strtoupper($data['curp']) : null,
                'telefono' => $data['telefono'] ?? null,
                'rol'      => 'doctor',
                'estado'   => 'activo',
            ]);

            $perfilDoctor = PerfilDoctor::create([
                'usuario_id'          => $usuario->id,
                'cedula_profesional'  => $data['cedula_profesional'],
                'cedula_especialidad' => $data['cedula_especialidad'] ?? null,
                'estado_validacion'   => $data['estado_validacion'] ?? 'pendiente',
            ]);

            if (!empty($data['especialidades'])) {
                $perfilDoctor->especialidades()->sync($data['especialidades']);
            }

            return [
                'mensaje' => 'Doctor registrado correctamente',
                'data'    => $perfilDoctor->load(['usuario', 'especialidades']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function obtenerDoctor(int $id)
    {
        try {
            $doctor = PerfilDoctor::with(['usuario', 'especialidades', 'horarios'])
                ->find($id);

            if (!$doctor) {
                return ['mensaje' => 'Doctor no encontrado'];
            }

            return [
                'mensaje' => 'Doctor obtenido correctamente',
                'data'    => $doctor,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function actualizarDoctor(int $id, array $data)
    {
        try {
            $doctor = PerfilDoctor::find($id);
            if (!$doctor) {
                return ['mensaje' => 'Doctor no encontrado'];
            }

            $doctor->usuario->update([
                'nombre'   => $data['nombre']   ?? $doctor->usuario->nombre,
                'email'    => $data['email']     ?? $doctor->usuario->email,
                'telefono' => $data['telefono']  ?? $doctor->usuario->telefono,
            ]);

            $doctor->update([
                'cedula_profesional'  => $data['cedula_profesional']  ?? $doctor->cedula_profesional,
                'cedula_especialidad' => $data['cedula_especialidad']  ?? $doctor->cedula_especialidad,
            ]);

            if (!empty($data['especialidades'])) {
                $doctor->especialidades()->sync($data['especialidades']);
            }

            return [
                'mensaje' => 'Doctor actualizado correctamente',
                'data'    => $doctor->load(['usuario', 'especialidades']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function validarDoctor(int $id, array $data, int $adminId)
    {
        try {
            $doctor = PerfilDoctor::find($id);
            if (!$doctor) {
                return ['mensaje' => 'Doctor no encontrado'];
            }

            $doctor->update([
                'estado_validacion' => $data['estado_validacion'],
                'notas_validacion'  => $data['notas_validacion'] ?? null,
                'validado_por'      => $adminId,
                'validado_en'       => now(),
            ]);

            $estadoUsuario = $data['estado_validacion'] === 'rechazado' ? 'inactivo' : 'activo';
            $doctor->usuario->update(['estado' => $estadoUsuario]);

            return [
                'mensaje' => 'Estado de validación actualizado',
                'data'    => $doctor->load('usuario'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
