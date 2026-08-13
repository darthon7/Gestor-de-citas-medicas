<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\AuthException;
use App\Http\Controllers\Controller;
use App\Http\Repository\AuthRepository;
use App\Http\Requests\StoreLoginRequest;
use App\Http\Requests\StoreRecuperacionRequest;
use App\Http\Requests\StoreRegistroMedicoRequest;
use App\Http\Requests\StoreRegistroPacienteRequest;
use App\Http\Requests\StoreRestablecerPasswordRequest;
use App\Http\Requests\StoreVerificarCodigoRequest;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthWebController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(StoreLoginRequest $request)
    {
        try {
            $resultado = $this->authRepository->login($request->all(), $request->ip());
            if (isset($resultado['usuario'])) {
                Auth::login($resultado['usuario'], $request->boolean('remember'));
                $request->session()->regenerate();

                if ($resultado['usuario']->rol === 'doctor') {
                    return redirect()->route('doctor.agenda');
                }

                return redirect()->intended(route('dashboard'));
            }

            return back()->withInput()->with('error', $resultado['mensaje'] ?? 'Error al iniciar sesión.');
        } catch (AuthException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            // Revocar token Sanctum si existe (sesión API); no falla si no hay token
            $this->authRepository->cerrarSesion($request->user());

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('landing')->with('success', 'Sesión cerrada correctamente.');
    }

    public function showRegistro()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.registro');
    }

    public function registrar(StoreRegistroPacienteRequest $request)
    {
        try {
            $resultado = $this->authRepository->registrarPaciente($request->all());
            if (isset($resultado['usuario'])) {
                Auth::login($resultado['usuario']);
                $request->session()->regenerate();

                return redirect()->route('dashboard')->with('success', 'Registro completado con éxito.');
            }

            return back()->withInput()->with('error', $resultado['mensaje'] ?? 'Error al registrar.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function showRecuperar()
    {
        return view('auth.recuperar-password');
    }

    public function solicitarRecuperacion(StoreRecuperacionRequest $request)
    {
        try {
            $resultado = $this->authRepository->solicitarRecuperacion($request->all());

            return redirect()->route('verificar.codigo', ['email' => $request->email])
                ->with('success', $resultado['mensaje'] ?? 'Código de recuperación enviado.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function showVerificarCodigo(Request $request)
    {
        $email = $request->query('email');

        return view('auth.verificar-codigo', compact('email'));
    }

    public function verificarCodigo(StoreVerificarCodigoRequest $request)
    {
        try {
            $resultado = $this->authRepository->verificarCodigo($request->all());
            if (isset($resultado['valido']) && $resultado['valido']) {
                return redirect()->route('restablecer', [
                    'email' => $request->email,
                    'codigo' => $request->codigo,
                ])->with('success', 'Código verificado exitosamente.');
            }

            return back()->withInput()->with('error', $resultado['mensaje'] ?? 'Código no válido o expirado.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function showRestablecer(Request $request)
    {
        $email = $request->query('email');
        $codigo = $request->query('codigo');

        return view('auth.restablecer-password', compact('email', 'codigo'));
    }

    public function restablecerPassword(StoreRestablecerPasswordRequest $request)
    {
        try {
            $resultado = $this->authRepository->restablecerPassword($request->all());

            return redirect()->route('login')->with('success', $resultado['mensaje'] ?? 'Contraseña restablecida correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // Registro público de Doctor (auto-solicitud con validación admin)
    // ---------------------------------------------------------------
    public function showRegistroDoctor()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Obtener especialidades para el selector del formulario
        $especialidades = Especialidad::orderBy('nombre')->get();

        return view('auth.registro-doctor', compact('especialidades'));
    }

    public function registrarDoctor(StoreRegistroMedicoRequest $request)
    {
        try {
            $resultado = $this->authRepository->registrarMedico($request->all());

            // Si hay error de cédula u otro, regresar con el mensaje
            if (! isset($resultado['usuario'])) {
                return back()->withInput()->with('error', $resultado['mensaje'] ?? 'No fue posible registrar la solicitud.');
            }

            // El doctor NO inicia sesión. Su cuenta queda en estado_validacion = pendiente.
            // El admin debe aprobarla desde el panel de Gestión de Doctores.
            return redirect()->route('login')->with('success',
                '✅ Solicitud enviada con éxito. Tu cuenta está pendiente de validación por el administrador. '.
                'Recibirás acceso una vez que sea aprobada.'
            );
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
