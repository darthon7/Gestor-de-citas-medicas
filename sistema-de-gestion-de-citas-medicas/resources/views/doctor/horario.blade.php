@extends('layouts.app')
@section('titulo', 'Mi Horario de Atención')

@section('content')
<!-- Header Controls -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-2xl">schedule</span>
            <h1 class="text-2xl font-bold text-primary-dark">Mi Horario de Atención</h1>
        </div>
        <p class="text-xs text-text-secondary mt-1">
            Configura tu disponibilidad y turnos de consulta para cada día de la semana.
        </p>
    </div>

    <div class="flex items-center gap-3">
        <button type="button" onclick="abrirModalHorario()" class="px-4 py-2.5 bg-primary text-white rounded-xl font-semibold text-xs shadow-md hover:bg-primary-dark transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">add</span>
            <span>Agregar Horario</span>
        </button>
    </div>
</div>

<!-- Summary Metrics Cards -->
@php
    $diasUnicos = $horarios->pluck('dia_semana')->unique()->count();
    $totalTurnos = $horarios->count();
    $turnosActivos = $horarios->where('activo', true)->count();
    $duracionPromedio = $horarios->avg('duracion_consulta_minutos') ?: 30;
@endphp
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-surface rounded-2xl p-4 card-shadow border border-border flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">calendar_month</span>
        </div>
        <div>
            <p class="text-[11px] font-medium text-text-secondary">Días Laborables</p>
            <h3 class="text-lg font-bold text-text-primary">{{ $diasUnicos }} / 7 días</h3>
        </div>
    </div>

    <div class="bg-surface rounded-2xl p-4 card-shadow border border-border flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">timelapse</span>
        </div>
        <div>
            <p class="text-[11px] font-medium text-text-secondary">Turnos Activos</p>
            <h3 class="text-lg font-bold text-emerald-700">{{ $turnosActivos }} franja(s)</h3>
        </div>
    </div>

    <div class="bg-surface rounded-2xl p-4 card-shadow border border-border flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">timer</span>
        </div>
        <div>
            <p class="text-[11px] font-medium text-text-secondary">Duración por Consulta</p>
            <h3 class="text-lg font-bold text-sky-800">{{ round($duracionPromedio) }} min</h3>
        </div>
    </div>
</div>

