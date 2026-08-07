@extends('layouts.app')
@section('titulo', 'Expediente de Paciente')

@section('content')
<!-- Header Controls -->
<div class="flex items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('pacientes.index') }}" class="p-2 bg-surface border border-border rounded-xl text-text-secondary hover:text-primary transition-all">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-primary-dark">Expediente Clínico</h1>
            <p class="text-xs text-text-secondary mt-0.5">Consulta la información y el historial de consultas del paciente</p>
        </div>
    </div>
</div>

<!-- Header Card: Patient Summary -->
<div class="bg-surface rounded-2xl p-6 card-shadow border border-border mb-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-primary-light/40 text-primary-dark font-bold text-xl flex items-center justify-center border-2 border-primary/20 flex-shrink-0">
                {{ strtoupper(substr($paciente->usuario?->nombre ?? 'P', 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-text-primary">{{ $paciente->usuario?->nombre }}</h2>
                    @php
                        $estado = strtolower($paciente->usuario?->estado ?? 'activo');
                    @endphp
                    @if($estado === 'activo')
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            Activo
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 text-xs font-semibold border border-rose-200">
                            Inactivo
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-4 mt-2 text-xs text-text-secondary">
                    <span>Expediente: <strong class="text-primary font-semibold">{{ $paciente->numero_expediente ?? 'EXP-' . str_pad($paciente->id, 4, '0', STR_PAD_LEFT) }}</strong></span>
                    <span>CURP: <strong class="text-text-primary font-mono">{{ $paciente->usuario?->curp ?? 'N/A' }}</strong></span>
                    <span>Teléfono: <strong class="text-text-primary">{{ $paciente->usuario?->telefono ?? 'N/A' }}</strong></span>
                    <span>Correo: <strong class="text-text-primary">{{ $paciente->usuario?->email ?? 'N/A' }}</strong></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Details & History Layout -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left: Personal Data Card -->
    <div class="bg-surface rounded-2xl card-shadow border border-border p-6 h-fit">
        <h3 class="font-bold text-text-primary text-base mb-4 pb-3 border-b border-border flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">badge</span>
            <span>Información General</span>
        </h3>

        <div class="space-y-4 text-xs">
            <div>
                <span class="text-text-secondary block mb-1">Fecha de Nacimiento</span>
                <p class="font-semibold text-text-primary text-sm">
                    {{ $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}
                </p>
            </div>

            <div>
                <span class="text-text-secondary block mb-1">Sexo</span>
                <p class="font-semibold text-text-primary text-sm">
                    {{ $paciente->sexo === 'M' ? 'Masculino' : ($paciente->sexo === 'F' ? 'Femenino' : 'No especificado') }}
                </p>
            </div>

            <div>
                <span class="text-text-secondary block mb-1">Dirección</span>
                <p class="font-semibold text-text-primary text-sm">
                    {{ $paciente->direccion ?? 'No registrada' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Right: Appointments Timeline -->
    <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-bold text-text-primary text-base">Historial de Citas y Consultas</h3>
            <span class="text-xs text-text-secondary">Total: {{ count($paciente->citas) }} citas</span>
        </div>

        @forelse($paciente->citas as $cita)
            <div class="bg-surface rounded-2xl card-shadow border border-border p-5 hover:border-primary/30 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-primary/10 text-primary font-bold flex flex-col items-center justify-center flex-shrink-0 border border-primary/20">
                        <span class="text-lg leading-none">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d') }}</span>
                        <span class="text-[10px] uppercase tracking-wider font-semibold">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('M') }}</span>
                    </div>

                    <div>
                        <h4 class="font-bold text-text-primary text-sm">Dr. {{ $cita->perfilDoctor?->usuario?->nombre ?? 'Médico' }}</h4>
                        <p class="text-xs text-text-secondary mt-0.5">Especialidad: {{ $cita->especialidad?->nombre ?? 'General' }}</p>
                        <p class="text-[11px] text-text-muted mt-0.5">Hora: {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('h:i A') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                    @php
                        $statusClass = match(strtolower($cita->estado)) {
                            'confirmada', 'completada' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'en_consulta' => 'bg-sky-50 text-sky-700 border-sky-200',
                            'agendada', 'pendiente' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'cancelada' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-gray-50 text-gray-700 border-gray-200'
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClass }} capitalize">
                        {{ $cita->estado }}
                    </span>

                    <a href="{{ route('citas.show', $cita->id) }}" class="px-3 py-1.5 bg-background border border-border text-primary font-semibold text-xs rounded-xl hover:bg-primary/5 transition-all">
                        Ver Detalle
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-surface rounded-2xl card-shadow border border-border p-10 text-center text-xs text-text-muted">
                <span class="material-symbols-outlined text-4xl mb-2 block text-text-muted">event_busy</span>
                Este paciente aún no cuenta con citas registradas en su historial.
            </div>
        @endforelse
    </div>
</div>
@endsection
