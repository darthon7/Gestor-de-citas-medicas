<?php

namespace App\Http\Controllers;

use App\Http\Repository\NotasConsultaRepository;
use App\Http\Requests\StoreNotaConsultaRequest;

class NotasConsultaController extends Controller
{
    protected $notasRepository;

    public function __construct(NotasConsultaRepository $notasRepository)
    {
        $this->notasRepository = $notasRepository;
    }

    public function registrarNota(StoreNotaConsultaRequest $request, int $citaId)
    {
        try {
            $doctorUsuarioId = $request->user()->id;
            $resultado       = $this->notasRepository->registrarNota($citaId, $request->all(), $doctorUsuarioId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function obtenerNotas(int $citaId)
    {
        try {
            $resultado = $this->notasRepository->obtenerNotas($citaId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
