@extends('layouts.app')
@section('titulo', 'Agendar Nueva Cita')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/pages/citas.css') }}">
@endsection

@section('content')
<div class="d-flex align-items-center gap-3 mb-4 pb-2 border-bottom">
    <a href="{{ route('citas.index') }}" class="btn btn-outline-secondary btn-sm"><i data-lucide="arrow-left"></i></a>
    <h1 class="h3 fw-bold mb-0">Agendar Nueva Cita</h1>
</div>

<div class="mx-auto" style="max-width: 760px;">
    <div class="card border-0 shadow-sm rounded-3 p-4">
        <form method="POST" action="{{ route('citas.store') }}" id="form_agendar_cita">
            @csrf
            
            <h5 class="fw-bold mb-4 border-bottom pb-2">Información de la Cita</h5>

            <!-- Paciente -->
            <div class="mb-3">
                <label for="sel_paciente_id" class="form-label fw-medium">Paciente *</label>
                <select id="sel_paciente_id" name="perfil_paciente_id" class="form-select" required>
                    <option value="">Seleccione Paciente...</option>
                    @foreach($pacientes as $pac)
                        <option value="{{ $pac->id }}" {{ old('perfil_paciente_id') == $pac->id ? 'selected' : '' }}>
                            {{ $pac->usuario?->nombre }} ({{ $pac->numero_expediente ?? 'EXP-' . str_pad($pac->id, 4, '0', STR_PAD_LEFT) }}) - CURP: {{ $pac->usuario?->curp }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Especialidad & Doctor -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="sel_especialidad" class="form-label fw-medium">Especialidad *</label>
                    <select id="sel_especialidad" name="especialidad_id" class="form-select" required onchange="filtrarDoctores()">
                        <option value="">Seleccione Especialidad...</option>
                        @foreach($especialidades as $esp)
                            <option value="{{ $esp['id'] }}">{{ $esp['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="sel_doctor" class="form-label fw-medium">Doctor *</label>
                    <select id="sel_doctor" name="perfil_doctor_id" class="form-select" required onchange="consultarDisponibilidad()">
                        <option value="">Seleccione Doctor...</option>
                        @foreach($doctores as $doc)
                            <option value="{{ $doc['id'] }}" data-especialidad="{{ $doc['especialidad_id'] ?? '' }}">
                                Dr. {{ $doc['nombre'] }} ({{ $doc['especialidad'] ?? 'General' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Fecha y Hora -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="inp_fecha" class="form-label fw-medium">Fecha de Cita *</label>
                    <input type="date" id="inp_fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" class="form-control" required onchange="consultarDisponibilidad()">
                </div>

                <div class="col-md-6">
                    <label for="inp_hora" class="form-label fw-medium">Hora *</label>
                    <input type="time" id="inp_hora" name="hora" value="{{ old('hora', '09:00') }}" class="form-control" required>
                </div>
            </div>

            <!-- Slots recomendados -->
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Horarios Disponibles Consultados</label>
                <div id="slots_container" class="d-flex gap-2 flex-wrap mt-1">
                    <p class="text-muted small mb-0">Seleccione doctor y fecha para ver sugerencias.</p>
                </div>
            </div>

            <!-- Motivo -->
            <div class="mb-4">
                <label for="txt_motivo" class="form-label fw-medium">Motivo de la Consulta *</label>
                <textarea id="txt_motivo" name="motivo_consulta" class="form-control" rows="3" placeholder="Describa el motivo de la cita médica..." required>{{ old('motivo_consulta') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('citas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Confirmar y Agendar Cita</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function filtrarDoctores() {
        const espId = document.getElementById('sel_especialidad').value;
        const selectDoc = document.getElementById('sel_doctor');
        const options = selectDoc.querySelectorAll('option');

        options.forEach(opt => {
            if (!opt.value) return;
            const docEsp = opt.getAttribute('data-especialidad');
            if (!espId || docEsp == espId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
        selectDoc.value = '';
    }

    async function consultarDisponibilidad() {
        const doctorId = document.getElementById('sel_doctor').value;
        const fecha = document.getElementById('inp_fecha').value;
        const container = document.getElementById('slots_container');

        if (!doctorId || !fecha) {
            container.innerHTML = '<p class="text-muted small mb-0">Seleccione doctor y fecha para ver sugerencias.</p>';
            return;
        }

        try {
            const response = await fetch(`/api/obtenerDisponibilidad/${doctorId}?fecha=${fecha}`);
            const data = await response.json();
            const slots = data.data || data || [];

            if (!Array.isArray(slots) || slots.length === 0) {
                container.innerHTML = '<p class="text-warning small mb-0">No hay horarios disponibles para esta fecha.</p>';
                return;
            }

            container.innerHTML = slots.map(slot => `
                <div class="slot-btn border" onclick="seleccionarHora('${slot}')">${slot}</div>
            `).join('');
        } catch (e) {
            container.innerHTML = '<p class="text-muted small mb-0">Sin sugerencias automáticas.</p>';
        }
    }

    function seleccionarHora(hora) {
        document.getElementById('inp_hora').value = hora;
        document.querySelectorAll('.slot-btn').forEach(btn => {
            btn.classList.toggle('active', btn.textContent === hora);
        });
    }
</script>
@endsection
