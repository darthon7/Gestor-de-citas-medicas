@extends('layouts.app')
@section('titulo', 'Reportes y Estadísticas')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<!-- Header Controls -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Reportes y Estadísticas</h1>
        <p class="text-xs text-text-secondary mt-0.5">Análisis del flujo de citas, asistencia y actividad médica</p>
    </div>
    <a href="{{ route('reportes.exportar', ['tipo' => 'citas', 'formato' => 'pdf', 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" target="_blank" class="px-5 py-2.5 bg-surface border border-danger/40 text-danger rounded-xl font-semibold text-xs hover:bg-danger-light/50 transition-all flex items-center justify-center gap-2">
        <span class="material-symbols-outlined text-lg">picture_as_pdf</span>
        <span>Exportar PDF</span>
    </a>
</div>

<!-- Filter Bar Card -->
<div class="bg-surface rounded-2xl card-shadow border border-border p-5 mb-6">
    <form method="GET" action="{{ route('reportes.index') }}" class="flex flex-wrap items-end gap-4">
        <div class="space-y-1">
            <label for="inp_fecha_inicio" class="text-xs font-semibold text-text-secondary block">Desde:</label>
            <input type="date" id="inp_fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}" class="px-4 py-2.5 bg-white border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
        </div>
        <div class="space-y-1">
            <label for="inp_fecha_fin" class="text-xs font-semibold text-text-secondary block">Hasta:</label>
            <input type="date" id="inp_fecha_fin" name="fecha_fin" value="{{ $fechaFin }}" class="px-4 py-2.5 bg-white border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
        </div>
        <div class="space-y-1">
            <label for="sel_doctor" class="text-xs font-semibold text-text-secondary block">Doctor:</label>
            <select id="sel_doctor" name="doctor_id" class="pl-4 pr-8 py-2.5 bg-white border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                <option value="">Todos</option>
                @foreach($doctores as $doc)
                    @php
                        $docId = is_object($doc) ? $doc->id : ($doc['id'] ?? null);
                        $nombre = is_object($doc)
                            ? ($doc->usuario?->nombre ?? ($doc->nombre ?? 'Médico'))
                            : ($doc['usuario']['nombre'] ?? ($doc['nombre'] ?? 'Médico'));
                        $nombreCompleto = preg_match('/^(dr|dra)\.?\s/i', $nombre) ? $nombre : 'Dr. ' . $nombre;
                    @endphp
                    <option value="{{ $docId }}" {{ $doctorId == $docId ? 'selected' : '' }}>{{ $nombreCompleto }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1">
            <label for="sel_especialidad" class="text-xs font-semibold text-text-secondary block">Especialidad:</label>
            <select id="sel_especialidad" name="especialidad_id" class="pl-4 pr-8 py-2.5 bg-white border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                <option value="">Todas</option>
                @foreach($especialidades as $esp)
                    <option value="{{ $esp['id'] }}" {{ $especialidadId == $esp['id'] ? 'selected' : '' }}>{{ $esp['nombre'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-5 py-2.5 bg-primary text-white rounded-xl font-semibold text-xs shadow-md hover:bg-primary-dark transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">refresh</span>
            <span>Generar Reporte</span>
        </button>
    </form>
</div>

<!-- Row 1: Stat Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    <div class="bg-surface rounded-2xl p-5 card-shadow border-l-4 border-primary card-shadow-hover">
        <div class="p-2.5 bg-primary/10 text-primary rounded-xl w-fit mb-3">
            <span class="material-symbols-outlined text-2xl">event</span>
        </div>
        <h3 class="text-3xl font-bold text-primary-dark">{{ $totalAgendadas }}</h3>
        <p class="text-xs text-text-secondary mt-1">Total Agendadas</p>
    </div>
    <div class="bg-surface rounded-2xl p-5 card-shadow border-l-4 border-secondary card-shadow-hover">
        <div class="p-2.5 bg-secondary-light/40 text-secondary rounded-xl w-fit mb-3">
            <span class="material-symbols-outlined text-2xl">check_circle</span>
        </div>
        <h3 class="text-3xl font-bold text-primary-dark">{{ $totalCompletadas }}</h3>
        <p class="text-xs text-text-secondary mt-1">Completadas</p>
    </div>
    <div class="bg-surface rounded-2xl p-5 card-shadow border-l-4 border-danger card-shadow-hover">
        <div class="p-2.5 bg-danger-light text-danger rounded-xl w-fit mb-3">
            <span class="material-symbols-outlined text-2xl">cancel</span>
        </div>
        <h3 class="text-3xl font-bold text-primary-dark">{{ $totalCanceladas }}</h3>
        <p class="text-xs text-text-secondary mt-1">Canceladas</p>
    </div>
    <div class="bg-surface rounded-2xl p-5 card-shadow border-l-4 border-tertiary-fixed-dim card-shadow-hover">
        <div class="p-2.5 bg-amber-100 text-amber-800 rounded-xl w-fit mb-3">
            <span class="material-symbols-outlined text-2xl">percent</span>
        </div>
        <h3 class="text-3xl font-bold text-primary-dark">{{ $tasaAsistencia }}%</h3>
        <p class="text-xs text-text-secondary mt-1">Tasa de Asistencia</p>
    </div>
</div>

<!-- Row 2: Charts Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-surface rounded-2xl card-shadow border border-border p-6">
        <h3 class="font-bold text-text-primary text-sm mb-4 pb-3 border-b border-border">Distribución por Estado</h3>
        <div style="height: 280px; position: relative;">
            <canvas id="chart_estados"></canvas>
        </div>
    </div>
    <div class="bg-surface rounded-2xl card-shadow border border-border p-6">
        <h3 class="font-bold text-text-primary text-sm mb-4 pb-3 border-b border-border">Citas por Especialidad</h3>
        <div style="height: 280px; position: relative;">
            <canvas id="chart_especialidades"></canvas>
        </div>
    </div>
</div>

<!-- Row 3: Tabla Detalle -->
<div class="bg-surface rounded-2xl card-shadow border border-border overflow-hidden">
    <div class="px-6 py-4 border-b border-border">
        <h5 class="font-bold text-text-primary text-sm">Desglose de Citas</h5>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background/60 border-b border-border text-xs font-semibold text-text-secondary uppercase tracking-wider">
                    <th class="px-6 py-3">Fecha / Hora</th>
                    <th class="px-6 py-3">Paciente</th>
                    <th class="px-6 py-3">Doctor</th>
                    <th class="px-6 py-3">Especialidad</th>
                    <th class="px-6 py-3 text-right">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-sm">
                @forelse($citasData as $cita)
                    @php
                        $estado = strtolower($cita['estado'] ?? 'pendiente');
                        $badgeClass = match($estado) {
                            'confirmada', 'completada' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'cancelada' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'en_consulta' => 'bg-sky-50 text-sky-700 border-sky-200',
                            default => 'bg-amber-50 text-amber-700 border-amber-200'
                        };
                    @endphp
                    <tr class="hover:bg-background/40 transition-colors">
                        <td class="px-6 py-3.5 font-bold text-primary text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($cita['fecha_hora'])->format('d/m/Y H:i A') }}</td>
                        <td class="px-6 py-3.5 text-xs text-text-primary">{{ $cita['paciente']['nombre'] ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 text-xs text-text-secondary">Dr. {{ $cita['doctor']['nombre'] ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 text-xs whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg bg-background text-text-secondary border border-border font-medium">{{ $cita['especialidad']['nombre'] ?? 'General' }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }} capitalize">{{ $estado }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-xs text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-1 block text-text-muted">query_stats</span>
                            Sin datos para el período seleccionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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