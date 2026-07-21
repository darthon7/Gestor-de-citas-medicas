<?php

namespace App\Http\Controllers;

use App\Http\Repository\EspecialidadesRepository;
use Illuminate\Http\Request;

class EspecialidadesController extends Controller
{
    protected $especialidadesRepository;

    public function __construct(EspecialidadesRepository $especialidadesRepository)
    {
        $this->especialidadesRepository = $especialidadesRepository;
    }

    public function obtenerEspecialidades()
    {
        try {
            $resultado = $this->especialidadesRepository->obtenerEspecialidades();
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarEspecialidad(Request $request)
    {
        try {
            $resultado = $this->especialidadesRepository->registrarEspecialidad($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
