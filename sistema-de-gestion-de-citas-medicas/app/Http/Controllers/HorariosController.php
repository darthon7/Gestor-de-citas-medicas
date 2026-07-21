<?php

namespace App\Http\Controllers;

use App\Http\Repository\HorariosRepository;
use App\Http\Requests\StoreHorarioRequest;
use Illuminate\Http\Request;

class HorariosController extends Controller
{
    protected $horariosRepository;

    public function __construct(HorariosRepository $horariosRepository)
    {
        $this->horariosRepository = $horariosRepository;
    }

    public function obtenerHorarios(int $doctorId)
    {
        try {
            $resultado = $this->horariosRepository->obtenerHorarios($doctorId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarHorario(StoreHorarioRequest $request, int $doctorId)
    {
        try {
            $resultado = $this->horariosRepository->registrarHorario($doctorId, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function actualizarHorario(Request $request, int $id)
    {
        try {
            $resultado = $this->horariosRepository->actualizarHorario($id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function eliminarHorario(int $id)
    {
        try {
            $resultado = $this->horariosRepository->eliminarHorario($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
