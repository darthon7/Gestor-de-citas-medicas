<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\DoctoresRepository;
use App\Http\Repository\EspecialidadesRepository;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardWebController extends Controller
{
    protected $doctoresRepository;
    protected $especialidadesRepository;

    public function __construct(
        DoctoresRepository $doctoresRepository,
        EspecialidadesRepository $especialidadesRepository
    ) {
        $this->doctoresRepository = $doctoresRepository;
        $this->especialidadesRepository = $especialidadesRepository;
    }

    public function index()
    {
        $user = auth()->user();
        $hoy = Carbon::today()->format('Y-m-d');

        $baseHoy = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad'])
            ->whereDate('fecha_cita', $hoy);

        if ($user->rol === 'paciente') {
            $pacienteId = $user->perfilPaciente?->id;
            $baseHoy->where('perfil_paciente_id', $pacienteId);
        } elseif ($user->rol === 'doctor') {
            $doctorId = $user->perfilDoctor?->id;
            $baseHoy->where('perfil_doctor_id', $doctorId);
        }

        $citasHoy = $baseHoy->clone()->orderBy('hora_cita', 'asc')->paginate(8)->withQueryString();

        $stats = $baseHoy->get();
        $statTotalDia = $stats->count();
        $statCompletadas = $stats->where('estado', 'completada')->count();
        $statPendientes = $stats->whereIn('estado', ['agendada', 'confirmada'])->count();
        $statCanceladas = $stats->where('estado', 'cancelada')->count();

        $resDoctores = $this->doctoresRepository->obtenerDoctores();
        $doctores = $resDoctores['data'] ?? [];

        $resEsp = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $resEsp['data'] ?? [];

        $pacientes = \App\Models\PerfilPaciente::with('usuario')->get();

        return view('dashboard.index', compact(
            'citasHoy',
            'statTotalDia',
            'statCompletadas',
            'statPendientes',
            'statCanceladas',
            'doctores',
            'especialidades',
            'pacientes'
        ));
    }
}
