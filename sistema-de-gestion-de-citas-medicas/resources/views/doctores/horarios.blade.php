@extends('layouts.app')
@section('titulo', 'Horarios de Atención')

@section('content')
<!-- Header Controls -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('doctores.index') }}" class="p-2 bg-surface border border-border rounded-xl text-text-secondary hover:text-primary transition-all">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Horarios de Atención</h1>
        <p class="text-xs text-text-secondary mt-0.5">Configura la disponibilidad semanal y los bloqueos de agenda del médico</p>
    </div>
</div>

<!-- Doctor Mini Card -->
<div class="bg-surface rounded-2xl card-shadow border border-border p-5 mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-primary-light/40 text-primary-dark font-bold flex items-center justify-center border border-primary/20 text-lg">
            {{ strtoupper(substr($doctor->usuario->nombre ?? 'D', 0, 2)) }}
        </div>
        <div>
            <h4 class="font-bold text-text-primary text-base">Dr. {{ $doctor->usuario->nombre ?? 'Médico' }}</h4>
            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-md bg-background text-primary text-[11px] font-semibold border border-border">{{ $doctor->especialidades->first()->nombre ?? 'General' }}</span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="abrirModalHorario()" class="px-4 py-2.5 bg-primary text-white rounded-xl font-semibold text-xs shadow-md hover:bg-primary-dark transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">add</span>
            <span>Agregar Horario</span>
        </button>
        <button type="button" onclick="abrirModalBloqueo()" class="px-4 py-2.5 bg-surface border border-danger/40 text-danger rounded-xl font-semibold text-xs hover:bg-danger-light/50 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">block</span>
            <span>Bloquear Horario</span>
        </button>
    </div>
</div>

