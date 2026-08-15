@extends('layouts.app')
@section('titulo', 'Calendario de Citas')

@section('styles')
<style>
    .calendar-grid-wrapper {
        display: grid;
        grid-template-columns: 65px repeat(7, 1fr);
    }
    .time-slot-line {
        height: 48px;
        border-bottom: 1px solid #E2E8F0;
    }
    .appointment-block {
        position: absolute;
        border-radius: 6px;
        padding: 3px 6px;
        font-size: 11px;
        overflow: hidden;
    }
</style>
@endsection

@section('content')
@php
    $fechaRef = $fechaRef ?? \Carbon\Carbon::now();
    $mesRef = $fechaRef->copy()->startOfMonth();
    $primerDiaCuadricula = $mesRef->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $ultimoDiaMes = $fechaRef->copy()->endOfMonth();
    $vista = $vista ?? 'semana';

    $modos = [
        'dia'    => 'Día',
        'semana' => 'Semana',
        'mes'    => 'Mes',
    ];

    // Navegacion prev/next segun modo
    $fechaPrev = $fechaRef->copy()->subDay();
    $fechaNext = $fechaRef->copy()->addDay();
    if ($vista === 'semana') { $fechaPrev = $fechaRef->copy()->subWeek(); $fechaNext = $fechaRef->copy()->addWeek(); }
    if ($vista === 'mes') { $fechaPrev = $fechaRef->copy()->subMonth(); $fechaNext = $fechaRef->copy()->addMonth(); }

    $tituloRango = match($vista) {
        'dia' => $fechaRef->isoFormat('D [de] MMMM, YYYY'),
        'mes' => $fechaRef->isoFormat('MMMM YYYY'),
        default => $startOfWeek->isoFormat('D [—] D [de] MMM, YYYY'),
    };

    // Proyección mínima de doctores para el cliente (mapeo corregido: nombre en usuario)
    $doctoresLista = $doctores instanceof \Illuminate\Contracts\Pagination\Paginator
        ? $doctores->items()
        : $doctores;

    $doctoresJson = collect($doctoresLista)->map(function ($d) {
        $especialidades = collect(is_object($d) ? ($d->especialidades ?? []) : ($d['especialidades'] ?? []));
        $nombre = is_object($d)
            ? ($d->usuario?->nombre ?? ($d->nombre ?? 'Médico'))
            : ($d['usuario']['nombre'] ?? ($d['nombre'] ?? 'Médico'));

        return [
            'id'                  => is_object($d) ? $d->id : ($d['id'] ?? null),
            'nombre'              => $nombre,
            'especialidad_nombre' => $especialidades->first()['nombre'] ?? ($especialidades->first()->nombre ?? 'General'),
            'especialidades'      => $especialidades->pluck('id'),
            'estado_validacion'   => is_object($d) ? ($d->estado_validacion ?? 'pendiente') : ($d['estado_validacion'] ?? 'pendiente'),
        ];
    })->values();
@endphp

