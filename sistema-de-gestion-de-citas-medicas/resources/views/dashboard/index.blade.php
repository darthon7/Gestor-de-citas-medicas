@extends('layouts.app')
@section('titulo', 'Panel Principal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Panel Principal</h1>
    <span class="text-secondary small fw-medium">
        <i data-lucide="calendar" class="me-1"></i>
        {{ \Carbon\Carbon::now()->isoFormat('D [de] MMMM [de] YYYY') }}
    </span>
</div>

<!-- Stats Grid -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-primary">
            <div class="d-flex align-items-center">
                <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary me-3">
                    <i data-lucide="calendar" class="fs-4"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">{{ $statTotalDia }}</h3>
                    <span class="text-muted small">Citas del día</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-success">
            <div class="d-flex align-items-center">
                <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success me-3">
                    <i data-lucide="check-circle-2" class="fs-4"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">{{ $statCompletadas }}</h3>
                    <span class="text-muted small">Completadas hoy</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-warning">
            <div class="d-flex align-items-center">
                <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning me-3">
                    <i data-lucide="clock" class="fs-4"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">{{ $statPendientes }}</h3>
                    <span class="text-muted small">Pendientes</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-danger">
            <div class="d-flex align-items-center">
                <div class="rounded-circle p-3 bg-danger bg-opacity-10 text-danger me-3">
                    <i data-lucide="x-circle" class="fs-4"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">{{ $statCanceladas }}</h3>
                    <span class="text-muted small">Canceladas hoy</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard 2-column layout -->
<div class="row g-4">
    <!-- Left: Agenda del Día -->
    <div class="col-lg-7 col-xl-8">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Agenda del Día</h5>
                <a href="{{ route('citas.index') }}" class="btn btn-sm btn-outline-primary">Ver Calendario</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Hora</th>
                                <th>Paciente</th>
                                <th>Doctor</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($citasHoy as $cita)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('h:i A') }}</td>
                                    <td class="fw-semibold">{{ $cita->perfilPaciente?->usuario?->nombre ?? 'N/A' }}</td>
                                    <td>Dr. {{ $cita->perfilDoctor?->usuario?->nombre ?? 'N/A' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $cita->especialidad?->nombre ?? 'N/A' }}</span></td>
                                    <td>
                                        @php
                                            $badgeClass = match(strtolower($cita->estado)) {
                                                'confirmada', 'completada' => 'bg-success',
                                                'pendiente' => 'bg-warning text-dark',
                                                'cancelada' => 'bg-danger',
                                                default => 'bg-info'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} text-capitalize">{{ $cita->estado }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Sin citas agendadas para el día de hoy.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Próximas Citas -->
    <div class="col-lg-5 col-xl-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Próximas Citas</h5>
                <a href="{{ route('citas.crear') }}" class="btn btn-sm btn-primary">+ Nueva Cita</a>
            </div>
            <div class="card-body p-3">
                @forelse($proximasCitas as $cita)
                    <div class="p-3 bg-light rounded-3 mb-2 d-flex justify-content-between align-items-center border">
                        <div>
                            <div class="fw-bold text-dark mb-1">{{ $cita->perfilPaciente?->usuario?->nombre ?? 'Paciente' }}</div>
                            <div class="small text-secondary">
                                Dr. {{ $cita->perfilDoctor?->usuario?->nombre ?? 'Doctor' }} • {{ $cita->especialidad?->nombre }}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-primary small">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d/M H:i A') }}</div>
                            <span class="badge bg-info text-capitalize small" style="font-size: 10px;">{{ $cita->estado }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0 py-3 text-center">No hay próximas citas registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
