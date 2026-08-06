<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\BloqueosRepository;
use App\Http\Repository\DoctoresRepository;
use App\Http\Repository\EspecialidadesRepository;
use App\Http\Repository\HorariosRepository;
use App\Http\Requests\StoreBloqueoRequest;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\StoreHorarioRequest;
use Illuminate\Http\Request;

class DoctoresWebController extends Controller
{
    protected $doctoresRepository;
    protected $especialidadesRepository;
    protected $horariosRepository;
    protected $bloqueosRepository;

    public function __construct(
        DoctoresRepository $doctoresRepository,
        EspecialidadesRepository $especialidadesRepository,
        HorariosRepository $horariosRepository,
        BloqueosRepository $bloqueosRepository
    ) {
        $this->doctoresRepository = $doctoresRepository;
        $this->especialidadesRepository = $especialidadesRepository;
        $this->horariosRepository = $horariosRepository;
        $this->bloqueosRepository = $bloqueosRepository;
    }

    public function index(Request $request)
    {
        $resDoctores = $this->doctoresRepository->obtenerDoctores([
            'buscar'            => $request->query('buscar'),
            'especialidad_id'   => $request->query('especialidad_id'),
            'estado_validacion' => $request->query('estado_validacion') ?: null,
        ]);
        $doctores = isset($resDoctores['data']) ? collect($resDoctores['data']->items()) : collect();

        $resEsp = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $resEsp['data'] ?? [];

        return view('doctores.index', compact('doctores', 'especialidades'));
    }

    public function store(StoreDoctorRequest $request)
    {
        try {
            $this->doctoresRepository->registrarDoctor($request->all());
            return redirect()->route('doctores.index')->with('success', 'Médico registrado con éxito. Pendiente de validación.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->doctoresRepository->actualizarDoctor($id, $request->all());
            return redirect()->route('doctores.index')->with('success', 'Médico actualizado con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function validar(Request $request, $id)
    {
        try {
            $this->doctoresRepository->validarDoctor($id, $request->all(), $request->user()->id);
            return redirect()->route('doctores.index')->with('success', 'Estado de validación del médico actualizado.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function horarios($doctorId)
    {
        try {
            $doctorRes = $this->doctoresRepository->obtenerDoctor($doctorId);
            $doctor = $doctorRes['data'] ?? null;

            $horariosRes = $this->horariosRepository->obtenerHorarios($doctorId);
            $horarios = $horariosRes['data'] ?? [];

            $bloqueosRes = $this->bloqueosRepository->obtenerBloqueos($doctorId);
            $bloqueos = $bloqueosRes['data'] ?? [];

            return view('doctores.horarios', compact('doctor', 'horarios', 'bloqueos', 'doctorId'));
        } catch (\Exception $e) {
            return redirect()->route('doctores.index')->with('error', $e->getMessage());
        }
    }

    public function storeHorario(StoreHorarioRequest $request, $doctorId)
    {
        try {
            $this->horariosRepository->registrarHorario($doctorId, $request->all());
            return back()->with('success', 'Horario registrado correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateHorario(Request $request, $id)
    {
        try {
            $this->horariosRepository->actualizarHorario($id, $request->all());
            return back()->with('success', 'Horario actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteHorario($id)
    {
        try {
            $this->horariosRepository->eliminarHorario($id);
            return back()->with('success', 'Horario eliminado.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeBloqueo(StoreBloqueoRequest $request, $doctorId)
    {
        try {
            $this->bloqueosRepository->registrarBloqueo($doctorId, $request->all(), $request->user()->id);
            return back()->with('success', 'Bloqueo de horario registrado.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteBloqueo($id)
    {
        try {
            $this->bloqueosRepository->eliminarBloqueo($id);
            return back()->with('success', 'Bloqueo eliminado.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
