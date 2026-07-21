<?php

namespace App\Http\Controllers;

use App\Http\Repository\DoctoresRepository;
use App\Http\Requests\StoreDoctorRequest;
use Illuminate\Http\Request;

class DoctoresController extends Controller
{
    protected $doctoresRepository;

    public function __construct(DoctoresRepository $doctoresRepository)
    {
        $this->doctoresRepository = $doctoresRepository;
    }

    public function obtenerDoctores(Request $request)
    {
        try {
            $resultado = $this->doctoresRepository->obtenerDoctores($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarDoctor(StoreDoctorRequest $request)
    {
        try {
            $resultado = $this->doctoresRepository->registrarDoctor($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function obtenerDoctor(int $id)
    {
        try {
            $resultado = $this->doctoresRepository->obtenerDoctor($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function actualizarDoctor(Request $request, int $id)
    {
        try {
            $resultado = $this->doctoresRepository->actualizarDoctor($id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function validarDoctor(Request $request, int $id)
    {
        try {
            $adminId   = $request->user()->id;
            $resultado = $this->doctoresRepository->validarDoctor($id, $request->all(), $adminId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
