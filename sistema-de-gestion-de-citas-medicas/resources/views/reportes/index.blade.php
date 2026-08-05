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

        <div class="col-12 col-md-auto">
            <select name="doctor_id" class="form-select form-select-sm" style="width: 170px;">
                <option value="">Doctor: Todos</option>
                @foreach($doctores as $doc)
                    <option value="{{ $doc['id'] }}" {{ $doctorId == $doc['id'] ? 'selected' : '' }}>
                        Dr. {{ $doc['nombre'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-auto">
            <select name="especialidad_id" class="form-select form-select-sm" style="width: 170px;">
                <option value="">Especialidad: Todas</option>
                @foreach($especialidades as $esp)
                    <option value="{{ $esp['id'] }}" {{ $especialidadId == $esp['id'] ? 'selected' : '' }}>
                        {{ $esp['nombre'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">
                Generar Reporte
            </button>
        </div>

        <div class="col-auto ms-auto">
            <a href="{{ route('reportes.exportar', ['tipo' => 'citas', 'formato' => 'pdf', 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                <i data-lucide="file-text" class="me-1"></i> Exportar PDF
            </a>
        </div>
    </form>
</div>

<!-- Row 1: Stat Summary Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-top border-4 border-primary">
            <div>
                <h3 class="fw-bold mb-0 text-primary">{{ $totalAgendadas }}</h3>
                <span class="text-muted small">Total Agendadas</span>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-top border-4 border-success">
            <div>
                <h3 class="fw-bold mb-0 text-success">{{ $totalCompletadas }}</h3>
                <span class="text-muted small">Completadas</span>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-top border-4 border-danger">
            <div>
                <h3 class="fw-bold mb-0 text-danger">{{ $totalCanceladas }}</h3>
                <span class="text-muted small">Canceladas</span>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-top border-4 border-warning">
            <div>
                <h3 class="fw-bold mb-0 text-warning">{{ $tasaAsistencia }}%</h3>
                <span class="text-muted small">Tasa de Asistencia</span>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Charts Grid -->
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

<!-- Row 3: Tabla Detalle -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-0 pt-3 px-3">
        <h5 class="fw-bold mb-0">Desglose de Citas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Fecha / Hora</th>
                        <th>Paciente</th>
                        <th>Doctor</th>
                        <th>Especialidad</th>
                        <th class="pe-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($citasData as $cita)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">{{ \Carbon\Carbon::parse($cita['fecha_hora'])->format('d/m/Y H:i A') }}</td>
                            <td>{{ $cita['paciente']['nombre'] ?? 'N/A' }}</td>
                            <td>Dr. {{ $cita['doctor']['nombre'] ?? 'N/A' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $cita['especialidad']['nombre'] ?? 'General' }}</span></td>
                            <td class="pe-3">
                                @php
                                    $estado = strtolower($cita['estado'] ?? 'pendiente');
                                    $badgeClass = match($estado) {
                                        'confirmada', 'completada' => 'bg-success',
                                        'cancelada' => 'bg-danger',
                                        default => 'bg-warning text-dark'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} text-capitalize">{{ $estado }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Sin datos para el período seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                datasets: [{
                    data: Object.values(estados),
                    backgroundColor: ['#2a9d8f', '#e76f51', '#e9c46a', '#457b9d']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        new Chart(document.getElementById('chart_especialidades'), {
            type: 'bar',
            data: {
                labels: Object.keys(especialidades),
                datasets: [{
                    label: 'Citas agendadas',
                    data: Object.values(especialidades),
                    backgroundColor: '#457b9d'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>
@endsection