<!-- Control Bar -->
<section class="bg-surface rounded-2xl card-shadow border border-border p-3.5 flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center bg-background p-1 rounded-lg border border-border">
            @foreach($modos as $clave => $etiqueta)
                @if($vista === $clave)
                    <span class="px-3.5 py-1.5 rounded-md text-xs font-semibold bg-primary text-white shadow-sm">{{ $etiqueta }}</span>
                @else
                    <a href="{{ route('citas.index', ['vista' => $clave, 'fecha' => $fechaRef->format('Y-m-d'), 'doctor_id' => $doctorId]) }}" class="px-3.5 py-1.5 rounded-md text-xs font-medium text-text-secondary hover:bg-surface transition-all">{{ $etiqueta }}</a>
                @endif
            @endforeach
        </div>
        <div class="flex items-center gap-2 bg-surface p-0.5 rounded-lg border border-border">
            <a href="{{ route('citas.index', ['vista' => $vista, 'fecha' => $fechaPrev->format('Y-m-d'), 'doctor_id' => $doctorId]) }}" class="p-1.5 rounded-lg hover:bg-background text-text-secondary transition-all" title="Anterior">
                <span class="material-symbols-outlined text-lg">chevron_left</span>
            </a>
            <h2 class="font-bold text-primary-dark text-xs sm:text-sm px-1 whitespace-nowrap capitalize">
                {{ $tituloRango }}
            </h2>
            <a href="{{ route('citas.index', ['vista' => $vista, 'fecha' => $fechaNext->format('Y-m-d'), 'doctor_id' => $doctorId]) }}" class="p-1.5 rounded-lg hover:bg-background text-text-secondary transition-all" title="Siguiente">
                <span class="material-symbols-outlined text-lg">chevron_right</span>
            </a>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <!-- Doctor Filter -->
        <form method="GET" action="{{ route('citas.index') }}" id="filter_form" class="flex items-center gap-3">
            <input type="hidden" name="fecha" value="{{ $fechaRef->format('Y-m-d') }}">
            <input type="hidden" name="vista" value="{{ $vista }}">
            <div class="relative">
                <select name="doctor_id" id="sel_filtro_doctor" onchange="document.getElementById('filter_form').submit();" class="appearance-none pl-3.5 pr-9 py-1.5 bg-surface border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all cursor-pointer">
                    <option value="">Todos los doctores</option>
                </select>
                <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-text-muted text-lg">expand_more</span>
            </div>
        </form>

        @if(in_array(Auth::user()->rol, ['admin', 'recepcionista', 'paciente']))
            <button type="button" onclick="abrirModalCita()" class="flex items-center gap-1.5 bg-primary text-white px-4 py-1.5 rounded-xl font-semibold text-xs hover:bg-primary-dark shadow-md active:scale-95 transition-all">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Nueva Cita</span>
            </button>
        @endif
    </div>
</section>

