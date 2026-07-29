@extends('layouts.app')
@section('titulo', 'Horarios de Atención')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4 pb-2 border-bottom">
    <a href="{{ route('doctores.index') }}" class="btn btn-outline-secondary btn-sm"><i data-lucide="arrow-left"></i></a>
    <h1 class="h3 fw-bold mb-0">Horarios de Atención</h1>
</div>

<!-- Mini Card Doctor -->
<div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 18px;">
                {{ strtoupper(substr($doctor['nombre'] ?? 'D', 0, 2)) }}
            </div>
            <div>
                <h4 class="fw-bold mb-1">Dr. {{ $doctor['nombre'] ?? 'Médico' }}</h4>
                <span class="badge bg-success">{{ $doctor['especialidad'] ?? 'General' }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_horario">+ Agregar Horario</button>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal_bloqueo">+ Bloquear Horario</button>
        </div>
    </div>
</div>

<!-- Main Layout Grid + Side Panel -->
<div class="row g-4">
    <!-- Weekly Schedule Grid -->
    <div class="col-lg-8 col-xl-9">
        <h5 class="fw-bold mb-3">Disponibilidad Semanal</h5>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-xl-7 g-2">
            @php
                $diasMap = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
            @endphp
            @foreach($diasMap as $numDia => $nombreDia)
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-3 h-100 p-2 bg-light">
                        <div class="fw-bold text-center small pb-2 border-bottom mb-2 text-dark">{{ $nombreDia }}</div>
                        <div class="d-flex flex-column gap-2">
                            @php
                                $horariosDia = array_filter($horarios, fn($h) => ($h['dia_semana'] ?? 0) == $numDia);
                            @endphp
                            @forelse($horariosDia as $h)
                                <div class="p-2 bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-2 position-relative small">
                                    <form method="POST" action="{{ route('horarios.destroy', $h['id']) }}" onsubmit="return confirm('¿Eliminar horario?');" class="position-absolute top-0 end-0 me-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0 text-decoration-none" title="Eliminar">&times;</button>
                                    </form>
                                    <div class="fw-bold" style="font-size: 11px;">{{ \Carbon\Carbon::parse($h['hora_inicio'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($h['hora_fin'])->format('h:i A') }}</div>
                                    <div class="text-secondary" style="font-size: 10px;">{{ $h['duracion_cita_minutos'] ?? 30 }} min</div>
                                </div>
                            @empty
                                <span class="text-muted text-center extra-small py-2" style="font-size: 11px;">Sin horario</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Bloqueos Registrados -->
    <div class="col-lg-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <h5 class="fw-bold mb-3">Bloqueos de Agenda</h5>
            <div class="d-flex flex-column gap-2">
                @forelse($bloqueos as $bloqueo)
                    <div class="p-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-3 position-relative small">
                        <form method="POST" action="{{ route('bloqueos.destroy', $bloqueo['id']) }}" onsubmit="return confirm('¿Eliminar bloqueo?');" class="position-absolute top-0 end-0 me-2 mt-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0 text-decoration-none" title="Eliminar Bloqueo">&times;</button>
                        </form>
                        <div class="fw-bold mb-1">{{ \Carbon\Carbon::parse($bloqueo['fecha_inicio'])->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($bloqueo['fecha_fin'])->format('d/m/Y H:i') }}</div>
                        <div class="text-secondary small">Motivo: {{ $bloqueo['motivo'] ?? 'Sin motivo' }}</div>
                    </div>
                @empty
                    <p class="text-muted small mb-0 py-2">No hay bloqueos activos para este médico.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Horario Nativo Bootstrap -->
<div class="modal fade" id="modal_horario" tabindex="-1" aria-labelledby="modal_horario_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modal_horario_title">Agregar Horario de Atención</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('horarios.store', $doctorId) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="sel_dia" class="form-label fw-medium">Día de la Semana *</label>
                        <select id="sel_dia" name="dia_semana" class="form-select" required>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="7">Domingo</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="inp_hora_inicio" class="form-label fw-medium">Hora Inicio *</label>
                            <input type="time" id="inp_hora_inicio" name="hora_inicio" value="08:00" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label for="inp_hora_fin" class="form-label fw-medium">Hora Fin *</label>
                            <input type="time" id="inp_hora_fin" name="hora_fin" value="14:00" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inp_duracion" class="form-label fw-medium">Duración por Cita (Minutos)</label>
                        <input type="number" id="inp_duracion" name="duracion_cita_minutos" value="30" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Horario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Registrar Bloqueo Nativo Bootstrap -->
<div class="modal fade" id="modal_bloqueo" tabindex="-1" aria-labelledby="modal_bloqueo_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title text-danger fw-bold" id="modal_bloqueo_title">Registrar Bloqueo de Agenda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('bloqueos.store', $doctorId) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="inp_f_inicio" class="form-label fw-medium">Fecha / Hora Inicio *</label>
                        <input type="datetime-local" id="inp_f_inicio" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="inp_f_fin" class="form-label fw-medium">Fecha / Hora Fin *</label>
                        <input type="datetime-local" id="inp_f_fin" name="fecha_fin" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="txt_motivo_blq" class="form-label fw-medium">Motivo del Bloqueo *</label>
                        <input type="text" id="txt_motivo_blq" name="motivo" placeholder="Vacaciones, congreso, etc." class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Registrar Bloqueo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
