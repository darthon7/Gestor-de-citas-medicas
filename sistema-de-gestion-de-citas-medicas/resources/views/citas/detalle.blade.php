@extends('layouts.app')
@section('titulo', 'Detalle de la Cita')

@section('content')
<!-- Header Controls -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ Auth::user()->rol === 'doctor' ? route('doctor.agenda') : route('citas.index') }}" class="p-2 bg-surface border border-border rounded-xl text-text-secondary hover:text-primary transition-all">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Detalle de Cita #{{ $cita['id'] }}</h1>
        <p class="text-xs text-text-secondary mt-0.5">Información completa y expediente de la consulta médica</p>
    </div>
</div>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Status Banner Card -->
    @php
        $estado = strtolower($cita['estado'] ?? 'pendiente');
        $statusStyle = match($estado) {
            'confirmada', 'completada' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'en_consulta' => 'bg-sky-50 text-sky-800 border-sky-200',
            'cancelada' => 'bg-rose-50 text-rose-800 border-rose-200',
            default => 'bg-amber-50 text-amber-800 border-amber-200'
        };
        $estadoNombre = match($estado) {
            'completada' => 'Finalizada',
            'en_consulta' => 'En Consulta',
            'cancelada' => 'Cancelada',
            'confirmada' => 'Confirmada',
            'agendada' => 'Agendada',
            default => ucfirst($estado)
        };
    @endphp
    <div class="p-4 rounded-2xl border {{ $statusStyle }} flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-2xl">event_available</span>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider block">Estado de la Cita</span>
                <span class="text-sm font-semibold">{{ $estadoNombre }}</span>
            </div>
        </div>
        <span class="font-mono text-xs font-bold opacity-80">REF-{{ str_pad($cita['id'], 5, '0', STR_PAD_LEFT) }}</span>
    </div>

    <!-- Main Consultation Info Card -->
    <div class="bg-surface rounded-2xl card-shadow border border-border p-6 space-y-4">
        <h3 class="font-bold text-primary-dark text-base border-b border-border pb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">medical_information</span>
            <span>Información de la Consulta</span>
        </h3>

        <div class="divide-y divide-border text-xs">
            <div class="py-3 flex items-center justify-between">
                <span class="text-text-secondary font-medium">Fecha y Hora:</span>
                <strong class="text-text-primary text-sm font-bold">
                    {{ \Carbon\Carbon::parse($cita['fecha_hora'])->isoFormat('DD [de] MMMM YYYY, h:i A') }}
                </strong>
            </div>

            <div class="py-3 flex items-center justify-between">
                <span class="text-text-secondary font-medium">Paciente:</span>
                <strong class="text-text-primary text-sm font-semibold">
                    {{ $cita['perfilPaciente']['usuario']['nombre'] ?? 'N/A' }}
                </strong>
            </div>

            <div class="py-3 flex items-center justify-between">
                <span class="text-text-secondary font-medium">Doctor:</span>
                <strong class="text-text-primary text-sm font-semibold">
                    Dr. {{ $cita['perfilDoctor']['usuario']['nombre'] ?? 'N/A' }}
                </strong>
            </div>

            <div class="py-3 flex items-center justify-between">
                <span class="text-text-secondary font-medium">Especialidad:</span>
                <span class="px-2.5 py-1 rounded-lg bg-background text-text-secondary border border-border font-semibold">
                    {{ $cita['especialidad']['nombre'] ?? 'General' }}
                </span>
            </div>

            <div class="pt-3">
                <span class="text-text-secondary font-medium block mb-1">Motivo de Consulta:</span>
                <div class="p-3.5 bg-background rounded-xl text-text-primary text-xs border border-border">
                    {{ $cita['motivo_consulta'] ?? 'Sin motivo especificado' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Doctor Clinical Notes & Medical Report Card (If available) -->
    @php
        $nota = $cita->notaConsulta ?? $cita['nota_consulta'] ?? $cita['notas'] ?? null;
    @endphp
    @if($nota)
        <div class="bg-surface rounded-2xl card-shadow border-l-4 border-emerald-500 border border-border p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-border pb-3">
                <h3 class="font-bold text-emerald-800 text-base flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 text-xl">clinical_notes</span>
                    <span>Informe Médico y Diagnóstico</span>
                </h3>
                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                    Consulta Concluida
                </span>
            </div>

            <!-- Signos Vitales -->
            @if(!empty($nota['presion_arterial']) || !empty($nota['frecuencia_cardiaca']) || !empty($nota['temperatura']) || !empty($nota['peso']))
                <div>
                    <h4 class="text-xs font-bold text-text-secondary uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-rose-500 text-base">heart_pulse</span>
                        <span>Signos Vitales Registrados</span>
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        @if(!empty($nota['presion_arterial']))
                            <div class="p-3 bg-background rounded-xl border border-border">
                                <span class="text-text-muted text-[10px] block">Presión Arterial</span>
                                <strong class="text-text-primary text-sm">{{ $nota['presion_arterial'] }}</strong>
                                <span class="text-[10px] text-text-secondary block">mmHg</span>
                            </div>
                        @endif
                        @if(!empty($nota['frecuencia_cardiaca']))
                            <div class="p-3 bg-background rounded-xl border border-border">
                                <span class="text-text-muted text-[10px] block">Frecuencia Cardíaca</span>
                                <strong class="text-text-primary text-sm">{{ $nota['frecuencia_cardiaca'] }}</strong>
                                <span class="text-[10px] text-text-secondary block">bpm</span>
                            </div>
                        @endif
                        @if(!empty($nota['temperatura']))
                            <div class="p-3 bg-background rounded-xl border border-border">
                                <span class="text-text-muted text-[10px] block">Temperatura</span>
                                <strong class="text-text-primary text-sm">{{ $nota['temperatura'] }}</strong>
                                <span class="text-[10px] text-text-secondary block">°C</span>
                            </div>
                        @endif
                        @if(!empty($nota['peso']))
                            <div class="p-3 bg-background rounded-xl border border-border">
                                <span class="text-text-muted text-[10px] block">Peso Corporal</span>
                                <strong class="text-text-primary text-sm">{{ $nota['peso'] }}</strong>
                                <span class="text-[10px] text-text-secondary block">kg</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Diagnóstico -->
            <div class="space-y-1.5 text-xs">
                <span class="font-bold text-text-primary flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-base">assignment</span>
                    <span>Diagnóstico Clínico:</span>
                </span>
                <div class="text-text-primary bg-primary/5 p-4 rounded-xl border border-primary/15 leading-relaxed whitespace-pre-line">
                    {{ $nota['diagnostico'] ?? $nota['nota'] ?? 'Sin diagnóstico registrado' }}
                </div>
            </div>

            <!-- Tratamiento -->
            <div class="space-y-1.5 text-xs">
                <span class="font-bold text-text-primary flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sky-700 text-base">medication</span>
                    <span>Tratamiento y Recomendaciones Médicas:</span>
                </span>
                <div class="text-text-primary bg-sky-50/50 p-4 rounded-xl border border-sky-200/60 leading-relaxed whitespace-pre-line">
                    {{ $nota['tratamiento'] ?? 'Sin tratamiento especificado' }}
                </div>
            </div>

            <!-- Observaciones Adicionales -->
            @if(!empty($nota['notas_adicionales']))
                <div class="space-y-1.5 text-xs">
                    <span class="font-bold text-text-primary flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-amber-600 text-base">notes</span>
                        <span>Observaciones Adicionales:</span>
                    </span>
                    <div class="text-text-secondary bg-background p-3.5 rounded-xl border border-border leading-relaxed whitespace-pre-line">
                        {{ $nota['notas_adicionales'] }}
                    </div>
                </div>
            @endif

            <!-- Footer con Médico y Fecha -->
            <div class="pt-3 border-t border-border/80 flex items-center justify-between text-[11px] text-text-muted">
                <span>Registrado por: <strong>Dr. {{ $nota->creadoPor?->nombre ?? $cita['perfilDoctor']['usuario']['nombre'] ?? 'Médico Tratante' }}</strong></span>
                @if(!empty($nota['created_at']))
                    <span>{{ \Carbon\Carbon::parse($nota['created_at'])->isoFormat('D [de] MMMM, YYYY - h:i A') }}</span>
                @endif
            </div>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="space-y-3">
        @if(in_array(Auth::user()->rol, ['admin', 'recepcionista']))
            @if(in_array($estado, ['pendiente', 'confirmada', 'agendada']))
                <form method="POST" action="{{ route('citas.checkin', $cita['id']) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-primary hover:bg-primary-dark text-white font-semibold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg">how_to_reg</span>
                        <span>Registrar Check-in (Paciente Presente)</span>
                    </button>
                </form>
            @endif

            @if($estado !== 'cancelada' && $estado !== 'completada')
                <button type="button" onclick="abrirModalCancelar()" class="w-full py-3 px-6 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 font-semibold text-xs transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">cancel</span>
                    <span>Cancelar Cita</span>
                </button>
            @endif
        @endif

        @if(Auth::user()->rol === 'doctor')
            @if(in_array($estado, ['confirmada', 'pendiente', 'agendada']))
                <form method="POST" action="{{ route('citas.iniciar', $cita['id']) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-primary hover:bg-primary-dark text-white font-semibold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg">play_circle</span>
                        <span>Iniciar Consulta Médica</span>
                    </button>
                </form>
            @endif

            @if($estado === 'en_consulta')
                <a href="{{ route('doctor.diagnostico', $cita['id']) }}" class="w-full py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">edit_note</span>
                    <span>Registrar Nota y Finalizar Consulta</span>
                </a>
            @endif
        @endif
    </div>
</div>

<!-- Modal Cancelar Cita -->
<div id="modal_cancelar_cita" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-rose-50 border-b border-rose-100 flex items-center justify-between">
            <h3 class="font-bold text-rose-800 text-base">Cancelar Cita Médica</h3>
            <button type="button" onclick="cerrarModalCancelar()" class="text-rose-400 hover:text-rose-800 transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('citas.cancelar', $cita['id']) }}" class="p-6 space-y-4">
            @csrf
            @method('PATCH')

            <p class="text-xs text-text-secondary">Por favor especifica el motivo de la cancelación:</p>

            <textarea name="motivo_cancelacion" required rows="3" placeholder="Motivo de la cancelación..." class="w-full p-3 bg-white border border-border rounded-xl text-xs text-text-primary focus:outline-none focus:border-danger focus:ring-2 focus:ring-danger/10 transition-all"></textarea>

            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalCancelar()" class="px-4 py-2 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">
                    Volver
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md transition-all">
                    Confirmar Cancelación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function abrirModalCancelar() {
        document.getElementById('modal_cancelar_cita').classList.remove('hidden');
    }
    function cerrarModalCancelar() {
        document.getElementById('modal_cancelar_cita').classList.add('hidden');
    }
</script>
@endsection
