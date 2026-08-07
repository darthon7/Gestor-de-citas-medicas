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
        $user = $request->user();
        $fechaRef = $request->query('fecha') ? Carbon::parse($request->query('fecha')) : Carbon::now();
        $doctorId = $request->query('doctor_id');

        // Modo de vista: dia | semana | mes
        $vista = in_array($request->query('vista'), ['dia', 'semana', 'mes'], true)
            ? $request->query('vista')
            : 'semana';

        // Calcular rango segun el modo activo
        $startOfWeek = $fechaRef->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $fechaRef->copy()->endOfWeek(Carbon::SUNDAY);

        switch ($vista) {
            case 'dia':
                $fechaInicio = $fechaRef->format('Y-m-d');
                $fechaFin    = $fechaRef->format('Y-m-d');
                break;
            case 'mes':
                $fechaInicio = $fechaRef->copy()->startOfMonth()->format('Y-m-d');
                $fechaFin    = $fechaRef->copy()->endOfMonth()->format('Y-m-d');
                break;
            default:
                $fechaInicio = $startOfWeek->format('Y-m-d');
                $fechaFin    = $endOfWeek->format('Y-m-d');
        }

        $params = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ];

        if ($user->rol === 'paciente') {
            $params['paciente_id'] = $user->perfilPaciente?->id;
        } elseif ($user->rol === 'doctor') {
            $params['doctor_id'] = $user->perfilDoctor?->id;
        } elseif ($doctorId) {
            $params['doctor_id'] = $doctorId;
        }

        $resCitas = $this->citasRepository->obtenerCitas($params);
        $citas = isset($resCitas['data']) ? collect($resCitas['data']->items()) : collect();

        $resDoctores = $this->doctoresRepository->obtenerDoctores();
        $doctores = $resDoctores['data'] ?? [];

        $resEsp = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $resEsp['data'] ?? [];

        $pacientes = $user->rol === 'paciente'
            ? \App\Models\PerfilPaciente::with('usuario')->where('id', $user->perfilPaciente?->id)->get()
            : \App\Models\PerfilPaciente::with('usuario')->get();

        return view('citas.index', compact(
            'citas',
            'doctores',
            'especialidades',
            'pacientes',
            'startOfWeek',
            'endOfWeek',
            'fechaRef',
            'doctorId',
            'vista',
            'fechaInicio',
            'fechaFin'
        ));
    }

    public function crear(Request $request)
    {
        $user = $request->user();
        $resDoctores = $this->doctoresRepository->obtenerDoctores();
        $doctores = $resDoctores['data'] ?? [];

        $resEsp = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $resEsp['data'] ?? [];

        if ($user->rol === 'paciente') {
            $pacientes = \App\Models\PerfilPaciente::with('usuario')
                ->where('id', $user->perfilPaciente?->id)
                ->get();
        } else {
            $pacientes = \App\Models\PerfilPaciente::with('usuario')->get();
        }

        return view('citas.agendar', compact('doctores', 'especialidades', 'pacientes'));
    }

    public function store(StoreCitaRequest $request)
    {
        try {
            $user = $request->user();
            $data = $request->all();

            if ($user->rol === 'paciente') {
                $data['perfil_paciente_id'] = $user->perfilPaciente?->id;
            }

            if (isset($data['fecha']) && !isset($data['fecha_cita'])) {
                $data['fecha_cita'] = $data['fecha'];
            }
            if (isset($data['hora']) && !isset($data['hora_cita'])) {
                $data['hora_cita'] = $data['hora'];
            }

            $res = $this->citasRepository->registrarCita($data);

            if (!isset($res['data'])) {
                return back()->withInput()->with('error', $res['mensaje'] ?? 'No fue posible registrar la cita.');
            }

            return redirect()->route('citas.index')->with('success', $res['mensaje'] ?? 'Cita registrada con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $user = auth()->user();
            $res = $this->citasRepository->obtenerCita($id);
            $cita = $res['data'] ?? null;
            if (!$cita) {
                return redirect()->route('citas.index')->with('error', 'Cita no encontrada.');
            }

            if ($user->rol === 'paciente' && $cita->perfil_paciente_id !== $user->perfilPaciente?->id) {
                abort(403, 'No tienes permisos para consultar esta cita.');
            }
            if ($user->rol === 'doctor' && $cita->perfil_doctor_id !== $user->perfilDoctor?->id) {
                abort(403, 'No tienes permisos para consultar esta cita.');
            }

            return view('citas.detalle', compact('cita'));
        } catch (\Exception $e) {
            return redirect()->route('citas.index')->with('error', $e->getMessage());
        }
    }

    public function reprogramar(UpdateCitaRequest $request, $id)
    {
        try {
            $this->citasRepository->reprogramarCita($id, $request->all());
            return back()->with('success', 'Cita reprogramada correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function cancelar(CancelacionCitaRequest $request, $id)
    {
        try {
            $user = $request->user();
            if ($user->rol === 'paciente') {
                $pacienteId = $user->perfilPaciente?->id;
                $res = $this->citasRepository->cancelarCitaPaciente($id, $request->all(), $pacienteId, $user->id);
            } else {
                $res = $this->citasRepository->cancelarCita($id, $request->all(), $user->id);
            }

            if (isset($res['mensaje']) && str_contains(strtolower($res['mensaje']), 'error')) {
                return back()->with('error', $res['mensaje']);
            }

            return back()->with('success', $res['mensaje'] ?? 'Cita cancelada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function checkIn(Request $request, $id)
    {
        try {
            $this->citasRepository->checkInCita($id, $request->user()->id);
            return back()->with('success', 'Check-in registrado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
