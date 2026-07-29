<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\UsuariosRepository;
use Illuminate\Http\Request;

class PerfilWebController extends Controller
{
    protected $usuariosRepository;

    public function __construct(UsuariosRepository $usuariosRepository)
    {
        $this->usuariosRepository = $usuariosRepository;
    }

    public function index(Request $request)
    {
        $usuario = $request->user();
        return view('perfil.index', compact('usuario'));
    }

    public function update(Request $request)
    {
        try {
            $this->usuariosRepository->actualizarPerfil($request->user()->id, $request->all());
            return back()->with('success', 'Perfil actualizado con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->usuariosRepository->cambiarPassword($request->user()->id, $request->all());
            return back()->with('success', 'Contraseña modificada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function actualizarFoto(Request $request)
    {
        $request->validate(['foto' => 'required|image|max:2048']);
        try {
            $ruta = $request->file('foto')->store('fotos_perfil', 'public');
            $this->usuariosRepository->actualizarFoto($request->user()->id, $ruta);
            return back()->with('success', 'Foto de perfil actualizada.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
