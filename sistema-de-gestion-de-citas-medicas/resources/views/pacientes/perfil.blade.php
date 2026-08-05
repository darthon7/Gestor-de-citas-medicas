@extends('layouts.app')
@section('titulo', 'Expediente de Paciente')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Expediente de Paciente</h1>
    <a href="{{ route('pacientes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i data-lucide="arrow-left" class="me-1"></i> Volver a Lista
    </a>
</div>

<!-- Patient Profile Card -->
<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="profile-avatar-md flex-shrink-0" style="width: 72px; height: 72px; font-size: 26px;">
            {{ strtoupper(substr($paciente->usuario?->nombre ?? 'P', 0, 2)) }}
        </div>
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h2 class="h4 fw-bold mb-0 text-dark">{{ $paciente->usuario?->nombre }}</h2>
                @php
                    $estado = strtolower($paciente->usuario?->estado ?? 'activo');
                    $badgeClass = match($estado) {
                        'activo' => 'bg-success',
                        'inactivo' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                @endphp
                <span class="badge {{ $badgeClass }} text-capitalize">{{ $estado }}</span>
            </div>
            <div class="d-flex gap-3 text-secondary small flex-wrap">
                <span>Expediente: <strong class="text-primary-custom">{{ $paciente->numero_expediente ?? 'EXP-' . str_pad($paciente->id, 4, '0', STR_PAD_LEFT) }}</strong></span>
                <span>CURP: <strong class="text-dark font-monospace">{{ $paciente->usuario?->curp ?? 'N/A' }}</strong></span>
                <span>Teléfono: <strong class="text-dark">{{ $paciente->usuario?->telefono ?? 'N/A' }}</strong></span>
                <span>Correo: <strong class="text-dark">{{ $paciente->usuario?->email ?? 'N/A' }}</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Layout Details & Citas Timeline -->
<div class="row g-4">
    <!-- Left: Datos Personales -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">Información General</h5>
            <div class="d-flex flex-column gap-3 small">
                <div>
                    <span class="text-secondary d-block extra-small">Fecha de Nacimiento</span>
                    <strong class="text-dark fs-6">{{ $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</strong>
                </div>
                <div>
                    <span class="text-secondary d-block extra-small">Sexo</span>
                    <strong class="text-dark fs-6">{{ $paciente->sexo === 'M' ? 'Masculino' : ($paciente->sexo === 'F' ? 'Femenino' : 'N/A') }}</strong>
                </div>
                <div>
                    <span class="text-secondary d-block extra-small">Dirección</span>
                    <strong class="text-dark fs-6">{{ $paciente->direccion ?? 'No registrada' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Historial de Citas -->
    <div class="col-lg-8">
        <h5 class="fw-bold mb-3 text-dark">Historial de Citas y Consultas</h5>
        @forelse($paciente->citas as $cita)
            <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary fw-bold p-2 text-center" style="min-width: 60px;">
                            <span class="fs-5 d-block leading-none">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d') }}</span>
                            <span class="extra-small text-uppercase">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('M') }}</span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Dr. {{ $cita->perfilDoctor?->usuario?->nombre ?? 'Médico' }}</h6>
                            <span class="text-secondary small d-block">Especialidad: {{ $cita->especialidad?->nombre ?? 'General' }}</span>
                            <span class="text-muted extra-small">Hora: {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('h:i A') }}</span>
                        </div>
                    </div>
                    <div class="text-end">
                        @php
                            $badgeClass = match(strtolower($cita->estado)) {
                                'confirmada', 'completada' => 'bg-success',
                                'pendiente' => 'bg-warning text-dark',
                                'cancelada' => 'bg-danger',
                                default => 'bg-info'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} text-capitalize mb-2 d-inline-block">{{ $cita->estado }}</span>
                        <div>
                            <a href="{{ route('citas.show', $cita->id) }}" class="btn btn-sm btn-outline-primary py-1">Ver Detalle</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center text-muted">
                Este paciente aún no registra historial de citas médicas.
            </div>
        @endforelse
    </div>
</div>
@endsection
