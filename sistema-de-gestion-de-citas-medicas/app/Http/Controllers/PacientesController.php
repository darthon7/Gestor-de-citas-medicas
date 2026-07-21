<?php

namespace App\Http\Controllers;

use App\Http\Repository\PacientesRepository;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use Illuminate\Http\Request;

class PacientesController extends Controller
{
    protected $pacientesRepository;

    public function __construct(PacientesRepository $pacientesRepository)
    {
        $this->pacientesRepository = $pacientesRepository;
    }

    public function obtenerPacientes(Request $request)
    {
        try {
            $resultado = $this->pacientesRepository->obtenerPacientes($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarPaciente(StorePacienteRequest $request)
    {
        try {
            $resultado = $this->pacientesRepository->registrarPaciente($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function obtenerPaciente(int $id)
    {
        try {
            $resultado = $this->pacientesRepository->obtenerPaciente($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function actualizarPaciente(UpdatePacienteRequest $request, int $id)
    {
        try {
            $resultado = $this->pacientesRepository->actualizarPaciente($id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function desactivarPaciente(int $id)
    {
        try {
            $resultado = $this->pacientesRepository->desactivarPaciente($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
