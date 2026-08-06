@extends('layouts.app')
@section('titulo', 'Registro de Consulta')

@section('content')
<!-- Header Controls -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('doctor.agenda') }}" class="p-2 bg-surface border border-border rounded-xl text-text-secondary hover:text-primary transition-all">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Registro de Consulta y Diagnóstico</h1>
        <p class="text-xs text-text-secondary mt-0.5">Registra los signos vitales, diagnóstico y tratamiento del paciente</p>
    </div>
</div>

<div class="max-w-3xl mx-auto space-y-6">
    <!-- Patient Mini Card -->
    <div class="bg-surface rounded-2xl card-shadow border border-border p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center border border-primary/20 text-lg">
                {{ strtoupper(substr($cita->perfilPaciente?->usuario?->nombre ?? 'P', 0, 2)) }}
            </div>
            <div>
                <h4 class="font-bold text-text-primary text-base">{{ $cita->perfilPaciente?->usuario?->nombre ?? 'Paciente' }}</h4>
                <p class="text-xs text-text-secondary mt-0.5">
                    Expediente: <strong class="text-primary">{{ $cita->perfilPaciente?->numero_expediente ?? 'N/A' }}</strong> | Cita #{{ $cita->id }}
                </p>
                <p class="text-[11px] text-text-muted mt-0.5">Dr. {{ auth()->user()->nombre }} • {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('D, d M Y - h:i A') }}</p>
            </div>
        </div>
        <span class="px-3 py-1.5 rounded-full bg-sky-50 text-sky-700 text-xs font-semibold border border-sky-200 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
            En Consulta
        </span>
    </div>

    <!-- Form Card -->
    <form method="POST" action="{{ route('notas.store', $cita->id ) }}" class="bg-surface rounded-2xl card-shadow border border-border p-6 space-y-6">
        @csrf

        <!-- Section 1: Signos Vitales -->
        <div>
            <h3 class="font-bold text-danger text-base mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-xl">heart_pulse</span>
                <span>Signos Vitales</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="space-y-1">
                    <label for="inp_presion" class="text-xs font-semibold text-text-secondary block">Presión (mmHg)</label>
                    <input type="text" id="inp_presion" name="presion_arterial" placeholder="120/80" value="{{ old('presion_arterial') }}" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="inp_frecuencia" class="text-xs font-semibold text-text-secondary block">FC (bpm)</label>
                    <input type="number" id="inp_frecuencia" name="frecuencia_cardiaca" placeholder="72" value="{{ old('frecuencia_cardiaca') }}" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="inp_temperatura" class="text-xs font-semibold text-text-secondary block">Temp (°C)</label>
                    <input type="text" id="inp_temperatura" name="temperatura" placeholder="36.5" value="{{ old('temperatura') }}" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="inp_peso" class="text-xs font-semibold text-text-secondary block">Peso (kg)</label>
                    <input type="text" id="inp_peso" name="peso" placeholder="70" value="{{ old('peso') }}" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>
        </div>

        <hr class="border-border">

        <!-- Section 2: Diagnóstico -->
        <div>
            <h3 class="font-bold text-primary text-base mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-xl">clipboard</span>
                <span>Diagnóstico Médico *</span>
            </h3>
            <textarea id="txt_diagnostico" name="diagnostico" rows="5" required placeholder="Descripción detallada del diagnóstico del paciente..." class="w-full p-4 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">{{ old('diagnostico') }}</textarea>
            @error('diagnostico')
                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>

        <hr class="border-border">

        <!-- Section 3: Tratamiento -->
        <div>
            <h3 class="font-bold text-sky-700 text-base mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-xl">pill</span>
                <span>Tratamiento y Recomendaciones *</span>
            </h3>
            <textarea id="txt_tratamiento" name="tratamiento" rows="4" required placeholder="Medicamentos, dosis y recomendaciones clínicas..." class="w-full p-4 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">{{ old('tratamiento') }}</textarea>
            @error('tratamiento')
                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>

        <hr class="border-border">

        <!-- Section 4: Observaciones -->
        <div>
            <label for="txt_observaciones" class="text-xs font-semibold text-text-secondary block mb-2">Observaciones Adicionales (Opcional)</label>
            <textarea id="txt_observaciones" name="notas_adicionales" rows="3" placeholder="Comentarios adicionales o notas internas..." class="w-full p-4 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">{{ old('notas_adicionales') }}</textarea>
        </div>

        <!-- Actions Bar -->
        <div class="pt-4 border-t border-border flex flex-col sm:flex-row items-center justify-between gap-3">
            <a href="{{ route('doctor.agenda') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all text-center">
                Cancelar
            </a>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-lg">check_circle</span>
                <span>Registrar Nota y Completar Consulta</span>
            </button>
        </div>
    </form>
</div>
@endsection