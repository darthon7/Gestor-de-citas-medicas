@extends('layouts.app')
@section('titulo', 'Panel Principal')

@section('content')
<!-- Top Header Info / Welcome -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Panel Principal</h1>
        <p class="text-xs text-text-secondary mt-1">Resumen diario de actividades y flujo de citas médicas</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('citas.index') }}" class="px-4 py-2.5 bg-surface border border-border text-primary font-semibold text-xs rounded-xl hover:bg-primary/5 transition-all shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">calendar_month</span>
            <span>Ver Calendario</span>
        </a>
        @if(in_array(Auth::user()->rol, ['admin', 'recepcionista', 'paciente']))
            <button type="button" onclick="abrirModalCita()" class="px-4 py-2.5 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition-all shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">add</span>
                <span>Nueva Cita</span>
            </button>
        @endif
    </div>
</div>

<!-- Row 1: Stat Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat 1: Citas del día -->
    <div class="bg-surface rounded-xl p-6 card-shadow border-l-4 border-primary transition-all card-shadow-hover">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-primary-fixed text-on-primary-fixed-variant rounded-lg">
                <span class="material-symbols-outlined text-2xl">event</span>
            </div>
            <span class="text-secondary font-semibold text-xs flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">trending_up</span> Hoy
            </span>
        </div>
        <p class="text-xs font-medium text-text-secondary mb-1">Citas del día</p>
        <h3 class="text-3xl font-bold text-primary-dark">{{ $statTotalDia }}</h3>
    </div>

    <!-- Stat 2: Completadas -->
    <div class="bg-surface rounded-xl p-6 card-shadow border-l-4 border-secondary transition-all card-shadow-hover">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-secondary-container text-on-secondary-container rounded-lg">
                <span class="material-symbols-outlined text-2xl">check_circle</span>
            </div>
            <span class="text-secondary font-semibold text-xs">Hoy</span>
        </div>
        <p class="text-xs font-medium text-text-secondary mb-1">Completadas</p>
        <h3 class="text-3xl font-bold text-primary-dark">{{ $statCompletadas }}</h3>
    </div>

    <!-- Stat 3: Pendientes -->
    <div class="bg-surface rounded-xl p-6 card-shadow border-l-4 border-tertiary-fixed-dim transition-all card-shadow-hover">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-tertiary-fixed text-on-tertiary-fixed-variant rounded-lg">
                <span class="material-symbols-outlined text-2xl">pending_actions</span>
            </div>
            <span class="text-tertiary font-semibold text-xs">En espera</span>
        </div>
        <p class="text-xs font-medium text-text-secondary mb-1">Pendientes</p>
        <h3 class="text-3xl font-bold text-primary-dark">{{ $statPendientes }}</h3>
    </div>

    <!-- Stat 4: Canceladas -->
    <div class="bg-surface rounded-xl p-6 card-shadow border-l-4 border-danger transition-all card-shadow-hover">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-danger-light text-danger rounded-lg">
                <span class="material-symbols-outlined text-2xl">cancel</span>
            </div>
            <span class="text-danger font-semibold text-xs">Canceladas</span>
        </div>
        <p class="text-xs font-medium text-text-secondary mb-1">Canceladas hoy</p>
        <h3 class="text-3xl font-bold text-primary-dark">{{ $statCanceladas }}</h3>
    </div>
</div>

