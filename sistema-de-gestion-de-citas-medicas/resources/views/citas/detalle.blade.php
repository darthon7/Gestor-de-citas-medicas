@extends('layouts.app')
@section('titulo', 'Detalle de la Cita')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4 pb-2 border-bottom">
    <a href="{{ route('citas.index') }}" class="btn btn-outline-secondary btn-sm"><i data-lucide="arrow-left"></i></a>
    <h1 class="h3 fw-bold mb-0">Detalle de Cita #{{ $cita['id'] }}</h1>
</div>

<div class="mx-auto" style="max-width: 680px;">
    <!-- Status Banner -->
    @php
        $estado = strtolower($cita['estado'] ?? 'pendiente');
        $badgeClass = match($estado) {
            'confirmada', 'completada' => 'bg-success text-white',
            'cancelada' => 'bg-danger text-white',
            default => 'bg-warning text-dark'
        };
    @endphp
    <div class="card border-0 shadow-sm rounded-3 mb-4 {{ $badgeClass }} bg-opacity-10">
        <div class="card-body d-flex align-items-center justify-content-between py-3">
            <div>
                <span class="text-uppercase small fw-bold">Estado de Cita:</span>
                <span class="badge {{ $badgeClass }} fs-6 ms-2 text-capitalize">{{ $cita['estado'] }}</span>
            </div>
            <span class="font-monospace fw-bold opacity-75">REF-{{ str_pad($cita['id'], 5, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>

    <!-- Main Card Details -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark">Información de la Consulta</h5>
        </div>
        <div class="card-body p-4">
            <ul class="list-group list-group-flush mb-0">
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                    <span class="text-secondary">Fecha y Hora:</span>
                    <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($cita['fecha_hora'])->isoFormat('DD [de] MMMM YYYY, h:i A') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                    <span class="text-secondary">Paciente:</span>
                    <span class="fw-bold text-dark">{{ $cita['paciente']['nombre'] ?? 'N/A' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                    <span class="text-secondary">Doctor:</span>
                    <span class="fw-bold text-dark">Dr. {{ $cita['doctor']['nombre'] ?? 'N/A' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                    <span class="text-secondary">Especialidad:</span>
                    <span class="badge bg-light text-dark border">{{ $cita['especialidad']['nombre'] ?? 'General' }}</span>
                </li>
                <li class="list-group-item px-0 pt-3 pb-0 border-0">
                    <span class="text-secondary d-block mb-2">Motivo de Consulta:</span>
                    <div class="p-3 bg-light rounded-3 text-dark small border">
                        {{ $cita['motivo_consulta'] ?? 'Sin motivo especificado' }}
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Doctor Notes Card -->
    @if(!empty($cita['nota_consulta']) || !empty($cita['notas']))
        @php
            $nota = $cita['nota_consulta'] ?? $cita['notas'] ?? null;
        @endphp
        <div class="card border-0 shadow-sm rounded-3 mb-4 border-start border-4 border-success">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0 text-success">Diagnóstico Médico</h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <span class="fw-bold text-dark d-block mb-1">Diagnóstico:</span>
                    <p class="text-secondary mb-0">{{ $nota['diagnostico'] ?? $nota['nota'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="fw-bold text-dark d-block mb-1">Tratamiento Indicado:</span>
                    <p class="text-secondary mb-0">{{ $nota['tratamiento'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Action Buttons Container -->
    <div class="d-grid gap-2">
        @if(in_array(Auth::user()->rol, ['admin', 'recepcionista']))
            @if(in_array($estado, ['pendiente', 'confirmada']))
                <form method="POST" action="{{ route('citas.checkin', $cita['id']) }}" class="d-grid">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary py-2 fw-semibold">
                        <i data-lucide="check-square" class="me-1"></i> Registrar Check-in (Paciente Presente)
                    </button>
                </form>
            @endif

            @if($estado !== 'cancelada' && $estado !== 'completada')
                <button type="button" class="btn btn-outline-danger py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modal_cancelar_cita">
                    <i data-lucide="x-circle" class="me-1"></i> Cancelar Cita
                </button>
            @endif
        @endif

        @if(Auth::user()->rol === 'doctor')
            @if(in_array($estado, ['confirmada', 'pendiente']))
                <form method="POST" action="{{ route('citas.iniciar', $cita['id']) }}" class="d-grid">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary py-2 fw-semibold">
                        <i data-lucide="play-circle" class="me-1"></i> Iniciar Consulta
                    </button>
                </form>
            @endif

            @if($estado === 'en_consulta')
                <a href="{{ route('doctor.diagnostico', $cita['id']) }}" class="btn btn-success py-2 fw-semibold text-center">
                    <i data-lucide="clipboard-edit" class="me-1"></i> Registrar Nota y Finalizar Consulta
                </a>
            @endif
        @endif
    </div>
</div>

<!-- Modal Cancelar Cita Nativo Bootstrap -->
<div class="modal fade" id="modal_cancelar_cita" tabindex="-1" aria-labelledby="modal_cancelar_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title text-danger fw-bold" id="modal_cancelar_title">Cancelar Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('citas.cancelar', $cita['id']) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">Por favor especifique el motivo de cancelación:</p>
                    <div class="mb-3">
                        <textarea name="motivo_cancelacion" class="form-control" rows="3" placeholder="Motivo de la cancelación..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