<!-- Main Layout Grid + Side Panel -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Weekly Schedule Grid -->
    <div class="lg:col-span-2 bg-surface rounded-2xl card-shadow border border-border p-6">
        <h3 class="font-bold text-text-primary text-base mb-5 pb-3 border-b border-border flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">calendar_month</span>
            <span>Disponibilidad Semanal</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3">
            @php
                $diasMap = ['lunes' => 'Lun', 'martes' => 'Mar', 'miercoles' => 'Mié', 'jueves' => 'Jue', 'viernes' => 'Vie', 'sabado' => 'Sáb', 'domingo' => 'Dom'];
            @endphp
            @foreach($diasMap as $diaClave => $nombreDia)
                <div class="bg-background/60 border border-border rounded-xl p-3">
                    <div class="text-center font-bold text-xs text-text-primary pb-2 border-b border-border mb-2">{{ $nombreDia }}</div>
                    <div class="flex flex-col gap-2">
                        @php
                            $horariosDia = $horarios->filter(fn($h) => ($h['dia_semana'] ?? '') === $diaClave);
                        @endphp
                        @forelse($horariosDia as $h)
                            <div class="p-2.5 bg-primary/10 text-primary border border-primary/20 rounded-lg relative group">
                                <form method="POST" action="{{ route('horarios.destroy', $h['id']) }}" onsubmit="return confirm('¿Eliminar horario?');" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger hover:bg-danger-light/50 rounded p-0.5" title="Eliminar">
                                        <span class="material-symbols-outlined text-base">close</span>
                                    </button>
                                </form>
                                <div class="text-[11px] font-bold">{{ \Carbon\Carbon::parse($h['hora_inicio'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($h['hora_fin'])->format('h:i A') }}</div>
                                <div class="text-[10px] text-text-secondary mt-0.5">{{ $h['duracion_consulta_minutos'] ?? 30 }} min</div>
                            </div>
                        @empty
                            <p class="text-text-muted text-center text-[11px] py-2">Sin horario</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Bloqueos Registrados -->
    <div class="bg-surface rounded-2xl card-shadow border border-border p-6 h-fit">
        <h3 class="font-bold text-text-primary text-base mb-5 pb-3 border-b border-border flex items-center gap-2">
            <span class="material-symbols-outlined text-danger text-xl">block</span>
            <span>Bloqueos de Agenda</span>
        </h3>
        <div class="flex flex-col gap-3">
            @forelse($bloqueos as $bloqueo)
                <div class="p-4 bg-danger-light/60 text-danger border border-danger/20 rounded-xl relative group">
                    <form method="POST" action="{{ route('bloqueos.destroy', $bloqueo['id']) }}" onsubmit="return confirm('¿Eliminar bloqueo?');" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-danger hover:bg-danger-light rounded p-0.5" title="Eliminar Bloqueo">
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </form>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-lg leading-none">event_busy</span>
                        <div class="min-w-0">
                            <div class="font-bold text-xs leading-relaxed">{{ \Carbon\Carbon::parse($bloqueo['fecha_bloqueo'])->format('d/m/Y') }}
                                @if($bloqueo['hora_inicio_bloqueo'])
                                    · {{ \Carbon\Carbon::parse($bloqueo['hora_inicio_bloqueo'])->format('H:i') }} - {{ \Carbon\Carbon::parse($bloqueo['hora_fin_bloqueo'] ?? '23:59:00')->format('H:i') }}
                                @endif
                            </div>
                            <div class="text-[11px] text-text-secondary mt-1">Motivo: {{ $bloqueo['motivo'] ?? 'Sin motivo' }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-xs text-text-muted">
                    <span class="material-symbols-outlined text-3xl mb-1 block text-text-muted">event_available</span>
                    No hay bloqueos activos para este médico.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Agregar Horario -->
<div id="modal_horario" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-background border-b border-border flex items-center justify-between">
            <h3 class="font-bold text-primary-dark text-base">Agregar Horario de Atención</h3>
            <button type="button" onclick="cerrarModalHorario()" class="text-text-muted hover:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('horarios.store', $doctorId) }}" class="p-6 space-y-4">
            @csrf
            <div class="space-y-1">
                <label for="sel_dia" class="text-xs font-semibold text-text-secondary block">Día de la Semana *</label>
                <select id="sel_dia" name="dia_semana" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
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
                    <input type="time" id="inp_hora_inicio" name="hora_inicio" value="08:00" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="inp_hora_fin" class="text-xs font-semibold text-text-secondary block">Hora Fin *</label>
                    <input type="time" id="inp_hora_fin" name="hora_fin" value="14:00" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>
            <div class="space-y-1">
                <label for="inp_duracion" class="text-xs font-semibold text-text-secondary block">Duración por Cita (Minutos)</label>
                <input type="number" id="inp_duracion" name="duracion_consulta_minutos" value="30" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalHorario()" class="px-4 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all">Guardar Horario</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registrar Bloqueo -->
<div id="modal_bloqueo" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-danger-light border-b border-danger/20 flex items-center justify-between">
            <h3 class="font-bold text-danger text-base">Registrar Bloqueo de Agenda</h3>
            <button type="button" onclick="cerrarModalBloqueo()" class="text-danger/60 hover:text-danger transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('bloqueos.store', $doctorId) }}" class="p-6 space-y-4">
            @csrf
            <div class="space-y-1">
                <label for="inp_f_bloqueo" class="text-xs font-semibold text-text-secondary block">Fecha del Bloqueo *</label>
                <input type="date" id="inp_f_bloqueo" name="fecha_bloqueo" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-danger focus:ring-2 focus:ring-danger/10 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="inp_h_bloqueo_inicio" class="text-xs font-semibold text-text-secondary block">Hora Inicio</label>
                    <input type="time" id="inp_h_bloqueo_inicio" name="hora_inicio_bloqueo" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-danger focus:ring-2 focus:ring-danger/10 transition-all">
                </div>
                <div class="space-y-1">
                    <label for="inp_h_bloqueo_fin" class="text-xs font-semibold text-text-secondary block">Hora Fin</label>
                    <input type="time" id="inp_h_bloqueo_fin" name="hora_fin_bloqueo" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-danger focus:ring-2 focus:ring-danger/10 transition-all">
                </div>
            </div>
            <p class="text-[11px] text-text-muted">Deja las horas vacías para bloquear todo el día.</p>
            <div class="space-y-1">
                <label for="txt_motivo_blq" class="text-xs font-semibold text-text-secondary block">Motivo del Bloqueo *</label>
                <input type="text" id="txt_motivo_blq" name="motivo" required placeholder="Vacaciones, congreso, etc." class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-danger focus:ring-2 focus:ring-danger/10 transition-all">
            </div>
            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalBloqueo()" class="px-4 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md transition-all">Registrar Bloqueo</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function abrirModalHorario() { document.getElementById('modal_horario').classList.remove('hidden'); }
    function cerrarModalHorario() { document.getElementById('modal_horario').classList.add('hidden'); }
    function abrirModalBloqueo() { document.getElementById('modal_bloqueo').classList.remove('hidden'); }
    function cerrarModalBloqueo() { document.getElementById('modal_bloqueo').classList.add('hidden'); }
</script>
@endsection