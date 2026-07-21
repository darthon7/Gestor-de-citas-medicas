<?php

namespace App\Http\Controllers;

use App\Http\Repository\DisponibilidadRepository;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    protected $disponibilidadRepository;

    public function __construct(DisponibilidadRepository $disponibilidadRepository)
    {
        $this->disponibilidadRepository = $disponibilidadRepository;
    }

    public function obtenerDisponibilidad(Request $request, int $doctorId)
    {
        try {
            $fecha     = $request->query('fecha', now()->format('Y-m-d'));
            $resultado = $this->disponibilidadRepository->obtenerSlotsDisponibles($doctorId, $fecha);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
