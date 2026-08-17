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
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-2xl">calendar_today</span>
            <h1 class="text-2xl font-bold text-primary-dark">Mi Agenda Médica</h1>
        </div>
        <p class="text-xs text-text-secondary mt-1">
            @if(!empty($fecha))
                Mostrando <strong class="text-primary">{{ count($citas) }}</strong> consulta(s) para el <strong>{{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM, YYYY') }}</strong>.
            @else
                Tienes <strong class="text-primary">{{ count($citas) }}</strong> consulta(s) registradas en total en tu agenda médica.
            @endif
        </p>
    </div>

    <!-- Date and filter bar -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('doctor.agenda') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all {{ empty($fecha) ? 'bg-primary text-white shadow-sm' : 'bg-surface border border-border text-text-secondary hover:bg-background' }}">
            Todas las fechas
        </a>
        <a href="{{ route('doctor.agenda', ['fecha' => date('Y-m-d')]) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all {{ $fecha === date('Y-m-d') ? 'bg-primary text-white shadow-sm' : 'bg-surface border border-border text-text-secondary hover:bg-background' }}">
            Hoy
        </a>

        <form method="GET" action="{{ route('doctor.agenda') }}" class="flex items-center gap-2 m-0">
            <div class="relative">
                <input type="date" id="inp_fecha_agenda" name="fecha" value="{{ $fecha ?? '' }}" onchange="this.form.submit()" class="px-3.5 py-2 bg-surface border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
            @if(!empty($fecha))
                <a href="{{ route('doctor.agenda') }}" class="p-2 text-text-muted hover:text-danger hover:bg-danger-light/50 rounded-xl transition-colors" title="Quitar filtro de fecha">
                    <span class="material-symbols-outlined text-lg">close</span>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Welcome Banner -->
<div class="rounded-2xl p-6 mb-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4" style="background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-container) 100%);">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-white/10 border border-white/20 flex items-center justify-center font-bold text-lg">
            {{ strtoupper(substr($perfilDoctor->usuario->nombre ?? Auth::user()->nombre, 0, 2)) }}
        </div>
        <div>
            <h3 class="font-bold text-lg">Hola, Dr. {{ Auth::user()->nombre }}</h3>
            <p class="text-xs text-white/70">
                @if(!empty($fecha))
                    Fecha seleccionada: {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM, YYYY') }}
                @else
                    Listado general de todas tus consultas asignadas
                @endif
            </p>
        </div>
    </div>
    <div class="bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 flex items-center gap-2 self-start md:self-auto">
        <span class="material-symbols-outlined text-secondary-light">event_note</span>
        <span class="text-xs font-semibold">{{ count($citas) }} consulta(s) {{ empty($fecha) ? 'en total' : 'en la jornada' }}</span>
    </div>
</div>

<!-- Agenda Timeline -->
<div class="relative timeline-line">
    @forelse($citas as $cita)
        @php
            $estado = strtolower($cita->estado);
            $dotClass = match($estado) {
                'completada' => 'bg-emerald-100 text-emerald-700 border-emerald-400',
                'en_consulta' => 'bg-sky-100 text-sky-700 border-sky-400',
                'cancelada' => 'bg-rose-100 text-rose-700 border-rose-400',
                'confirmada', 'agendada' => 'bg-amber-100 text-amber-700 border-amber-400',
                default => 'bg-surface-container-high text-text-muted border-border'
            };
            $cardClass = match($estado) {
                'completada' => 'border-l-4 border-emerald-500',
                'en_consulta' => 'border-l-4 border-sky-500 ring-2 ring-sky-200',
                'cancelada' => 'border-l-4 border-rose-400 opacity-75',
                'confirmada', 'agendada' => 'border-l-4 border-amber-400',
                default => 'border-l-4 border-border'
            };
            $badgeClass = match($estado) {
                'completada' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                'en_consulta' => 'bg-sky-100 text-sky-800 border border-sky-200',
                'cancelada' => 'bg-rose-100 text-rose-800 border border-rose-200',
                'confirmada', 'agendada' => 'bg-amber-100 text-amber-800 border border-amber-200',
                default => 'bg-background text-text-secondary border border-border'
            };
            $displayEstado = match($estado) {
                'completada' => 'Finalizada',
                'en_consulta' => 'En Consulta',
                'cancelada' => 'Cancelada',
                'confirmada' => 'Confirmada',
                'agendada' => 'Agendada',
                default => ucfirst($estado)
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
                        <div class="text-center min-w-[90px] flex-shrink-0 pr-3 border-r border-border/80">
                            <span class="inline-block px-2 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider mb-1">
                                {{ \Carbon\Carbon::parse($cita->fecha_cita)->format('d/m/Y') }}
                            </span>
                            <p class="text-base font-bold text-text-primary leading-tight">
                                {{ \Carbon\Carbon::parse($cita->hora_cita ?? $cita->fecha_hora)->format('h:i A') }}
                            </p>
                            <p class="text-[10px] text-text-muted mt-0.5">{{ $cita->duracion_minutos ?? 30 }} min</p>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-text-primary text-sm truncate">{{ $cita->perfilPaciente?->usuario?->nombre ?? 'Paciente' }}</h4>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeClass }}">{{ $displayEstado }}</span>
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
                            <a href="{{ route('doctor.diagnostico', $cita->id) }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">edit_note</span>
                                <span>Registrar Diagnóstico</span>
                            </a>
                        @elseif($estado === 'completada')
                            <a href="{{ route('citas.show', $cita->id) }}" class="px-4 py-2 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base">description</span>
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
            @if(!empty($fecha))
                No tienes consultas agendadas para el {{ \Carbon\Carbon::parse($fecha)->isoFormat('D [de] MMMM, YYYY') }}.
                <div class="mt-3">
                    <a href="{{ route('doctor.agenda') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary font-semibold rounded-lg hover:bg-primary/20 transition-all">
                        <span class="material-symbols-outlined text-sm">visibility</span>
                        <span>Ver todas las citas</span>
                    </a>
                </div>
            @else
                No tienes consultas agendadas en tu agenda médica.
            @endif
        </div>
    @endforelse
</div>
@endsection
