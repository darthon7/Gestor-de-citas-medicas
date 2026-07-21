<?php

namespace App\Http\Middleware;

use Closure;
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
            return response()->json(['mensaje' => 'No autenticado.'], 401);
        }

        if ($usuario->estado === 'inactivo') {
            return response()->json(['mensaje' => 'Tu cuenta está desactivada. Contacta al administrador.'], 403);
        }

        if ($usuario->estado === 'bloqueado') {
            if ($usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture()) {
                return response()->json([
                    'mensaje' => 'Tu cuenta está bloqueada temporalmente hasta ' . $usuario->bloqueado_hasta->format('H:i') . '.',
                ], 403);
            }
            // Si expiró el bloqueo, lo resetear automáticamente
            $usuario->update(['estado' => 'activo', 'intentos_fallidos' => 0, 'bloqueado_hasta' => null]);
        }

        return $next($request);
    }
}
