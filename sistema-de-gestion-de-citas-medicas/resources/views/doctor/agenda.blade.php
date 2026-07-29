@extends('layouts.app')
@section('titulo', 'Mi Agenda Médica')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Mi Agenda Médica</h1>
    <span class="text-secondary small fw-medium">
        <i data-lucide="calendar" class="me-1"></i>
        {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM, YYYY') }}
    </span>
</div>

<!-- Welcome Card -->
<div class="card border-0 shadow-sm rounded-4 text-white p-4 mb-4" style="background: linear-gradient(135deg, #1d3557 0%, #2a9d8f 100%);">
    <h3 class="fw-bold mb-1 text-white">Hola, Dr. {{ Auth::user()->nombre }}</h3>
    <p class="mb-0 text-white-50">Tienes {{ count($citas) }} consultas agendadas para la jornada seleccionada.</p>
</div>

<!-- Date Selector Bar -->
<form method="GET" action="{{ route('doctor.agenda') }}" class="row g-2 align-items-center mb-4">
    <div class="col-auto">
        <label for="inp_fecha_agenda" class="form-label fw-semibold small text-secondary mb-0">Seleccionar Fecha:</label>
    </div>
    <div class="col-auto">
        <input type="date" id="inp_fecha_agenda" name="fecha" value="{{ $fecha }}" class="form-control form-control-sm" style="width: 170px;" onchange="this.form.submit()">
    </div>
</form>

<!-- Agenda Timeline List -->
<div class="d-flex flex-column gap-3">
    @forelse($citas as $cita)
        @php
            $estado = strtolower($cita->estado);
            $badgeClass = match($estado) {
                'confirmada', 'completada' => 'bg-success',
                'en_consulta' => 'bg-warning text-dark',
                'cancelada' => 'bg-danger',
                default => 'bg-info'
            };
        @endphp
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary fw-bold p-3 text-center" style="min-width: 90px;">
                        {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('h:i A') }}
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1">{{ $cita->perfilPaciente?->usuario?->nombre ?? 'Paciente' }}</h5>
                        <span class="text-secondary small">Motivo: {{ $cita->motivo_consulta }}</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <span class="badge {{ $badgeClass }} text-capitalize fs-6">{{ $estado }}</span>

                    @if(in_array($estado, ['confirmada', 'pendiente']))
                        <form method="POST" action="{{ route('citas.iniciar', $cita->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-primary px-3">
                                Iniciar Consulta
                            </button>
                        </form>
                    @elseif($estado === 'en_consulta')
                        <a href="{{ route('doctor.diagnostico', $cita->id) }}" class="btn btn-sm btn-success px-3">
                            Registrar Diagnóstico
                        </a>
                    @elseif($estado === 'completada')
                        <a href="{{ route('citas.show', $cita->id) }}" class="btn btn-sm btn-outline-secondary px-3">
                            Ver Nota
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted">
            No tienes consultas agendadas para esta fecha.
        </div>
    @endforelse
</div>
@endsection
