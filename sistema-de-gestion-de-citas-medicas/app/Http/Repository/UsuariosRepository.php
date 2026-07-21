<?php

namespace App\Http\Repository;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Exception;

class UsuariosRepository
{
    public function obtenerPerfil(int $id)
    {
        try {
            $usuario = Usuario::with([
                'perfilDoctor.especialidades',
                'perfilPaciente',
                'perfilRecepcionista',
            ])->find($id);

            if (!$usuario) {
                return ['mensaje' => 'Usuario no encontrado'];
            }

            return [
                'mensaje' => 'Perfil obtenido correctamente',
                'data'    => $usuario,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function actualizarPerfil(int $id, array $data)
    {
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return ['mensaje' => 'Usuario no encontrado'];
            }

            $usuario->update([
                'nombre'   => $data['nombre']   ?? $usuario->nombre,
                'telefono' => $data['telefono']  ?? $usuario->telefono,
            ]);

            // Actualizar perfil específico según rol
            if ($usuario->rol === 'paciente' && $usuario->perfilPaciente) {
                $usuario->perfilPaciente->update([
                    'direccion'                    => $data['direccion']                    ?? $usuario->perfilPaciente->direccion,
                    'contacto_emergencia_nombre'   => $data['contacto_emergencia_nombre']   ?? $usuario->perfilPaciente->contacto_emergencia_nombre,
                    'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? $usuario->perfilPaciente->contacto_emergencia_telefono,
                ]);
            }

            return [
                'mensaje' => 'Perfil actualizado correctamente',
                'data'    => $usuario->load(['perfilDoctor', 'perfilPaciente', 'perfilRecepcionista']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function cambiarPassword(int $id, array $data)
    {
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return ['mensaje' => 'Usuario no encontrado'];
            }

            if (!Hash::check($data['password_actual'], $usuario->password)) {
                return ['mensaje' => 'La contraseña actual es incorrecta.'];
            }

            $usuario->update(['password' => Hash::make($data['password'])]);

            return ['mensaje' => 'Contraseña actualizada correctamente'];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function actualizarFoto(int $id, string $rutaFoto)
    {
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return ['mensaje' => 'Usuario no encontrado'];
            }

            $usuario->update(['foto_perfil' => $rutaFoto]);

            return [
                'mensaje' => 'Foto de perfil actualizada correctamente',
                'data'    => ['foto_perfil' => $rutaFoto],
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
