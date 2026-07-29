<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\CitasRepository;
use App\Http\Repository\DoctoresRepository;
use App\Http\Repository\EspecialidadesRepository;
use App\Http\Requests\CancelacionCitaRequest;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UpdateCitaRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CitasWebController extends Controller
{
    protected $citasRepository;
    protected $doctoresRepository;
    protected $especialidadesRepository;

    public function __construct(
        CitasRepository $citasRepository,
        DoctoresRepository $doctoresRepository,
        EspecialidadesRepository $especialidadesRepository
    ) {
        $this->citasRepository = $citasRepository;
        $this->doctoresRepository = $doctoresRepository;
        $this->especialidadesRepository = $especialidadesRepository;
    }

    public function index(Request $request)
    {
        $fechaRef = $request->query('fecha') ? Carbon::parse($request->query('fecha')) : Carbon::now();
        $doctorId = $request->query('doctor_id');

        // Calcular lunes y domingo de la semana activa
        $startOfWeek = $fechaRef->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $fechaRef->copy()->endOfWeek(Carbon::SUNDAY);

        $params = [
            'fecha_inicio' => $startOfWeek->format('Y-m-d'),
            'fecha_fin' => $endOfWeek->format('Y-m-d'),
        ];
        if ($doctorId) {
            $params['doctor_id'] = $doctorId;
        }

        $resCitas = $this->citasRepository->obtenerCitas($params);
        $citas = $resCitas['data'] ?? [];

        $resDoctores = $this->doctoresRepository->obtenerDoctores();
        $doctores = $resDoctores['data'] ?? [];

        return view('citas.index', compact(
            'citas',
            'doctores',
            'startOfWeek',
            'endOfWeek',
            'fechaRef',
            'doctorId'
        ));
    }

    public function crear()
    {
        $resDoctores = $this->doctoresRepository->obtenerDoctores();
        $doctores = $resDoctores['data'] ?? [];

        $resEsp = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $resEsp['data'] ?? [];

        $pacientes = \App\Models\PerfilPaciente::with('usuario')->get();

        return view('citas.agendar', compact('doctores', 'especialidades', 'pacientes'));
    }

    public function store(StoreCitaRequest $request)
    {
        try {
            $this->citasRepository->registrarCita($request->all());
            return redirect()->route('citas.index')->with('success', 'Cita registrada con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(int $id)
    {
        try {
            $res = $this->citasRepository->obtenerCita($id);
            $cita = $res['data'] ?? null;
            if (!$cita) {
                return redirect()->route('citas.index')->with('error', 'Cita no encontrada.');
            }
            return view('citas.detalle', compact('cita'));
        } catch (\Exception $e) {
            return redirect()->route('citas.index')->with('error', $e->getMessage());
        }
    }

    public function reprogramar(UpdateCitaRequest $request, int $id)
    {
        try {
            $this->citasRepository->reprogramarCita($id, $request->all());
            return back()->with('success', 'Cita reprogramada correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function cancelar(CancelacionCitaRequest $request, int $id)
    {
        try {
            $this->citasRepository->cancelarCita($id, $request->all(), $request->user()->id);
            return back()->with('success', 'Cita cancelada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function checkIn(Request $request, int $id)
    {
        try {
            $this->citasRepository->checkInCita($id, $request->user()->id);
            return back()->with('success', 'Check-in registrado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
