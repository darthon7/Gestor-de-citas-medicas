<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\PacientesRepository;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use Illuminate\Http\Request;

class PacientesWebController extends Controller
{
    protected $pacientesRepository;

    public function __construct(PacientesRepository $pacientesRepository)
    {
        $this->pacientesRepository = $pacientesRepository;
    }

    public function index(Request $request)
    {
        $query = $request->query('buscar');
        $pacientes = \App\Models\PerfilPaciente::with('usuario')
            ->when($query, function ($q) use ($query) {
                $q->whereHas('usuario', function ($u) use ($query) {
                    $u->where('nombre', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('curp', 'like', "%{$query}%")
                        ->orWhere('telefono', 'like', "%{$query}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pacientes.index', compact('pacientes', 'query'));
    }

    public function store(StorePacienteRequest $request)
    {
        try {
            $this->pacientesRepository->registrarPaciente($request->all());
            return redirect()->route('pacientes.index')->with('success', 'Paciente registrado correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $paciente = \App\Models\PerfilPaciente::with(['usuario', 'citas.perfilDoctor.usuario', 'citas.especialidad'])->findOrFail($id);
            return view('pacientes.perfil', compact('paciente'));
        } catch (\Exception $e) {
            return redirect()->route('pacientes.index')->with('error', 'Paciente no encontrado.');
        }
    }

    public function update(UpdatePacienteRequest $request, int $id)
    {
        try {
            $this->pacientesRepository->actualizarPaciente($id, $request->all());
            return redirect()->route('pacientes.index')->with('success', 'Paciente actualizado con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function desactivar($id)
    {
        try {
            $this->pacientesRepository->desactivarPaciente($id);
            return redirect()->route('pacientes.index')->with('success', 'Paciente desactivado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
