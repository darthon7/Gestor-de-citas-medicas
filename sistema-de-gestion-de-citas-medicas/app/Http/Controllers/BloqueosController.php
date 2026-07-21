<?php

namespace App\Http\Controllers;

use App\Http\Repository\BloqueosRepository;
use App\Http\Requests\StoreBloqueoRequest;

class BloqueosController extends Controller
{
    protected $bloqueosRepository;

    public function __construct(BloqueosRepository $bloqueosRepository)
    {
        $this->bloqueosRepository = $bloqueosRepository;
    }

    public function obtenerBloqueos(int $doctorId)
    {
        try {
            $resultado = $this->bloqueosRepository->obtenerBloqueos($doctorId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarBloqueo(StoreBloqueoRequest $request, int $doctorId)
    {
        try {
            $usuarioId = $request->user()->id;
            $resultado = $this->bloqueosRepository->registrarBloqueo($doctorId, $request->all(), $usuarioId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function eliminarBloqueo(int $id)
    {
        try {
            $resultado = $this->bloqueosRepository->eliminarBloqueo($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
