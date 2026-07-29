@extends('layouts.app')
@section('titulo', 'Calendario de Citas')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/pages/citas.css') }}">
@endsection

@section('content')
<header class="header">
    <h1 class="header__title">Calendario de Citas</h1>
</header>

<main class="main-content">
    <!-- Control Bar -->
    <div class="calendar-controls">
        <form method="GET" action="{{ route('citas.index') }}" id="filter_form" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <input type="hidden" name="fecha" value="{{ $fechaRef->format('Y-m-d') }}">

            <div style="display: flex; align-items: center; gap: 16px;">
                <a href="{{ route('citas.index', ['fecha' => $fechaRef->copy()->subWeek()->format('Y-m-d'), 'doctor_id' => $doctorId]) }}" class="btn btn-icon">
                    <i data-lucide="chevron-left"></i>
                </a>
                <span style="font-weight: 700; font-size: 16px; min-width: 240px; text-align: center;">
                    {{ $startOfWeek->isoFormat('D [de] MMM') }} - {{ $endOfWeek->isoFormat('D [de] MMM, YYYY') }}
                </span>
                <a href="{{ route('citas.index', ['fecha' => $fechaRef->copy()->addWeek()->format('Y-m-d'), 'doctor_id' => $doctorId]) }}" class="btn btn-icon">
                    <i data-lucide="chevron-right"></i>
                </a>
            </div>

            <div style="display: flex; gap: 12px;">
                <select name="doctor_id" onchange="document.getElementById('filter_form').submit();" class="form-select" style="width: 220px;">
                    <option value="">Todos los doctores</option>
                    @foreach($doctores as $doc)
                        <option value="{{ $doc['id'] }}" {{ $doctorId == $doc['id'] ? 'selected' : '' }}>
                            Dr. {{ $doc['nombre'] }}
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('citas.crear') }}" class="btn btn-primary">
                    <i data-lucide="plus"></i> Nueva Cita
                </a>
            </div>
        </form>
    </div>

    <!-- Calendar Main Grid & Right Summary Layout -->
    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 24px;">
        <!-- Calendar Grid -->
        <div id="calendar_grid" class="calendar-grid">
            <div class="calendar-header-col" style="background-color: var(--color-bg);">Hora</div>
            @for($i = 0; $i < 7; $i++)
                @php
                    $dayDate = $startOfWeek->copy()->addDays($i);
                @endphp
                <div class="calendar-header-col {{ $dayDate->isToday() ? 'calendar-header-col--today' : '' }}">
                    <div>{{ ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'][$i] }}</div>
                    <div style="font-size: 11px; font-weight: 500;">{{ $dayDate->format('d/m') }}</div>
                </div>
            @endfor

            <!-- Hora 8:00 - 18:00 -->
            <div class="calendar-time-col">
                @for($h = 8; $h <= 18; $h++)
                    <div class="calendar-time-slot-label">
                        {{ $h < 12 ? "{$h}:00 AM" : ($h == 12 ? "12:00 PM" : ($h-12) . ":00 PM") }}
                    </div>
                @endfor
            </div>

            @for($i = 0; $i < 7; $i++)
                @php
                    $currentColDate = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
                @endphp
                <div class="calendar-day-col" id="day_col_{{ $i }}" style="position: relative;">
                    @foreach($citas as $cita)
                        @php
                            $citaCarbon = \Carbon\Carbon::parse($cita['fecha_hora']);
                        @endphp
                        @if($citaCarbon->format('Y-m-d') === $currentColDate)
                            @php
                                $hour = (int)$citaCarbon->format('H');
                                $min = (int)$citaCarbon->format('i');
                                $topPx = (($hour - 8) * 60) + $min;
                                $estado = strtolower($cita['estado'] ?? 'pendiente');
                            @endphp
                            <a href="{{ route('citas.show', $cita['id']) }}"
                               class="appointment-block appointment-block--{{ $estado }}"
                               style="top: {{ $topPx }}px; height: 52px; text-decoration: none;"
                               title="Ver Cita #{{ $cita['id'] }}">
                                <strong style="font-size:10px;">{{ $citaCarbon->format('h:i A') }}</strong>
                                <span style="font-weight:600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $cita['paciente']['nombre'] ?? 'Paciente' }}</span>
                                <span style="font-size:10px; opacity:0.8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Dr. {{ $cita['doctor']['nombre'] ?? 'Médico' }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endfor
        </div>

        <!-- Right Summary Panel -->
        <div class="card" style="height: fit-content; padding: 20px;">
            <h3 style="margin-bottom: 16px; font-size: 16px;">Resumen de la Semana</h3>
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; font-size: 13px;">
                    <span>Citas programadas:</span>
                    <strong>{{ count($citas) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;">
                    <span>Confirmadas/Completadas:</span>
                    <strong style="color: var(--color-secondary);">
                        {{ count(array_filter($citas, fn($c) => in_array(strtolower($c['estado']), ['confirmada', 'completada']))) }}
                    </strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;">
                    <span>Canceladas:</span>
                    <strong style="color: var(--color-danger);">
                        {{ count(array_filter($citas, fn($c) => strtolower($c['estado']) === 'cancelada')) }}
                    </strong>
                </div>
            </div>

            <h4 style="margin-bottom: 12px; font-size: 14px;">Próximas Citas</h4>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse(array_slice($citas, 0, 5) as $cita)
                    <a href="{{ route('citas.show', $cita['id']) }}" style="background-color: var(--color-bg); padding: 10px; border-radius: 6px; border: 1px solid var(--color-border); display:flex; justify-content:space-between; align-items:center; text-decoration: none; color: inherit;">
                        <div>
                            <strong style="font-size:13px; display: block;">{{ $cita['paciente']['nombre'] ?? 'Paciente' }}</strong>
                            <div style="font-size:11px; color:var(--color-text-secondary);">Dr. {{ $cita['doctor']['nombre'] ?? 'Médico' }}</div>
                        </div>
                        <span style="font-weight:700; font-size:12px; color:var(--color-primary);">{{ \Carbon\Carbon::parse($cita['fecha_hora'])->format('d/M H:i') }}</span>
                    </a>
                @empty
                    <p style="color:var(--color-text-muted); font-size:12px;">Sin citas para esta semana.</p>
                @endforelse
            </div>
        </div>
    </div>
</main>
@endsection
