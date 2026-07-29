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
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['mensaje' => 'No autenticado.'], 401);
            }
            return redirect()->route('login');
        }

        if (!in_array($usuario->rol, $roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'mensaje' => 'No tienes permisos para realizar esta acción. Rol requerido: ' . implode(' o ', $roles) . '.',
                ], 403);
            }
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        return $next($request);
    }
}
