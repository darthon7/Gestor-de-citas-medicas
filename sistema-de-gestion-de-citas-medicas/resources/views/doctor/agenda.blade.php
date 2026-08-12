@extends('layouts.app')
@section('titulo', 'Mi Agenda Médica')

@section('styles')
<style>
    .timeline-line::before {
        content: '';
        position: absolute;
        left: 1.25rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #E2E8F0;
        z-index: 0;
    }
</style>
@endsection

@section('content')
<!-- Header & Date Controls -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Mi Agenda Médica</h1>
        <p class="text-xs text-text-secondary mt-0.5">
            Tienes <strong class="text-primary">{{ count($citas) }}</strong> consultas agendadas para la jornada seleccionada.
        </p>
    </div>

    <form method="GET" action="{{ route('doctor.agenda') }}" class="flex items-center gap-3">
        <label for="inp_fecha_agenda" class="text-xs font-semibold text-text-secondary">Seleccionar Fecha:</label>
        <input type="date" id="inp_fecha_agenda" name="fecha" value="{{ $fecha }}" onchange="this.form.submit()" class="px-4 py-2.5 bg-surface border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
    </form>
</div>

<!-- Welcome Card -->
<div class="rounded-2xl p-6 mb-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4" style="background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-container) 100%);">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-white/10 border border-white/20 flex items-center justify-center font-bold text-lg">
            {{ strtoupper(substr($perfilDoctor->usuario->nombre ?? Auth::user()->nombre, 0, 2)) }}
        </div>
        <div>
            <h3 class="font-bold text-lg">Hola, Dr. {{ Auth::user()->nombre }}</h3>
            <p class="text-xs text-white/70">{{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM, YYYY') }}</p>
        </div>
    </div>
    <div class="bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 flex items-center gap-2 self-start md:self-auto">
        <span class="material-symbols-outlined text-secondary-light">calendar_month</span>
        <span class="text-xs font-semibold">{{ count($citas) }} consulta(s) en jornada</span>
    </div>
</div>

<!-- Agenda Timeline -->
<div class="relative timeline-line">
    @forelse($citas as $cita)
        @php
            $estado = strtolower($cita->estado);
            $dotClass = match($estado) {
                'completada' => 'bg-secondary-light text-secondary border-secondary',
                'en_consulta' => 'bg-primary-light text-primary border-primary',
                'cancelada' => 'bg-danger-light text-danger border-danger',
                'confirmada', 'agendada' => 'bg-tertiary-fixed text-tertiary border-tertiary',
                default => 'bg-surface-container-high text-text-muted border-border'
            };
            $cardClass = match($estado) {
                'completada' => 'border-l-4 border-secondary',
                'en_consulta' => 'border-l-4 border-primary-container ring-2 ring-primary-light/20',
                'cancelada' => 'border-l-4 border-danger opacity-70',
                'confirmada', 'agendada' => 'border-l-4 border-tertiary-fixed-dim',
                default => 'border-l-4 border-border'
            };
            $badgeClass = match($estado) {
                'completada' => 'bg-secondary-light/30 text-secondary',
                'en_consulta' => 'bg-primary-light/20 text-primary',
                'cancelada' => 'bg-danger-light text-danger',
                'confirmada', 'agendada' => 'bg-tertiary-fixed/30 text-tertiary',
                default => 'bg-background text-text-secondary'
            };
            $icon = match($estado) {
                'completada' => 'check_circle',
                'en_consulta' => 'play_circle',
                'cancelada' => 'cancel',
                'confirmada', 'agendada' => 'schedule',
                default => 'event'
            };
        @endphp
        <div class="relative z-10 flex gap-6 mb-6">
            <div class="w-10 h-10 rounded-full flex items-center justify-center border-4 border-background shadow-sm flex-shrink-0 {{ $dotClass }}">
                <span class="material-symbols-outlined text-lg">{{ $icon }}</span>
            </div>

            <div class="flex-1 bg-surface rounded-xl card-shadow {{ $cardClass }} overflow-hidden transition-all card-shadow-hover">
                <div class="p-5 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-start gap-5 min-w-0">
                        <div class="text-center min-w-[70px] flex-shrink-0">
                            <p class="text-lg font-bold text-text-primary leading-tight">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('h:i A') }}</p>
                            <p class="text-[11px] text-text-muted mt-0.5">{{ $cita->duracion_minutos ?? 30 }} min</p>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-text-primary text-sm truncate">{{ $cita->perfilPaciente?->usuario?->nombre ?? 'Paciente' }}</h4>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeClass }} capitalize">{{ $estado }}</span>
                            </div>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 items-center mt-1.5">
                                <span class="text-xs text-text-secondary flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">id_card</span>
                                    Exp. {{ $cita->perfilPaciente?->numero_expediente ?? 'N/A' }}
                                </span>
                                <span class="text-xs text-text-secondary flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">medical_information</span>
                                    {{ $cita->motivo_consulta }}
                                </span>
                                <span class="text-xs text-text-secondary flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">stethoscope</span>
                                    {{ $cita->especialidad?->nombre ?? 'General' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if(in_array($estado, ['confirmada', 'agendada', 'pendiente']))
                            <form method="POST" action="{{ route('citas.iniciar', $cita->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">play_arrow</span>
                                    <span>Iniciar Consulta</span>
                                </button>
                            </form>
                        @elseif($estado === 'en_consulta')
                            <a href="{{ route('doctor.diagnostico', $cita->id) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">edit_note</span>
                                <span>Registrar Diagnóstico</span>
                            </a>
                        @elseif($estado === 'completada')
                            <a href="{{ route('citas.show', $cita->id) }}" class="px-4 py-2 bg-background border border-border text-primary rounded-lg text-xs font-semibold transition-all flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">description</span>
                                <span>Ver Nota</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="relative z-10 bg-surface rounded-2xl card-shadow border border-border p-10 text-center text-xs text-text-muted">
            <span class="material-symbols-outlined text-4xl mb-2 block text-text-muted">event_busy</span>
            No tienes consultas agendadas para esta fecha.
        </div>
    @endforelse
</div>
@endsection
