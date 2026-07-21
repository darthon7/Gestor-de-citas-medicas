<?php

namespace App\Http\Controllers;

use App\Http\Repository\UsuariosRepository;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    protected $usuariosRepository;

    public function __construct(UsuariosRepository $usuariosRepository)
    {
        $this->usuariosRepository = $usuariosRepository;
    }

    public function miPerfil(Request $request)
    {
        try {
            $resultado = $this->usuariosRepository->obtenerPerfil($request->user()->id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function actualizarMiPerfil(Request $request)
    {
        try {
            $resultado = $this->usuariosRepository->actualizarPerfil($request->user()->id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function cambiarPassword(Request $request)
    {
        try {
            $request->validate([
                'password_actual'      => 'required|string',
                'password'             => 'required|string|min:8|confirmed',
            ]);
            $resultado = $this->usuariosRepository->cambiarPassword($request->user()->id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function actualizarFoto(Request $request)
    {
        try {
            $request->validate(['foto' => 'required|image|max:2048']);
            $ruta      = $request->file('foto')->store('fotos_perfil', 'public');
            $resultado = $this->usuariosRepository->actualizarFoto($request->user()->id, $ruta);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function miHistorial(Request $request)
    {
        try {
            $usuario = $request->user()->load([
                'perfilPaciente.citas' => function ($q) {
                    $q->where('estado', 'completada')
                        ->with(['notaConsulta', 'perfilDoctor.usuario', 'especialidad'])
                        ->orderBy('fecha_cita', 'desc');
                },
            ]);
            return response()->json([
                'mensaje' => 'Historial médico obtenido correctamente',
                'data'    => $usuario->perfilPaciente?->citas ?? [],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function miConsulta(int $id)
    {
        try {
            $nota = \App\Models\NotaConsulta::with([
                'cita.perfilDoctor.usuario',
                'cita.especialidad',
            ])->whereHas('cita', function ($q) use ($id) {
                $q->where('id', $id)->where('estado', 'completada');
            })->first();

            if (!$nota) {
                return response()->json(['mensaje' => 'Consulta no encontrada'], 200);
            }

            return response()->json([
                'mensaje' => 'Detalle de consulta obtenido',
                'data'    => $nota,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
