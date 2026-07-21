<?php

namespace App\Http\Controllers;

use App\Http\Repository\AuthRepository;
use App\Http\Requests\StoreLoginRequest;
use App\Http\Requests\StoreRegistroPacienteRequest;
use App\Http\Requests\StoreRegistroMedicoRequest;
use App\Http\Requests\StoreRegistroRecepcionistaRequest;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login(StoreLoginRequest $request)
    {
        try {
            $resultado = $this->authRepository->login($request->all(), $request->ip());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarPaciente(StoreRegistroPacienteRequest $request)
    {
        try {
            $resultado = $this->authRepository->registrarPaciente($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarMedico(StoreRegistroMedicoRequest $request)
    {
        try {
            $resultado = $this->authRepository->registrarMedico($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarRecepcionista(StoreRegistroRecepcionistaRequest $request)
    {
        try {
            $adminId   = $request->user()->id;
            $resultado = $this->authRepository->registrarRecepcionista($request->all(), $adminId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function cerrarSesion(\Illuminate\Http\Request $request)
    {
        try {
            $resultado = $this->authRepository->cerrarSesion($request->user());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
