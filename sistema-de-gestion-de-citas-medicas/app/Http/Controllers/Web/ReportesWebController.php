<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\DoctoresRepository;
use App\Http\Repository\EspecialidadesRepository;
use App\Http\Repository\ReportesRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportesWebController extends Controller
{
    protected $reportesRepository;
    protected $doctoresRepository;
    protected $especialidadesRepository;

    public function __construct(
        ReportesRepository $reportesRepository,
        DoctoresRepository $doctoresRepository,
        EspecialidadesRepository $especialidadesRepository
    ) {
        $this->reportesRepository = $reportesRepository;
        $this->doctoresRepository = $doctoresRepository;
        $this->especialidadesRepository = $especialidadesRepository;
    }

    public function index(Request $request)
    {
        $fechaInicio = $request->query('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));
        $doctorId = $request->query('doctor_id');
        $especialidadId = $request->query('especialidad_id');

        $filtros = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ];
        if ($doctorId) $filtros['doctor_id'] = $doctorId;
        if ($especialidadId) $filtros['especialidad_id'] = $especialidadId;

        $reporteCitas = $this->reportesRepository->reporteCitas($filtros);
        $citasData = $reporteCitas['data'] ?? [];

        $resDocs = $this->doctoresRepository->obtenerDoctores();
        $doctores = $resDocs['data'] ?? [];

        $resEsps = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $resEsps['data'] ?? [];

        // Calcular estadísticas
        $totalAgendadas = count($citasData);
        $totalCompletadas = count(array_filter($citasData, fn($c) => strtolower($c['estado']) === 'completada'));
        $totalCanceladas = count(array_filter($citasData, fn($c) => strtolower($c['estado']) === 'cancelada'));
        $tasaAsistencia = $totalAgendadas > 0 ? round(($totalCompletadas / $totalAgendadas) * 100, 1) : 0;

        return view('reportes.index', compact(
            'citasData',
            'doctores',
            'especialidades',
            'fechaInicio',
            'fechaFin',
            'doctorId',
            'especialidadId',
            'totalAgendadas',
            'totalCompletadas',
            'totalCanceladas',
            'tasaAsistencia'
        ));
    }

    public function exportar(Request $request, string $tipo)
    {
        $filtros = $request->all();
        $datos = $this->reportesRepository->reporteCitas($filtros);
        $citas = $datos['data'] ?? [];

        if ($request->query('formato') === 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf', ['citas' => $citas, 'tipo' => $tipo]);
            return $pdf->download('reporte-' . $tipo . '-' . now()->format('Ymd') . '.pdf');
        }

        return redirect()->route('reportes.index')->with('warning', 'Formato no soportado.');
    }
}
