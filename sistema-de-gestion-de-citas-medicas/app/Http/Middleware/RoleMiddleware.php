<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Verifica que el usuario autenticado tenga uno de los roles permitidos.
     * Uso en rutas: middleware('role:admin,recepcionista')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json(['mensaje' => 'No autenticado.'], 401);
        }

        if (!in_array($usuario->rol, $roles)) {
            return response()->json([
                'mensaje' => 'No tienes permisos para realizar esta acción. Rol requerido: ' . implode(' o ', $roles) . '.',
            ], 403);
        }

        return $next($request);
    }
}
