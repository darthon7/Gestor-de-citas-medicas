<?php

namespace App\Http\Repository;

use App\Exceptions\AuthException;
use App\Mail\CodigoRecuperacionMail;
use App\Models\IntentoLogin;
use App\Models\PerfilDoctor;
use App\Models\PerfilPaciente;
use App\Models\PerfilRecepcionista;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Exception;

class AuthRepository
{
    public function login(array $credenciales, string $ip = null)
    {
        try {
            $usuario = Usuario::where('email', $credenciales['email'])->first();

            // Registrar intento
            IntentoLogin::create([
                'email'       => $credenciales['email'],
                'direccion_ip' => $ip,
                'exitoso'     => false,
            ]);

            if (!$usuario) {
                throw new AuthException('Las credenciales ingresadas son incorrectas', 401);
            }

            // Verificar si está bloqueado
            if ($usuario->estado === 'bloqueado' && $usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture()) {
                throw new AuthException('Cuenta bloqueada temporalmente. Intenta en ' . $usuario->bloqueado_hasta->diffForHumans(), 403);
            }

            // Si el bloqueo expiró, resetear
            if ($usuario->estado === 'bloqueado' && $usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isPast()) {
                $usuario->update(['estado' => 'activo', 'intentos_fallidos' => 0, 'bloqueado_hasta' => null]);
            }

            if ($usuario->estado === 'inactivo') {
                throw new AuthException('Tu cuenta está desactivada. Contacta al administrador.', 403);
            }

            if (!Hash::check($credenciales['password'], $usuario->password)) {
                $intentos = $usuario->intentos_fallidos + 1;

                if ($intentos >= 5) {
                    $usuario->update([
                        'intentos_fallidos' => $intentos,
                        'estado'            => 'bloqueado',
                        'bloqueado_hasta'   => Carbon::now()->addMinutes(15),
                    ]);
                    throw new AuthException('Cuenta bloqueada por 15 minutos tras 5 intentos fallidos.', 403);
                }

                $usuario->update(['intentos_fallidos' => $intentos]);
                throw new AuthException('Las credenciales ingresadas son incorrectas. Intentos restantes: ' . (5 - $intentos), 401);
            }

            // Login exitoso: resetear intentos
            $usuario->update(['intentos_fallidos' => 0, 'bloqueado_hasta' => null]);
            IntentoLogin::latest()->where('email', $credenciales['email'])->first()?->update(['exitoso' => true]);

            // Médico debe estar validado
            if ($usuario->rol === 'doctor') {
                $perfil = PerfilDoctor::where('usuario_id', $usuario->id)->first();
                if ($perfil && $perfil->estado_validacion !== 'validado') {
                    throw new AuthException('Tu cuenta de médico está pendiente de validación por el administrador.', 403);
                }
            }

            $token = $usuario->createToken('auth')->plainTextToken;

            return [
                'mensaje'  => 'Login correcto',
                'usuario'  => $usuario,
                'rol'      => $usuario->rol,
                'token'    => $token,
            ];
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function solicitarRecuperacion(array $data)
    {
        try {
            $email = $data['email'];
            $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_resets')->where('email', $email)->delete();
            DB::table('password_resets')->insert([
                'email'      => $email,
                'codigo'     => $codigo,
                'created_at' => Carbon::now(),
            ]);

            Mail::to($email)->send(new CodigoRecuperacionMail($codigo));

            return [
                'mensaje' => 'Código de recuperación enviado a tu correo electrónico.',
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function verificarCodigo(array $data)
    {
        try {
            $reset = DB::table('password_resets')
                ->where('email', $data['email'])
                ->where('codigo', $data['codigo'])
                ->first();

            if (!$reset) {
                return ['valido' => false, 'mensaje' => 'El código de verificación es incorrecto.'];
            }

            if (Carbon::parse($reset->created_at)->addMinutes(30)->isPast()) {
                return ['valido' => false, 'mensaje' => 'El código de verificación ha expirado. Solicita uno nuevo.'];
            }

            return ['valido' => true, 'mensaje' => 'Código verificado correctamente.'];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function restablecerPassword(array $data)
    {
        try {
            $verificacion = $this->verificarCodigo($data);
            if (isset($verificacion['valido']) && !$verificacion['valido']) {
                return ['mensaje' => $verificacion['mensaje']];
            }

            $usuario = Usuario::where('email', $data['email'])->first();
            if (!$usuario) {
                return ['mensaje' => 'Usuario no encontrado.'];
            }

            $usuario->update([
                'password' => Hash::make($data['password']),
            ]);

            DB::table('password_resets')->where('email', $data['email'])->delete();

            return [
                'mensaje' => 'Contraseña restablecida correctamente.',
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
                'password' => Hash::make($data['password']),
                'curp'     => strtoupper($data['curp']),
                'telefono' => $data['telefono'] ?? null,
                'rol'      => 'paciente',
                'estado'   => 'activo',
            ]);

            $numeroExpediente = 'EXP-' . now()->format('Ymd') . '-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT);

            PerfilPaciente::create([
                'usuario_id'        => $usuario->id,
                'numero_expediente' => $numeroExpediente,
                'fecha_nacimiento'  => $data['fecha_nacimiento'] ?? null,
                'sexo'              => $data['sexo'] ?? null,
                'direccion'         => $data['direccion'] ?? null,
                'nss'               => $data['nss'] ?? null,
            ]);

            $token = $usuario->createToken('auth')->plainTextToken;

            return [
                'mensaje'  => 'Paciente registrado correctamente',
                'usuario'  => $usuario->load('perfilPaciente'),
                'token'    => $token,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarMedico(array $data)
    {
        try {
            $usuario = Usuario::create([
                'nombre'   => $data['nombre'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'curp'     => strtoupper($data['curp']),
                'telefono' => $data['telefono'] ?? null,
                'rol'      => 'doctor',
                'estado'   => 'activo',
            ]);

            $perfilDoctor = PerfilDoctor::create([
                'usuario_id'          => $usuario->id,
                'cedula_profesional'  => $data['cedula_profesional'],
                'cedula_especialidad' => $data['cedula_especialidad'] ?? null,
                'estado_validacion'   => 'pendiente',
            ]);

            // Asignar especialidades
            if (!empty($data['especialidades'])) {
                $perfilDoctor->especialidades()->sync($data['especialidades']);
            }

            return [
                'mensaje'  => 'Médico registrado. Tu cuenta está pendiente de validación por el administrador.',
                'usuario'  => $usuario->load('perfilDoctor'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarRecepcionista(array $data, int $adminId)
    {
        try {
            $usuario = Usuario::create([
                'nombre'   => $data['nombre'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'curp'     => isset($data['curp']) ? strtoupper($data['curp']) : null,
                'telefono' => $data['telefono'] ?? null,
                'rol'      => 'recepcionista',
                'estado'   => 'activo',
            ]);

            PerfilRecepcionista::create([
                'usuario_id'          => $usuario->id,
                'numero_empleado'     => $data['numero_empleado'] ?? null,
                'unidad_asignada'     => $data['unidad_asignada'] ?? null,
                'turno'               => $data['turno'] ?? null,
                'creado_por_admin_id' => $adminId,
            ]);

            return [
                'mensaje'  => 'Recepcionista registrada correctamente',
                'usuario'  => $usuario->load('perfilRecepcionista'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function cerrarSesion(Usuario $usuario)
    {
        try {
// Solo eliminar token si existe (sesión API con Sanctum)
            $token = $usuario->currentAccessToken();
            if ($token) {
                $token->delete();
            }
            return ['mensaje' => 'Sesión cerrada correctamente'];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
