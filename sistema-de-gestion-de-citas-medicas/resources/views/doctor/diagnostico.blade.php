@extends('layouts.app')
@section('titulo', 'Registro de Consulta')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4 pb-2 border-bottom">
    <a href="{{ route('doctor.agenda') }}" class="btn btn-outline-secondary btn-sm"><i data-lucide="arrow-left"></i></a>
    <h1 class="h3 fw-bold mb-0">Registro de Consulta y Diagnóstico</h1>
</div>

<div class="mx-auto" style="max-width: 840px;">
    <!-- Patient Mini Card -->
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 20px;">
                    {{ strtoupper(substr($cita->perfilPaciente?->usuario?->nombre ?? 'P', 0, 2)) }}
                </div>
                <div>
                    <h5 class="fw-bold mb-1">{{ $cita->perfilPaciente?->usuario?->nombre ?? 'Paciente' }}</h5>
                    <span class="text-secondary small">
                        Expediente: {{ $cita->perfilPaciente?->numero_expediente ?? 'N/A' }} | Cita #{{ $cita->id }}
                    </span>
                </div>
            </div>
            <span class="badge bg-warning text-dark fs-6">En Consulta</span>
        </div>
    </div>

    <!-- Form Card -->
    <form method="POST" action="{{ route('notas.store', $cita->id) }}">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
            <!-- Section 1: Signos Vitales -->
            <div class="mb-4">
                <h5 class="fw-bold text-danger mb-3 d-flex align-items-center gap-2">
                    <i data-lucide="heart-pulse"></i> Signos Vitales
                </h5>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <label for="inp_presion" class="form-label small fw-semibold text-secondary">Presión (mmHg)</label>
                        <input type="text" id="inp_presion" name="presion_arterial" class="form-control" placeholder="120/80" value="{{ old('presion_arterial') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="inp_frecuencia" class="form-label small fw-semibold text-secondary">FC (bpm)</label>
                        <input type="number" id="inp_frecuencia" name="frecuencia_cardiaca" class="form-control" placeholder="72" value="{{ old('frecuencia_cardiaca') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="inp_temperatura" class="form-label small fw-semibold text-secondary">Temp (°C)</label>
                        <input type="text" id="inp_temperatura" name="temperatura" class="form-control" placeholder="36.5" value="{{ old('temperatura') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="inp_peso" class="form-label small fw-semibold text-secondary">Peso (kg)</label>
                        <input type="text" id="inp_peso" name="peso" class="form-control" placeholder="70" value="{{ old('peso') }}">
                    </div>
                </div>
            </div>

            <hr class="my-4 text-secondary opacity-25">

            <!-- Section 2: Diagnóstico -->
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                    <i data-lucide="clipboard"></i> Diagnóstico Médico *
                </h5>
                <textarea id="txt_diagnostico" name="diagnostico" class="form-control" rows="5" placeholder="Descripción detallada del diagnóstico del paciente..." required>{{ old('diagnostico') }}</textarea>
            </div>

            <hr class="my-4 text-secondary opacity-25">

            <!-- Section 3: Tratamiento Indicado -->
            <div class="mb-4">
                <h5 class="fw-bold text-info mb-3 d-flex align-items-center gap-2">
                    <i data-lucide="pill"></i> Tratamiento y Recomendaciones *
                </h5>
                <textarea id="txt_tratamiento" name="tratamiento" class="form-control" rows="4" placeholder="Medicamentos, dosis y recomendaciones clínicas..." required>{{ old('tratamiento') }}</textarea>
            </div>

            <!-- Section 4: Notas Adicionales -->
            <div class="mb-2">
                <label for="txt_notas_adicionales" class="form-label small fw-semibold text-secondary">Observaciones Adicionales (Opcional)</label>
                <textarea id="txt_notas_adicionales" name="observaciones" class="form-control" rows="3" placeholder="Comentarios adicionales o notas internas...">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('doctor.agenda') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                <i data-lucide="check-circle" class="me-1"></i> Registrar Nota y Completar Consulta
            </button>
        </div>
    </form>
</div>
@endsection
