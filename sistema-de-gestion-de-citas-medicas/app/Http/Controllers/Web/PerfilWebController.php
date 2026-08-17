<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\UsuariosRepository;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

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
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ], [
            'foto.required' => 'Debes seleccionar una imagen.',
            'foto.image'    => 'El archivo seleccionado debe ser una imagen.',
            'foto.mimes'    => 'Formato no compatible. Usa JPG, PNG, WEBP o GIF.',
            'foto.max'      => 'La imagen no debe superar los 10 MB.',
            'foto.uploaded' => 'No se pudo subir la imagen. Verifica que el archivo no supere el tamaño permitido.',
        ]);

        try {
            $user = $request->user();
            if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            $ruta = $request->file('foto')->store('fotos_perfil', 'public');
            $this->usuariosRepository->actualizarFoto($user->id, $ruta);
            return back()->with('success', 'Foto de perfil actualizada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