<!-- Row 2: Agenda del Día -->
<div class="bg-surface rounded-xl card-shadow overflow-hidden border border-border">
    <div class="px-6 py-4 border-b border-border flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-text-primary">Agenda del Día</h2>
            <p class="text-xs text-text-secondary">{{ \Carbon\Carbon::now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-lg">search</span>
                <input id="buscadorPaciente" type="text" placeholder="Buscar paciente..."
                    class="pl-10 pr-4 py-2 bg-background border border-border rounded-lg text-sm focus:ring-2 focus:ring-primary-light transition-all w-full sm:w-64">
            </div>
            <button class="p-2 rounded-lg bg-background border border-border hover:bg-surface-container-high transition-colors" title="Filtrar">
                <span class="material-symbols-outlined text-on-surface-variant">filter_list</span>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-background/60 border-b border-border text-xs font-semibold text-text-secondary uppercase tracking-wider">
                    <th class="px-6 py-4">Hora</th>
                    <th class="px-6 py-4">Paciente</th>
                    <th class="px-6 py-4">Doctor</th>
                    <th class="px-6 py-4">Especialidad</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-sm" id="agendaBody">
                @forelse($citasHoy as $cita)
                    <tr class="hover:bg-surface-container-low transition-colors group" data-paciente="{{ strtolower($cita->perfilPaciente?->usuario?->nombre ?? '') }}">
                        <td class="px-6 py-4 font-semibold text-primary whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('h:i A') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-fixed text-primary-dark font-bold text-xs flex items-center justify-center">
                                    {{ strtoupper(substr($cita->perfilPaciente?->usuario?->nombre ?? 'P', 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-text-primary text-xs leading-tight">{{ $cita->perfilPaciente?->usuario?->nombre ?? 'N/A' }}</p>
                                    <p class="text-[11px] text-text-secondary">Exp: {{ $cita->perfilPaciente?->numero_expediente ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-text-secondary font-medium whitespace-nowrap">
                            Dr. {{ $cita->perfilDoctor?->usuario?->nombre ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-xs whitespace-nowrap">
                            {{ $cita->especialidad?->nombre ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $badge = match(strtolower($cita->estado)) {
                                    'confirmada', 'completada' => ['bg-secondary-light/30 text-secondary border-secondary/30', 'Confirmada'],
                                    'en_consulta' => ['bg-primary-light/20 text-primary border-primary/20', 'En consulta'],
                                    'agendada', 'pendiente' => ['bg-tertiary-fixed/20 text-tertiary border-tertiary/20', 'Pendiente'],
                                    'cancelada' => ['bg-danger-light/30 text-danger border-danger/20', 'Cancelada'],
                                    default => ['bg-gray-50 text-gray-700 border-gray-200', ucfirst($cita->estado)]
                                };
                            @endphp
                            <span class="flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold capitalize border {{ $badge[0] }}">
                                @if(strtolower($cita->estado) === 'en_consulta')
                                    <span class="w-2 h-2 bg-primary rounded-full status-dot-pulse"></span>
                                @endif
                                {{ $badge[1] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('citas.show', $cita->id) }}" class="p-1.5 rounded-lg text-text-muted hover:text-primary hover:bg-background transition-colors inline-block" title="Ver Detalle">
                                <span class="material-symbols-outlined text-lg">eye</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-xs text-text-muted">
                            <span class="material-symbols-outlined text-3xl mb-1 text-text-muted block">event_busy</span>
                            No hay citas agendadas para el día de hoy.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 bg-surface-container-low border-t border-border flex flex-col sm:flex-row items-center justify-between gap-3">
        <span class="text-xs text-text-secondary">
            Mostrando {{ $citasHoy->firstItem() ?? 0 }}–{{ $citasHoy->lastItem() ?? 0 }} de {{ $citasHoy->total() }} citas del día
        </span>
        <div class="flex items-center gap-1">
            <a href="{{ $citasHoy->previousPageUrl() ?? '#' }}" class="p-1 rounded hover:bg-border transition-colors {{ $citasHoy->onFirstPage() ? 'opacity-40 pointer-events-none' : '' }}">
                <span class="material-symbols-outlined">chevron_left</span>
            </a>
            @for($i = 1; $i <= $citasHoy->lastPage(); $i++)
                <a href="{{ $citasHoy->url($i) }}" class="px-3 py-1 rounded text-sm font-semibold {{ $i === $citasHoy->currentPage() ? 'bg-primary text-on-primary' : 'hover:bg-border text-text-secondary' }}">
                    {{ $i }}
                </a>
            @endfor
            <a href="{{ $citasHoy->nextPageUrl() ?? '#' }}" class="p-1 rounded hover:bg-border transition disabled {{ $citasHoy->hasMorePages() ? '' : 'opacity-40 pointer-events-none' }}">
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        </div>
    </div>
</div>

@include('components.modal-nueva-cita')
@endsection

@section('scripts')
<script>
    const searchInput = document.getElementById('buscadorPaciente');
    if (searchInput) {
        const agendaRows = document.querySelectorAll('#agendaBody tr[data-paciente]');
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            agendaRows.forEach(row => {
                row.style.display = row.dataset.paciente.includes(q) ? '' : 'none';
            });
        });
    }
</script>
@endsection