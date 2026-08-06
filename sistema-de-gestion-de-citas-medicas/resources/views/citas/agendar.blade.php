@extends('layouts.app')
@section('titulo', 'Agendar Nueva Cita')

@section('styles')
<style>
    .slot-chip {
        padding: 6px 14px;
        border-radius: 10px;
        background-color: #f7f9fc;
        border: 1px solid #E2E8F0;
        font-size: 12px;
        font-weight: 600;
        color: #005275;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .slot-chip:hover {
        background-color: #e6f2f8;
        border-color: #005275;
    }
    .slot-chip.active {
        background-color: #005275;
        color: #ffffff;
        border-color: #005275;
        box-shadow: 0 2px 8px rgba(0, 82, 117, 0.25);
    }
</style>
@endsection

@section('content')
<!-- Header Controls -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('citas.index') }}" class="p-2 bg-surface border border-border rounded-xl text-text-secondary hover:text-primary transition-all">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Agendar Nueva Cita</h1>
        <p class="text-xs text-text-secondary mt-0.5">Selecciona el médico, la fecha y el horario de consulta</p>
    </div>
</div>

<div class="max-w-2xl mx-auto">
    <div class="bg-surface rounded-2xl p-6 md:p-8 card-shadow border border-border">
        <form method="POST" action="{{ route('citas.store') }}" id="form_agendar_cita" class="space-y-5">
            @csrf

            <h3 class="font-bold text-primary-dark text-base border-b border-border pb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">event_available</span>
                <span>Datos de la Cita Médica</span>
            </h3>

            <!-- Paciente -->
            @if(Auth::user()->rol === 'paciente')
                <input type="hidden" name="perfil_paciente_id" value="{{ Auth::user()->perfilPaciente?->id }}">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-text-secondary block">Paciente</label>
                    <input type="text" class="w-full px-4 py-2.5 bg-background border border-border rounded-xl text-sm font-semibold text-text-primary" value="{{ Auth::user()->nombre }} (Expediente: {{ Auth::user()->perfilPaciente?->numero_expediente ?? 'N/A' }})" readonly>
                </div>
            @else
                <div class="space-y-1">
                    <label for="sel_paciente_id" class="text-xs font-semibold text-text-secondary block">Paciente *</label>
                    <select id="sel_paciente_id" name="perfil_paciente_id" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                        <option value="">Seleccione Paciente...</option>
                        @foreach($pacientes as $pac)
                            <option value="{{ $pac->id }}" {{ old('perfil_paciente_id') == $pac->id ? 'selected' : '' }}>
                                {{ $pac->usuario?->nombre }} ({{ $pac->numero_expediente ?? 'EXP-' . str_pad($pac->id, 4, '0', STR_PAD_LEFT) }}) - CURP: {{ $pac->usuario?->curp }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Especialidad & Doctor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="sel_especialidad" class="text-xs font-semibold text-text-secondary block">Especialidad *</label>
                    <select id="sel_especialidad" name="especialidad_id" required onchange="filtrarDoctores()" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                        <option value="">Seleccione Especialidad...</option>
                        @foreach($especialidades as $esp)
                            <option value="{{ $esp['id'] }}">{{ $esp['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label for="sel_doctor" class="text-xs font-semibold text-text-secondary block">Doctor *</label>
                    <select id="sel_doctor" name="perfil_doctor_id" required onchange="consultarDisponibilidad()" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="inp_fecha" class="text-xs font-semibold text-text-secondary block">Fecha de Cita *</label>
                    <input type="date" id="inp_fecha" name="fecha_cita" value="{{ old('fecha_cita', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required onchange="consultarDisponibilidad()" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>

                <div class="space-y-1">
                    <label for="inp_hora" class="text-xs font-semibold text-text-secondary block">Hora de Consulta *</label>
                    <input type="time" id="inp_hora" name="hora_cita" value="{{ old('hora_cita', '09:00') }}" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm font-semibold text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <!-- Slots recomendados -->
            <div class="space-y-1">
                <label class="text-xs font-semibold text-text-secondary block">Horarios Sugeridos Disponibles</label>
                <div id="slots_container" class="flex flex-wrap gap-2 pt-1">
                    <p class="text-xs text-text-muted">Seleccione doctor y fecha para consultar horarios disponibles.</p>
                </div>
            </div>

            <!-- Motivo -->
            <div class="space-y-1">
                <label for="txt_motivo" class="text-xs font-semibold text-text-secondary block">Motivo de la Consulta *</label>
                <textarea id="txt_motivo" name="motivo_consulta" rows="3" required placeholder="Describe brevemente el motivo o síntomas..." class="w-full p-4 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">{{ old('motivo_consulta') }}</textarea>
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <a href="{{ route('citas.index') }}" class="px-5 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">check_circle</span>
                    <span>Confirmar Cita</span>
                </button>
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
            container.innerHTML = '<p class="text-xs text-text-muted">Seleccione doctor y fecha para consultar horarios disponibles.</p>';
            return;
        }

        try {
            const response = await fetch(`/api/obtenerDisponibilidad/${doctorId}?fecha=${fecha}`);
            const data = await response.json();
            const slots = data.data || data || [];

            if (!Array.isArray(slots) || slots.length === 0) {
                container.innerHTML = '<p class="text-xs text-amber-700 font-medium">No hay horarios disponibles para esta fecha.</p>';
                return;
            }

            container.innerHTML = slots.map(slot => `
                <button type="button" class="slot-chip" onclick="seleccionarHora('${slot}')">${slot}</button>
            `).join('');
        } catch (e) {
            container.innerHTML = '<p class="text-xs text-text-muted">Ingresa la hora manualmente arriba.</p>';
        }
    }

    function seleccionarHora(hora) {
        document.getElementById('inp_hora').value = hora;
        document.querySelectorAll('.slot-chip').forEach(btn => {
            btn.classList.toggle('active', btn.textContent === hora);
        });
    }
</script>
@endsection
