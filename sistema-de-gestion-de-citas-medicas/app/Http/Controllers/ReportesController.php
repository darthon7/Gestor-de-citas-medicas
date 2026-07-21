<?php

namespace App\Http\Controllers;

use App\Http\Repository\ReportesRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportesController extends Controller
{
    protected $reportesRepository;

    public function __construct(ReportesRepository $reportesRepository)
    {
        $this->reportesRepository = $reportesRepository;
    }

    public function reporteCitas(Request $request)
    {
        try {
            $resultado = $this->reportesRepository->reporteCitas($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function reporteDoctores(Request $request)
    {
        try {
            $resultado = $this->reportesRepository->reporteDoctores($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function reporteEspecialidades(Request $request)
    {
        try {
            $resultado = $this->reportesRepository->reporteEspecialidades($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function reportePacientes(Request $request)
    {
        try {
            $resultado = $this->reportesRepository->reportePacientes($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function resumenDiario(Request $request)
    {
        try {
            $fecha     = $request->query('fecha', now()->format('Y-m-d'));
            $resultado = $this->reportesRepository->resumenDiario($fecha);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function exportarReporte(Request $request, string $tipo)
    {
        try {
            $filtros = $request->all();

            switch ($tipo) {
                case 'citas':
                    $datos = $this->reportesRepository->reporteCitas($filtros);
                    break;
                case 'doctores':
                    $datos = $this->reportesRepository->reporteDoctores($filtros);
                    break;
                case 'especialidades':
                    $datos = $this->reportesRepository->reporteEspecialidades($filtros);
                    break;
                case 'pacientes':
                    $datos = $this->reportesRepository->reportePacientes($filtros);
                    break;
                default:
                    return response()->json(['mensaje' => 'Tipo de reporte no válido'], 422);
            }

            $formato = $request->query('formato', 'pdf');

            if ($formato === 'pdf') {
                $pdf = Pdf::loadView('reportes.' . $tipo, ['datos' => $datos['data']]);
                return $pdf->download('reporte-' . $tipo . '-' . now()->format('Ymd') . '.pdf');
            }

            // Excel: retornar datos como JSON por ahora (extensión real requiere clases Export)
            return response()->json([
                'mensaje' => 'Exportación Excel: datos listos',
                'data'    => $datos['data'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
