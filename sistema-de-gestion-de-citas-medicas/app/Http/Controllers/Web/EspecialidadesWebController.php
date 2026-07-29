<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\EspecialidadesRepository;
use Illuminate\Http\Request;

class EspecialidadesWebController extends Controller
{
    protected $especialidadesRepository;

    public function __construct(EspecialidadesRepository $especialidadesRepository)
    {
        $this->especialidadesRepository = $especialidadesRepository;
    }

    public function index()
    {
        $res = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $res['data'] ?? [];

        return view('especialidades.index', compact('especialidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:especialidades,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        try {
            $this->especialidadesRepository->registrarEspecialidad($request->all());
            return redirect()->route('especialidades.index')->with('success', 'Especialidad creada con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
