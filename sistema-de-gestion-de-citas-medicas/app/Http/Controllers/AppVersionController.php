<?php

namespace App\Http\Controllers;
use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AppVersionController extends Controller
{
    public function latest(): JsonResponse
    {
        $version = AppVersion::orderBy('run_number', 'desc')->first();

        if (!$version) {
            return response()->json([
                'mensaje' => 'No hay ninguna versión registrada.',
            ], 404);
        }

        return response()->json([
            'version'      => $version->version,
            'run_number'   => $version->run_number,
            'download_url' => $version->download_url,
            'notas'        => $version->notas,
            'created_at'   => $version->created_at?->toIso8601String(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tokenEsperado = config('citas.deploy_token', env('APP_DEPLOY_TOKEN', 'secret-deploy-token-citas'));
        $tokenRecibido = $request->header('X-Deploy-Token');

        if (empty($tokenRecibido) || !hash_equals((string) $tokenEsperado, (string) $tokenRecibido)) {
            return response()->json([
                'mensaje' => 'Acceso no autorizado. Token de despliegue inválido.',
            ], 401);
        }

        $validated = $request->validate([
            'version'      => 'required|string|max:50',
            'run_number'   => 'required|integer|min:1',
            'download_url' => 'required|string|max:500',
            'notas'        => 'nullable|string|max:1000',
        ]);

        $appVersion = AppVersion::updateOrCreate(
            ['run_number' => $validated['run_number']],
            [
                'version'      => $validated['version'],
                'download_url' => $validated['download_url'],
                'notas'        => $validated['notas'] ?? null,
            ]
        );

        return response()->json([
            'mensaje' => 'Versión registrada correctamente',
            'data'    => $appVersion,
        ], 200);
    }
}
