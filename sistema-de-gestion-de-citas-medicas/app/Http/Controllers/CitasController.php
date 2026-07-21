<?php

namespace App\Http\Controllers;

use App\Http\Repository\CitasRepository;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UpdateCitaRequest;
use App\Http\Requests\CancelacionCitaRequest;
use Illuminate\Http\Request;

class CitasController extends Controller
{
    protected $citasRepository;

    public function __construct(CitasRepository $citasRepository)
    {
        $this->citasRepository = $citasRepository;
    }

    public function obtenerCitas(Request $request)
    {
        try {
            $resultado = $this->citasRepository->obtenerCitas($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarCita(StoreCitaRequest $request)
    {
        try {
            $resultado = $this->citasRepository->registrarCita($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function obtenerCita(int $id)
    {
        try {
            $resultado = $this->citasRepository->obtenerCita($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function reprogramarCita(UpdateCitaRequest $request, int $id)
    {
        try {
            $resultado = $this->citasRepository->reprogramarCita($id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function cancelarCita(CancelacionCitaRequest $request, int $id)
    {
        try {
            $usuarioId = $request->user()->id;
            $resultado = $this->citasRepository->cancelarCita($id, $request->all(), $usuarioId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function checkInCita(Request $request, int $id)
    {
        try {
            $usuarioId = $request->user()->id;
            $resultado = $this->citasRepository->checkInCita($id, $usuarioId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function iniciarConsulta(int $id)
    {
        try {
            $resultado = $this->citasRepository->iniciarConsulta($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function completarCita(int $id)
    {
        try {
            $resultado = $this->citasRepository->completarCita($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    // Métodos para pacientes (app móvil)
    public function agendarCita(StoreCitaRequest $request)
    {
        try {
            $pacienteId = $request->user()->perfilPaciente->id;
            $resultado  = $this->citasRepository->registrarCitaPaciente($request->all(), $pacienteId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function misCitas(Request $request)
    {
        try {
            $pacienteId = $request->user()->perfilPaciente->id;
            $resultado  = $this->citasRepository->obtenerCitas(['perfil_paciente_id' => $pacienteId]);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function miCita(Request $request, int $id)
    {
        try {
            $resultado = $this->citasRepository->obtenerCita($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function cancelarMiCita(CancelacionCitaRequest $request, int $id)
    {
        try {
            $pacienteId = $request->user()->perfilPaciente->id;
            $resultado  = $this->citasRepository->cancelarCitaPaciente($id, $request->all(), $pacienteId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