<!-- Main Weekly Schedule Board -->
<div class="bg-surface rounded-2xl card-shadow border border-border p-5">
    <div class="flex items-center justify-between pb-3 mb-5 border-b border-border">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">view_week</span>
            <h2 class="font-bold text-text-primary text-base">Disponibilidad Semanal</h2>
        </div>
        <span class="text-xs text-text-muted">Horas de atención disponibles para reservaciones</span>
    </div>

    @php
        $diasNombres = [
            'lunes'     => ['nombre' => 'Lunes', 'abr' => 'LUN'],
            'martes'    => ['nombre' => 'Martes', 'abr' => 'MAR'],
            'miercoles' => ['nombre' => 'Miércoles', 'abr' => 'MIÉ'],
            'jueves'    => ['nombre' => 'Jueves', 'abr' => 'JUE'],
            'viernes'   => ['nombre' => 'Viernes', 'abr' => 'VIE'],
            'sabado'    => ['nombre' => 'Sábado', 'abr' => 'SÁB'],
            'domingo'   => ['nombre' => 'Domingo', 'abr' => 'DOM'],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3.5">
        @foreach($diasNombres as $claveDia => $infoDia)
            @php
                $horariosDelDia = $horarios->filter(fn($h) => ($h['dia_semana'] ?? '') === $claveDia);
            @endphp
            <div class="rounded-xl border border-border bg-background/50 p-3.5 flex flex-col justify-between min-h-[200px] transition-all hover:border-primary/40 hover:bg-surface">
                <div>
                    <!-- Encabezado del Día -->
                    <div class="flex items-center justify-between pb-2 border-b border-border/80 mb-2.5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $horariosDelDia->isNotEmpty() ? 'bg-primary' : 'bg-zinc-300' }}"></span>
                            <strong class="text-xs font-bold text-text-primary">{{ $infoDia['nombre'] }}</strong>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $horariosDelDia->isNotEmpty() ? 'bg-primary/10 text-primary font-bold' : 'bg-zinc-100 text-zinc-500' }}">
                            {{ $horariosDelDia->count() }} {{ $horariosDelDia->count() === 1 ? 'turno' : 'turnos' }}
                        </span>
                    </div>

                    <!-- Listado de franjas -->
                    <div class="space-y-2">
                        @forelse($horariosDelDia as $h)
                            <div class="p-2.5 bg-surface rounded-xl border border-border hover:border-primary/30 relative group shadow-sm transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1 text-[11px] font-bold text-primary">
                                        <span class="material-symbols-outlined text-xs">schedule</span>
                                        <span>{{ \Carbon\Carbon::parse($h['hora_inicio'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($h['hora_fin'])->format('h:i A') }}</span>
                                    </div>
                                    
                                    <!-- Acciones rápidas -->
                                    <div class="flex items-center gap-0.5">
                                        <button type="button" onclick="editarHorario({{ json_encode($h) }})" class="p-1 text-text-muted hover:text-primary hover:bg-primary/10 rounded transition-colors" title="Editar">
                                            <span class="material-symbols-outlined text-xs">edit</span>
                                        </button>
                                        <form method="POST" action="{{ route('doctor.horario.destroy', $h['id']) }}" onsubmit="return confirm('¿Estás seguro de eliminar este horario?');" class="inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-text-muted hover:text-danger hover:bg-danger-light/50 rounded transition-colors" title="Eliminar">
                                                <span class="material-symbols-outlined text-xs">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-[10px] text-text-muted mt-1.5 pt-1.5 border-t border-border/50">
                                    <span>{{ $h['duracion_consulta_minutos'] ?? 30 }}m / cita</span>
                                    <span class="{{ ($h['activo'] ?? true) ? 'text-emerald-700 font-semibold' : 'text-zinc-400 font-semibold' }}">
                                        {{ ($h['activo'] ?? true) ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="py-5 text-center">
                                <span class="material-symbols-outlined text-zinc-300 text-2xl block mb-1">bedtime</span>
                                <p class="text-[11px] text-text-muted">No laborable</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Botón para añadir en este día específico -->
                <div class="pt-2.5 mt-2 border-t border-border/60">
                    <button type="button" onclick="abrirModalHorarioConDia('{{ $claveDia }}')" class="w-full py-1.5 px-2 rounded-lg text-[11px] font-semibold text-primary hover:bg-primary/10 hover:text-primary-dark transition-all flex items-center justify-center gap-1 border border-dashed border-primary/30">
                        <span class="material-symbols-outlined text-xs">add</span>
                        <span>Agregar turno</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Agregar / Editar Horario -->
<div id="modal_horario" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-4 bg-background border-b border-border flex items-center justify-between">
            <h3 id="modal_horario_titulo" class="font-bold text-primary-dark text-base">Agregar Horario de Atención</h3>
            <button type="button" onclick="cerrarModalHorario()" class="text-text-muted hover:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>
        <form id="form_horario" method="POST" action="{{ route('doctor.horario.store') }}" class="p-6 space-y-4">
            @csrf
            <div id="method_field_container"></div>

            <div class="space-y-1">
                <label for="sel_dia_semana" class="text-xs font-semibold text-text-secondary block">Día de la Semana *</label>
                <select id="sel_dia_semana" name="dia_semana" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                    <option value="lunes">Lunes</option>
                    <option value="martes">Martes</option>
                    <option value="miercoles">Miércoles</option>
                    <option value="jueves">Jueves</option>
                    <option value="viernes">Viernes</option>
                    <option value="sabado">Sábado</option>
                    <option value="domingo">Domingo</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="inp_hora_inicio" class="text-xs font-semibold text-text-secondary block">Hora Inicio *</label>
                    <input type="time" id="inp_hora_inicio" name="hora_inicio" value="08:00" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="inp_hora_fin" class="text-xs font-semibold text-text-secondary block">Hora Fin *</label>
                    <input type="time" id="inp_hora_fin" name="hora_fin" value="14:00" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <div class="space-y-1">
                <label for="inp_duracion_consulta" class="text-xs font-semibold text-text-secondary block">Duración de cada Consulta (Minutos)</label>
                <select id="inp_duracion_consulta" name="duracion_consulta_minutos" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-xs font-medium text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                    <option value="15">15 minutos</option>
                    <option value="20">20 minutos</option>
                    <option value="30" selected>30 minutos (Recomendado)</option>
                    <option value="45">45 minutos</option>
                    <option value="60">60 minutos (1 hora)</option>
                </select>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" id="chk_activo" name="activo" value="1" checked class="w-4 h-4 text-primary rounded border-border focus:ring-primary">
                <label for="chk_activo" class="text-xs text-text-primary font-medium cursor-pointer">Horario activo para agendamiento</label>
            </div>

            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalHorario()" class="px-4 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">Cancelar</button>
                <button type="submit" id="btn_submit_horario" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all">Guardar Horario</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const storeUrl = @json(route('doctor.horario.store'));
    const updateUrlBase = @json(url('/mi-horario'));

    function abrirModalHorario() {
        document.getElementById('modal_horario_titulo').innerText = 'Agregar Horario de Atención';
        document.getElementById('form_horario').action = storeUrl;
        document.getElementById('method_field_container').innerHTML = '';
        document.getElementById('inp_hora_inicio').value = '08:00';
        document.getElementById('inp_hora_fin').value = '14:00';
        document.getElementById('inp_duracion_consulta').value = '30';
        document.getElementById('chk_activo').checked = true;
        document.getElementById('btn_submit_horario').innerText = 'Guardar Horario';
        document.getElementById('modal_horario').classList.remove('hidden');
    }

    function abrirModalHorarioConDia(dia) {
        abrirModalHorario();
        document.getElementById('sel_dia_semana').value = dia;
    }

    function editarHorario(horario) {
        document.getElementById('modal_horario_titulo').innerText = 'Editar Horario de Atención';
        document.getElementById('form_horario').action = updateUrlBase + '/' + horario.id;
        document.getElementById('method_field_container').innerHTML = '@method("PUT")';
        document.getElementById('sel_dia_semana').value = horario.dia_semana;
        document.getElementById('inp_hora_inicio').value = (horario.hora_inicio || '').substring(0, 5);
        document.getElementById('inp_hora_fin').value = (horario.hora_fin || '').substring(0, 5);
        document.getElementById('inp_duracion_consulta').value = horario.duracion_consulta_minutos || 30;
        document.getElementById('chk_activo').checked = Boolean(horario.activo);
        document.getElementById('btn_submit_horario').innerText = 'Actualizar Horario';
        document.getElementById('modal_horario').classList.remove('hidden');
    }

    function cerrarModalHorario() {
        document.getElementById('modal_horario').classList.add('hidden');
    }
</script>
@endsection
