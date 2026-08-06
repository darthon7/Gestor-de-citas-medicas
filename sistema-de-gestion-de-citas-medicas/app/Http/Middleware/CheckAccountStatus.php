<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Verifica que la cuenta del usuario esté activa (no bloqueada ni inactiva).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['mensaje' => 'No autenticado.'], 401);
            }
            return redirect()->route('login');
        }

        if ($usuario->estado === 'inactivo') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['mensaje' => 'Tu cuenta está desactivada. Contacta al administrador.'], 403);
            }
            \Illuminate\Support\Facades\Auth::logout();
            return redirect()->route('login')->with('error', 'Tu cuenta está desactivada. Contacta al administrador.');
        }

        if ($usuario->estado === 'bloqueado') {
            if ($usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture()) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'mensaje' => 'Tu cuenta está bloqueada temporalmente hasta ' . $usuario->bloqueado_hasta->format('H:i') . '.',
                    ], 403);
                }
                \Illuminate\Support\Facades\Auth::logout();
                return redirect()->route('login')->with('error', 'Tu cuenta está bloqueada temporalmente hasta ' . $usuario->bloqueado_hasta->format('H:i') . '.');
            }
            // Si expiró el bloqueo, lo resetear automáticamente
            $usuario->update(['estado' => 'activo', 'intentos_fallidos' => 0, 'bloqueado_hasta' => null]);
        }

        $response = $next($request);

        return $response instanceof ViewContract
            ? $response->toResponse($request)
            : $response;
    }
}
