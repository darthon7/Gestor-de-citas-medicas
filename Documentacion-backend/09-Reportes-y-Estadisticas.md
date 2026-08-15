# 📊 Módulo 9: Reportes y Estadísticas

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura del Módulo](#2-diagrama-de-arquitectura-del-módulo)
3. [Modelo de Datos Relacional y Agregaciones SQL](#3-modelo-de-datos-relacional-y-agregaciones-sql)
4. [Capa de Repositorios (Lógica de Negocio y Métricas Analíticas)](#4-capa-de-repositorios-lógica-de-negocio-y-métricas-analíticas)
5. [Capa de Controladores (API REST vs Blade SSR)](#5-capa-de-controladores-api-rest-vs-blade-ssr)
6. [Capa de Vistas y Tableros Gráficos (Blade SSR UI y Chart.js)](#6-capa-de-vistas-y-tableros-gráficos-blade-ssr-ui-y-chartjs)
7. [Motor de Exportación de Documentos (PDF / Excel)](#7-motor-de-exportación-de-documentos-pdf--excel)
8. [Rutas (API y Web)](#8-rutas-api-y-web)
9. [Flujos Completos de Operación](#9-flujos-completos-de-operación)
10. [Relación con Otros Módulos](#10-relación-con-otros-módulos)
11. [Mapa de Archivos del Módulo](#11-mapa-de-archivos-del-módulo)

---

## 1. Visión General del Módulo

El módulo de **Reportes y Estadísticas** es el motor de Business Intelligence (BI) y analítica del sistema. Su propósito principal es consolidar, procesar y visualizar los indicadores clave de rendimiento (KPIs) operacionales de la clínica, tales como volumen de consultas, tasa de ausentismo/asistencia, demanda por especialidad, productividad médica y exportación de reportes ejecutivos en formato PDF.

### Responsabilidades Principales

| Responsabilidad | Descripción Técnica |
|---|---|
| **Consolidación de Métricas (KPIs)** | Calcular en tiempo real totales de citas agendadas, completadas, canceladas y porcentaje de asistencia. |
| **Consultas Agregadas Eficientes** | Utilizar técnicas de clonación de Query Builder (`clone $query`) y `withCount` de Eloquent para minimizar el overhead en BD. |
| **Tablero de Control Gráfico** | Renderizar gráficos dinámicos e interactivos (Donut Chart para estados y Bar Chart para especialidades) mediante Chart.js. |
| **Generación de Reportes PDF** | Compilar vistas HTML/Blade a documentos PDF listos para descarga mediante la librería `barryvdh/laravel-dompdf`. |
| **Filtros Multidimensionales** | Permitir la segmentación de datos por rango de fechas (`fecha_inicio` / `fecha_fin`), médico específico y especialidad. |

### Roles que Interactúan con este Módulo

| Rol | Permisos y Operaciones |
|---|---|
| **Administrador** | Acceso completo al tablero de reportes globales, métricas de productividad médica y exportación de PDFs. |
| **Recepcionista** | Acceso a resúmenes diarios operativos para verificar asistencia del día. |

---

## 2. Diagrama de Arquitectura del Módulo

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PETICIÓN HTTP                                       │
│    API REST (/api/reportes/citas, /resumenDiario)  │   Web SSR (/reportes, /exportar)      │
└───────────────────────────┬───────────────────────────────┬────────────────────────────┘
                            │                               │
                            ▼                               ▼
               ┌──────────────────────────┐    ┌──────────────────────────────┐
               │    ReportesController    │    │    ReportesWebController     │
               │        (API JSON)        │    │     (Blade SSR + Session)    │
               └────────────┬─────────────┘    └──────────────┬───────────────┘
                            │                                 │
                            └───────────────┬─────────────────┘
                                            │
                                            ▼
                            ┌────────────────────────────────┐
                            │       ReportesRepository       │
                            │  • reporteCitas()              │
                            │  • reporteDoctores()           │
                            │  • reporteEspecialidades()     │
                            │  • reportePacientes()          │
                            │  • resumenDiario()             │
                            └───────────────┬────────────────┘
                                            │
                      ┌─────────────────────┼─────────────────────┐
                      ▼                     ▼                     ▼
        ┌──────────────────────────┐ ┌──────────────┐ ┌───────────────────────┐
        │  Consultas Agregadas BD  │ │ DomPDF Facade│ │   Chart.js JS Engine  │
        │(withCount / clone query) │ │ (Genera PDF) │ │(Gráficos Interactivos)│
        └─────────────┬────────────┘ └──────┬───────┘ └───────────┬───────────┘
                      │                     │                     │
                      ▼                     ▼                     ▼
        ┌──────────────────────────┐ ┌──────────────┐ ┌───────────────────────┐
        │     Base de Datos        │ │ Documento PDF│ │ Vista Blade Rendered  │
        └──────────────────────────┘ └──────────────┘ └───────────────────────┘
```

---

## 3. Modelo de Datos Relacional y Agregaciones SQL

El módulo de reportes no posee migraciones propias; en su lugar, ejecuta consultas avanzadas de agregación sobre las tablas existentes: `citas`, `perfiles_doctor`, `especialidades`, `perfiles_paciente` y `usuarios`.

```
┌─────────────────────────┐             ┌─────────────────────────┐             ┌─────────────────────────┐
│          citas          │             │     perfiles_doctor     │             │     especialidades      │
│─────────────────────────│             │─────────────────────────│             │─────────────────────────│
│ count() por estado      │────────────►│ withCount('citas')      │────────────►│ withCount('citas')      │
│ WHERE fecha_cita        │             │ (Consultas completadas) │             │ (Demanda por área)      │
└─────────────────────────┘             └─────────────────────────┘             └─────────────────────────┘
```

---

## 4. Capa de Repositorios (Lógica de Negocio y Métricas Analíticas)

**Archivo:** `app/Http/Repository/ReportesRepository.php`

```php
<?php

namespace App\Http\Repository;

use App\Models\Cita;
use App\Models\PerfilDoctor;
use App\Models\Especialidad;
use App\Models\Usuario;
use Exception;

class ReportesRepository
{
    /**
     * Reporte general de citas con técnica de clonación de queries para agregación óptima.
     */
    public function reporteCitas(array $filtros = [])
    {
        try {
            $query = Cita::query();

            if (!empty($filtros['fecha_inicio'])) {
                $query->where('fecha_cita', '>=', $filtros['fecha_inicio']);
            }
            if (!empty($filtros['fecha_fin'])) {
                $query->where('fecha_cita', '<=', $filtros['fecha_fin']);
            }
            if (!empty($filtros['doctor_id'])) {
                $query->where('perfil_doctor_id', $filtros['doctor_id']);
            }
            if (!empty($filtros['especialidad_id'])) {
                $query->where('especialidad_id', $filtros['especialidad_id']);
            }

            // Técnica de clonación para evitar re-ejecutar subconsultas compuestas
            $total       = $query->count();
            $agendadas   = (clone $query)->where('estado', 'agendada')->count();
            $confirmadas = (clone $query)->where('estado', 'confirmada')->count();
            $completadas = (clone $query)->where('estado', 'completada')->count();
            $canceladas  = (clone $query)->where('estado', 'cancelada')->count();

            return [
                'mensaje' => 'Reporte de citas generado',
                'data'    => [
                    'total'       => $total,
                    'agendadas'   => $agendadas,
                    'confirmadas' => $confirmadas,
                    'completadas' => $completadas,
                    'canceladas'  => $canceladas,
                    'citas'       => $query->with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad'])
                        ->orderBy('fecha_cita')
                        ->get(),
                ],
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Productividad por médico utilizando subconsultas withCount.
     */
    public function reporteDoctores(array $filtros = [])
    {
        try {
            $query = PerfilDoctor::withCount([
                'citas as total_consultas' => function ($q) use ($filtros) {
                    $q->where('estado', 'completada');
                    if (!empty($filtros['fecha_inicio'])) {
                        $q->where('fecha_cita', '>=', $filtros['fecha_inicio']);
                    }
                    if (!empty($filtros['fecha_fin'])) {
                        $q->where('fecha_cita', '<=', $filtros['fecha_fin']);
                    }
                },
            ])->with(['usuario', 'especialidades'])
                ->orderBy('total_consultas', 'desc');

            return [
                'mensaje' => 'Reporte de doctores generado',
                'data'    => $query->get(),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Demanda por especialidad médica.
     */
    public function reporteEspecialidades(array $filtros = [])
    {
        try {
            $especialidades = Especialidad::withCount([
                'citas as total_citas' => function ($q) use ($filtros) {
                    if (!empty($filtros['fecha_inicio'])) {
                        $q->where('fecha_cita', '>=', $filtros['fecha_inicio']);
                    }
                    if (!empty($filtros['fecha_fin'])) {
                        $q->where('fecha_cita', '<=', $filtros['fecha_fin']);
                    }
                },
            ])->orderBy('total_citas', 'desc')->get();

            return [
                'mensaje' => 'Reporte de especialidades generado',
                'data'    => $especialidades,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Resumen del día operativo para la recepción.
     */
    public function resumenDiario(string $fecha)
    {
        try {
            $citas = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad'])
                ->where('fecha_cita', $fecha)
                ->orderBy('hora_cita')
                ->get();

            $resumen = [
                'fecha'       => $fecha,
                'total'       => $citas->count(),
                'agendadas'   => $citas->where('estado', 'agendada')->count(),
                'confirmadas' => $citas->where('estado', 'confirmada')->count(),
                'en_consulta' => $citas->where('estado', 'en_consulta')->count(),
                'completadas' => $citas->where('estado', 'completada')->count(),
                'canceladas'  => $citas->where('estado', 'cancelada')->count(),
                'citas'       => $citas,
            ];

            return [
                'mensaje' => 'Resumen diario generado',
                'data'    => $resumen,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
```

---

## 5. Capa de Controladores (API REST vs Blade SSR)

### 5.1 Controlador API y PDF (`ReportesController`)

**Archivo:** `app/Http/Controllers/ReportesController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Repository\ReportesRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

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

    public function exportarReporte(Request $request, string $tipo)
    {
        try {
            $filtros = $request->all();
            $datos   = $this->reportesRepository->reporteCitas($filtros);
            $formato = $request->query('formato', 'pdf');

            if ($formato === 'pdf') {
                $pdf = Pdf::loadView('reportes.pdf', ['citas' => $datos['data']['citas'] ?? [], 'tipo' => $tipo]);
                return $pdf->download('reporte-' . $tipo . '-' . now()->format('Ymd') . '.pdf');
            }

            return response()->json(['mensaje' => 'Exportación Excel: datos listos', 'data' => $datos['data']], 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
```

---

### 5.2 Controlador Web (`ReportesWebController`)

**Archivo:** `app/Http/Controllers/Web/ReportesWebController.php`

Calcula los indicadores KPI en el servidor y alimenta el tablero Blade.

```php
public function index(Request $request)
{
    $fechaInicio    = $request->query('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
    $fechaFin       = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));
    $doctorId       = $request->query('doctor_id');
    $especialidadId = $request->query('especialidad_id');

    $filtros = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin];
    if ($doctorId)       $filtros['doctor_id'] = $doctorId;
    if ($especialidadId) $filtros['especialidad_id'] = $especialidadId;

    $reporteCitas = $this->reportesRepository->reporteCitas($filtros);
    $citasData    = $reporteCitas['data']['citas'] ?? [];

    // Cálculo de Indicadores Clave de Rendimiento (KPIs)
    $totalAgendadas   = count($citasData);
    $totalCompletadas = count(array_filter($citasData, fn($c) => strtolower($c['estado']) === 'completada'));
    $totalCanceladas  = count(array_filter($citasData, fn($c) => strtolower($c['estado']) === 'cancelada'));
    $tasaAsistencia   = $totalAgendadas > 0 ? round(($totalCompletadas / $totalAgendadas) * 100, 1) : 0;

    return view('reportes.index', compact(
        'citasData', 'doctores', 'especialidades', 'fechaInicio', 'fechaFin',
        'doctorId', 'especialidadId', 'totalAgendadas', 'totalCompletadas',
        'totalCanceladas', 'tasaAsistencia'
    ));
}
```

---

## 6. Capa de Vistas y Tableros Gráficos (Blade SSR UI y Chart.js)

**Archivo:** `resources/views/reportes/index.blade.php`

Integra librerías JavaScript de graficación (`Chart.js`) con las métricas calculadas en Laravel.

```html
@extends('layouts.app')
@section('titulo', 'Reportes y Estadísticas')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Reportes y Estadísticas</h1>
</div>

<!-- Filter Bar Card -->
<div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
    <form method="GET" action="{{ route('reportes.index') }}" class="row g-2 align-items-center">
        <div class="col-6 col-md-auto d-flex align-items-center gap-2">
            <span class="small fw-semibold text-secondary">Desde:</span>
            <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" class="form-control form-control-sm" style="width: 140px;">
        </div>

        <div class="col-6 col-md-auto d-flex align-items-center gap-2">
            <span class="small fw-semibold text-secondary">Hasta:</span>
            <input type="date" name="fecha_fin" value="{{ $fechaFin }}" class="form-control form-control-sm" style="width: 140px;">
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">Generar Reporte</button>
        </div>

        <div class="col-auto ms-auto">
            <a href="{{ route('reportes.exportar', ['tipo' => 'citas', 'formato' => 'pdf', 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                <i data-lucide="file-text" class="me-1"></i> Exportar PDF
            </a>
        </div>
    </form>
</div>

<!-- Stat Summary Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-top border-4 border-primary">
            <h3 class="fw-bold mb-0 text-primary">{{ $totalAgendadas }}</h3>
            <span class="text-muted small">Total Agendadas</span>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-top border-4 border-success">
            <h3 class="fw-bold mb-0 text-success">{{ $totalCompletadas }}</h3>
            <span class="text-muted small">Completadas</span>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-top border-4 border-danger">
            <h3 class="fw-bold mb-0 text-danger">{{ $totalCanceladas }}</h3>
            <span class="text-muted small">Canceladas</span>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-top border-4 border-warning">
            <h3 class="fw-bold mb-0 text-warning">{{ $tasaAsistencia }}%</h3>
            <span class="text-muted small">Tasa de Asistencia</span>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <h5 class="fw-bold mb-3">Distribución por Estado</h5>
            <div style="height: 280px; position: relative;">
                <canvas id="chart_estados"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <h5 class="fw-bold mb-3">Citas por Especialidad</h5>
            <div style="height: 280px; position: relative;">
                <canvas id="chart_especialidades"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const citasData = @json($citasData);
        const estados = {};
        const especialidades = {};

        citasData.forEach(c => {
            const est = c.estado || 'Pendiente';
            estados[est] = (estados[est] || 0) + 1;
            const esp = c.especialidad?.nombre || 'General';
            especialidades[esp] = (especialidades[esp] || 0) + 1;
        });

        new Chart(document.getElementById('chart_estados'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(estados),
                datasets: [{ data: Object.values(estados), backgroundColor: ['#2a9d8f', '#e76f51', '#e9c46a', '#457b9d'] }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('chart_especialidades'), {
            type: 'bar',
            data: {
                labels: Object.keys(especialidades),
                datasets: [{ label: 'Citas agendadas', data: Object.values(especialidades), backgroundColor: '#457b9d' }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    });
</script>
@endsection
```

---

## 7. Motor de Exportación de Documentos (PDF / Excel)

El sistema utiliza `Barryvdh\DomPDF\Facade\Pdf` para convertir dinámicamente plantillas Blade a archivos binarios PDF.

```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadView('reportes.pdf', ['citas' => $citas, 'tipo' => $tipo]);
return $pdf->download('reporte-' . $tipo . '-' . now()->format('Ymd') . '.pdf');
```

---

## 8. Rutas (API y Web)

### 8.1 Rutas API (`routes/api.php`)

```php
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/reportes/citas', [ReportesController::class, 'reporteCitas']);
        Route::get('/reportes/doctores', [ReportesController::class, 'reporteDoctores']);
        Route::get('/reportes/especialidades', [ReportesController::class, 'reporteEspecialidades']);
        Route::get('/reportes/pacientes', [ReportesController::class, 'reportePacientes']);
        Route::get('/reportes/resumenDiario', [ReportesController::class, 'resumenDiario']);
        Route::get('/reportes/exportar/{tipo}', [ReportesController::class, 'exportarReporte']);
    });
});
```

---

### 8.2 Rutas Web (`routes/web.php`)

```php
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/reportes', [ReportesWebController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/exportar/{tipo}', [ReportesWebController::class, 'exportar'])->name('reportes.exportar');
    });
});
```

---

## 9. Flujos Completos de Operación

### 9.1 Flujo de Generación de Tablero y Gráficas Chart.js

```
   ADMINISTRADOR (WEB)                    ReportesWebController                  ReportesRepository                       BASE DE DATOS
            │                                        │                                    │                                           │
            │ GET /reportes?fecha_inicio=...         │                                    │                                           │
            ├───────────────────────────────────────►│                                    │                                           │
            │                                        │ reporteCitas($filtros)             │                                           │
            │                                        ├───────────────────────────────────►│                                           │
            │                                        │                                    │ SELECT COUNT(*) ... (clone query)        │
            │                                        │                                    ├──────────────────────────────────────────►│
            │                                        │                                    │◄──────────────────────────────────────────┤
            │                                        │                                    │                                           │
            │                                        │ Calculo de KPIs:                   │                                           │
            │                                        │ tasaAsistencia = (comp/total)*100  │                                           │
            │                                        │                                    │                                           │
            │ Render /reportes/index.blade.php       │◄───────────────────────────────────┤                                           │
            │ + @json($citasData)                    │                                                                                │
            │ + Chart.js Doughnut & Bar Init         │                                                                                │
            │◄───────────────────────────────────────┤                                                                                │
```

---

## 10. Relación con Otros Módulos

```
                               ┌──────────────────────────┐
                               │   Módulo 9: REPORTES Y   │
                               │       ESTADÍSTICAS       │
                               └────────────┬─────────────┘
                                            │
         ┌───────────────────────┬──────────┴────────────┬────────────────────────┐
         ▼                       ▼                       ▼                        ▼
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────────┐
│ Mod 2: Doctores  │  │ Mod 3: Especialid│  │ Mod 5: Gestión de│  │ Mod 6: Pacientes     │
│                  │  │                  │  │ Citas            │  │                      │
│ Consultas atend. │  │ Demanda por área │  │ Métricas Estado  │  │ Asistencia Pacientes │
└──────────────────┘  └──────────────────┘  └──────────────────┘  └──────────────────────┘
```

---

## 11. Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ReportesController.php              # Controller API REST e impresión PDF
│   │   │   └── Web/
│   │   │       └── ReportesWebController.php        # Controller Web SSR y cálculo de KPIs
│   │   └── Repository/
│   │       └── ReportesRepository.php              # Repositorio de analítica y agregaciones SQL
├── resources/views/
│   └── reportes/
│       ├── index.blade.php                         # Tablero principal de KPIs y Chart.js
│       └── pdf.blade.php                           # Plantilla limpia para generación de DomPDF
└── routes/
    ├── api.php                                     # Endpoints API REST (/api/reportes/citas, etc.)
    └── web.php                                     # Rutas Web SSR (/reportes, etc.)
```

---

> **Módulo anterior:** [08 - Perfil de Usuario](./08-Perfil-de-Usuario.md)  
> **Siguiente módulo:** [10 - Recepcionistas](./10-Recepcionistas.md)