<!-- Calendar Layout Grid & Summary Side Panel -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-5 items-start">
    <!-- Main Calendar Grid (3 Cols) -->
    <div class="lg:col-span-3 bg-surface rounded-2xl card-shadow border border-border overflow-hidden flex flex-col">
        @if($vista === 'mes')
            <!-- ===== VISTA MES ===== -->
            <div class="grid grid-cols-7 border-b border-border bg-background/70 text-center">
                @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dayLabel)
                    <div class="h-9 flex items-center justify-center text-[10px] uppercase font-bold tracking-wider text-text-secondary border-r border-border last:border-r-0">{{ $dayLabel }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7">
                @for($d = 0; $d < $mesRef->diffInDays($primerDiaCuadricula); $d++)
                    <div class="min-h-[75px] border-r border-b border-border bg-background/30"></div>
                @endfor
                @for($day = $mesRef->copy(); $day->lte($mesRef->copy()->endOfMonth()); $day->addDay())
                    @php
                        $citasDia = $citas->filter(fn($c) => \Carbon\Carbon::parse($c['fecha_hora'])->format('Y-m-d') === $day->format('Y-m-d'));
                    @endphp
                    <a href="{{ route('citas.index', ['vista' => 'dia', 'fecha' => $day->format('Y-m-d'), 'doctor_id' => $doctorId]) }}"
                       class="min-h-[75px] border-r border-b border-border last:border-r-0 p-1 flex flex-col gap-0.5 transition-colors hover:bg-background/60 {{ $day->isToday() ? 'bg-primary/5' : '' }}">
                        <span class="text-[10px] font-bold px-0.5 {{ $day->isToday() ? 'text-primary' : 'text-text-primary' }}">{{ $day->format('d') }}</span>
                        <div class="flex flex-col gap-0.5 overflow-hidden">
                            @foreach($citasDia->take(2) as $cita)
                                @php
                                    $estadoM = strtolower($cita['estado'] ?? 'pendiente');
                                    $chip = match($estadoM) {
                                        'confirmada', 'completada' => 'bg-emerald-100 text-emerald-800',
                                        'en_consulta' => 'bg-sky-100 text-sky-800',
                                        'agendada', 'pendiente' => 'bg-amber-100 text-amber-800',
                                        'cancelada' => 'bg-rose-100 text-rose-800 opacity-60',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                @endphp
                                <span class="truncate rounded px-1 py-0.5 text-[8.5px] font-medium {{ $chip }}" title="{{ \Carbon\Carbon::parse($cita['fecha_hora'])->format('H:i') }} - {{ $cita['perfilPaciente']['usuario']['nombre'] ?? '' }}">
                                    {{ \Carbon\Carbon::parse($cita['fecha_hora'])->format('H:i') }} {{ $cita['perfilPaciente']['usuario']['nombre'] ?? '' }}
                                </span>
                            @endforeach
                            @if($citasDia->count() > 2)
                                <span class="text-[8.5px] text-text-muted font-semibold pl-1">+{{ $citasDia->count() - 2 }} más</span>
                            @endif
                        </div>
                    </a>
                @endfor
            </div>
        @else
            <!-- ===== VISTA DÍA / SEMANA ===== -->
            @php
                $numCols = $vista === 'dia' ? 1 : 7;
            @endphp
            <!-- Days Header -->
            <div class="calendar-grid-wrapper border-b border-border bg-background/70">
                <div class="h-10 flex items-center justify-center border-r border-border text-text-muted">
                    <span class="material-symbols-outlined text-base">schedule</span>
                </div>

                @for($i = 0; $i < $numCols; $i++)
                    @php
                        $dayDate = $startOfWeek->copy()->addDays($i);
                        $isToday = $dayDate->isToday();
                    @endphp
                    <div class="h-10 flex flex-col items-center justify-center border-r border-border last:border-r-0 {{ $isToday ? 'bg-primary/10 text-primary' : 'text-text-primary' }}">
                        <span class="text-[9px] uppercase font-bold tracking-wider {{ $isToday ? 'text-primary' : 'text-text-secondary' }}">
                            {{ ['LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB', 'DOM'][$i] }}
                        </span>
                        <span class="text-[11px] font-bold leading-none">{{ $dayDate->format('d/m') }}</span>
                    </div>
                @endfor
            </div>

            <!-- Scrollable Hours Body -->
            <div class="max-h-[480px] overflow-y-auto relative custom-scrollbar">
                <div class="calendar-grid-wrapper relative">
                    <!-- Hour Labels (Left Column) -->
                    <div class="border-r border-border bg-background/40">
                        @for($h = 8; $h <= 18; $h++)
                            <div class="time-slot-line flex items-center justify-center text-[9px] font-semibold text-text-muted">
                                {{ $h < 12 ? "{$h}:00 AM" : ($h == 12 ? "12:00 PM" : ($h-12) . ":00 PM") }}
                            </div>
                        @endfor
                    </div>

                    <!-- Grid Slots & Appointment Blocks -->
                    @for($i = 0; $i < $numCols; $i++)
                        @php
                            $currentColDate = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
                        @endphp
                        <div class="relative border-r border-border last:border-r-0">
                            @for($h = 8; $h <= 18; $h++)
                                <div class="time-slot-line"></div>
                            @endfor

                            @foreach($citas as $cita)
                                @php
                                    $citaCarbon = \Carbon\Carbon::parse($cita['fecha_hora']);
                                @endphp
                                @if($citaCarbon->format('Y-m-d') === $currentColDate)
                                    @php
                                        $hour = (int)$citaCarbon->format('H');
                                        $min = (int)$citaCarbon->format('i');
                                        $topPx = (($hour - 8) * 48) + round($min * 48 / 60);
                                        $estado = strtolower($cita['estado'] ?? 'pendiente');
                                        $blockClass = match($estado) {
                                            'confirmada', 'completada' => 'bg-emerald-50 text-emerald-900 border-l-4 border-emerald-500 shadow-sm',
                                            'en_consulta' => 'bg-sky-50 text-sky-900 border-l-4 border-sky-500 shadow-sm',
                                            'agendada', 'pendiente' => 'bg-amber-50 text-amber-900 border-l-4 border-amber-500 shadow-sm',
                                            'cancelada' => 'bg-rose-50 text-rose-900 border-l-4 border-rose-400 opacity-60',
                                            default => 'bg-gray-50 text-gray-900 border-l-4 border-gray-400'
                                        };
                                    @endphp
                                    <a href="{{ route('citas.show', $cita['id']) }}" class="appointment-block left-1 right-1 p-1.5 rounded-lg text-[11px] {{ $blockClass }} transition-all hover:scale-[1.02] hover:z-10"
                                       style="top: {{ $topPx }}px; height: 42px;"
                                       title="Ver Cita #{{ $cita['id'] }}">
                                        <div class="flex items-center justify-between leading-none">
                                            <strong class="text-[9.5px] font-bold tracking-tight">{{ $citaCarbon->format('h:i A') }}</strong>
                                            <span class="text-[8.5px] uppercase font-semibold opacity-75">{{ $cita['estado'] }}</span>
                                        </div>
                                        <p class="font-bold text-[10.5px] truncate leading-tight mt-0.5">{{ $cita['perfilPaciente']['usuario']['nombre'] ?? 'Paciente' }}</p>
                                        <p class="text-[9px] opacity-80 truncate leading-none">Dr. {{ $cita['perfilDoctor']['usuario']['nombre'] ?? 'Médico' }}</p>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endfor
                </div>
            </div>
        @endif
    </div>

    <!-- Summary Side Panel (1 Col) - Orden: Mini Calendario > Próximas del Mes > Resumen del Mes -->
    <div class="space-y-4">
        <!-- 1. Mini Calendario -->
        <div class="bg-surface rounded-2xl shadow-sm border border-border p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="font-bold text-text-primary text-xs capitalize">{{ $fechaRef->isoFormat('MMMM YYYY') }}</span>
                <div class="flex items-center gap-1 text-text-secondary">
                    <a href="{{ route('citas.index', ['vista' => $vista, 'fecha' => $mesRef->copy()->subMonth()->format('Y-m-d'), 'doctor_id' => $doctorId]) }}" class="p-1 rounded-md hover:bg-background transition-colors" title="Mes anterior">
                        <span class="material-symbols-outlined text-sm cursor-pointer hover:text-primary">chevron_left</span>
                    </a>
                    <a href="{{ route('citas.index', ['vista' => $vista, 'fecha' => $fechaRef->copy()->addMonth()->format('Y-m-d'), 'doctor_id' => $doctorId]) }}" class="p-1 rounded-md hover:bg-background transition-colors" title="Mes siguiente">
                        <span class="material-symbols-outlined text-sm cursor-pointer hover:text-primary">chevron_right</span>
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center">
                @foreach(['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $dayLabel)
                    <span class="text-[10px] text-text-muted font-bold">{{ $dayLabel }}</span>
                @endforeach

                @for($d = 0; $d < $mesRef->diffInDays($primerDiaCuadricula); $d++)
                    <span class="text-[10px] py-1 text-text-muted/30"></span>
                @endfor

                @for($day = $primerDiaCuadricula->copy(); $day->lte($ultimoDiaMes); $day->addDay())
                    @php
                        $esHoy = $day->isToday();
                        $esSeleccionado = $day->format('Y-m-d') === $fechaRef->format('Y-m-d');
                    @endphp
                    <a href="{{ route('citas.index', ['vista' => $vista, 'fecha' => $day->format('Y-m-d'), 'doctor_id' => $doctorId]) }}"
                       class="text-[10px] sm:text-[11px] py-1 rounded-lg transition-all flex items-center justify-center font-medium
                        {{ $esHoy ? 'bg-primary text-white font-bold shadow-sm' : ($esSeleccionado ? 'bg-primary/15 text-primary font-bold' : ($day->month != $fechaRef->month ? 'text-text-muted/40 hover:bg-background' : 'hover:bg-background text-text-primary')) }}">
                        {{ $day->day }}
                    </a>
                @endfor
            </div>
        </div>

        <!-- 2. Próximas Citas del Rango -->
        <div class="bg-surface rounded-2xl shadow-sm border border-border p-4">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-border">
                <h3 class="font-bold text-text-primary text-xs">Próximas {{ $vista === 'mes' ? 'del Mes' : 'en la ' . ($vista === 'dia' ? 'Fecha' : 'Semana') }}</h3>
                <span class="text-[10px] font-semibold text-text-muted bg-background px-2 py-0.5 rounded-full">{{ $citas->count() }}</span>
            </div>
            <div class="space-y-2 max-h-[220px] overflow-y-auto custom-scrollbar pr-0.5">
                @forelse($citas->slice(0, 5) as $cita)
                    <a href="{{ route('citas.show', $cita['id']) }}" class="block p-2.5 bg-background hover:bg-primary/5 hover:border-primary/30 rounded-xl border border-border transition-all">
                        <div class="flex items-center justify-between gap-1">
                            <strong class="text-xs text-text-primary truncate block">{{ $cita['perfilPaciente']['usuario']['nombre'] ?? 'Paciente' }}</strong>
                            <span class="text-[10px] font-bold text-primary whitespace-nowrap">{{ \Carbon\Carbon::parse($cita['fecha_hora'])->format('d/M H:i') }}</span>
                        </div>
                        <p class="text-[10px] text-text-secondary truncate mt-0.5">Dr. {{ $cita['perfilDoctor']['usuario']['nombre'] ?? 'Médico' }}</p>
                    </a>
                @empty
                    <p class="text-xs text-text-muted py-3 text-center">Sin citas para este período.</p>
                @endforelse
            </div>
        </div>

        <!-- 3. Resumen Estadístico -->
        <div class="bg-surface rounded-2xl shadow-sm border border-border p-4">
            <h3 class="font-bold text-text-primary text-xs mb-3 pb-2 border-b border-border flex items-center justify-between">
                <span>Resumen del {{ $vista === 'dia' ? 'Día' : ($vista === 'mes' ? 'Mes' : 'Semana') }}</span>
                <span class="material-symbols-outlined text-text-muted text-base">assessment</span>
            </h3>

            <div class="space-y-2">
                <div class="bg-background p-2.5 rounded-xl flex items-center justify-between border border-border">
                    <div>
                        <p class="text-[11px] text-text-secondary font-medium">Programadas</p>
                        <p class="text-base font-bold text-primary">{{ $citas->count() }} Citas</p>
                    </div>
                    <span class="material-symbols-outlined text-primary/40 text-xl">event</span>
                </div>

                <div class="bg-emerald-50 p-2.5 rounded-xl flex items-center justify-between border border-emerald-200/60">
                    <div>
                        <p class="text-[11px] text-emerald-700 font-medium">Confirmadas</p>
                        <p class="text-base font-bold text-emerald-700">
                            {{ $citas->filter(fn($c) => in_array(strtolower($c['estado'] ?? 'pendiente'), ['confirmada', 'completada']))->count() }}
                            <span class="text-[10px] font-medium">completadas</span>
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-emerald-500/40 text-xl">check_circle</span>
                </div>

                <div class="bg-rose-50 p-2.5 rounded-xl flex items-center justify-between border border-rose-200/60">
                    <div>
                        <p class="text-[11px] text-rose-700 font-medium">Alertas</p>
                        <p class="text-base font-bold text-rose-600">
                            {{ $citas->filter(fn($c) => strtolower($c['estado'] ?? 'pendiente') === 'cancelada')->count() }}
                            <span class="text-[10px] font-medium">canceladas</span>
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-rose-500/40 text-xl">cancel</span>
                </div>
            </div>
        </div>
    </div>
</div>

@include('components.modal-nueva-cita')
@endsection

@section('scripts')
<script>
    // --- Filtro de doctores: solo validados (filtro en estado local, sin backend) ---
    const doctoresCitas = @json($doctoresJson);
    const doctorIdActivo = @json($doctorId);

    (function () {
        const select = document.getElementById('sel_filtro_doctor');
        if (!select) return;

        function formatearNombreDoctor(nombre) {
            const nom = (nombre || 'Médico').trim();
            return /^(dr|dra)\.?\s/i.test(nom) ? nom : 'Dr. ' + nom;
        }

        const visibles = doctoresCitas.filter(d => d.estado_validacion === 'validado');
        const opciones = visibles.map(d => '<option value="' + d.id + '">' + formatearNombreDoctor(d.nombre) + '</option>');

        if (doctorIdActivo && !visibles.some(d => String(d.id) === String(doctorIdActivo))) {
            const activo = doctoresCitas.find(d => String(d.id) === String(doctorIdActivo));
            if (activo) opciones.push('<option value="' + activo.id + '">' + formatearNombreDoctor(activo.nombre) + '</option>');
        }

        select.insertAdjacentHTML('beforeend', opciones.join(''));
        if (doctorIdActivo) select.value = String(doctorIdActivo);
    })();
</script>
@endsection