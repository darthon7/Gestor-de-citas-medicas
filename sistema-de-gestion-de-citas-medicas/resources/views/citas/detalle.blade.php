@extends('layouts.app')
@section('titulo', 'Detalle de la Cita')

@section('content')
<!-- Header Controls -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('citas.index') }}" class="p-2 bg-surface border border-border rounded-xl text-text-secondary hover:text-primary transition-all">
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
    @endphp
    <div class="p-4 rounded-2xl border {{ $statusStyle }} flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-2xl">event_available</span>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider block">Estado de la Cita</span>
                <span class="text-sm font-semibold capitalize">{{ $cita['estado'] }}</span>
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

    <!-- Doctor Clinical Notes Card (If available) -->
    @if(!empty($cita['nota_consulta']) || !empty($cita['notas']))
        @php
            $nota = $cita['nota_consulta'] ?? $cita['notas'] ?? null;
        @endphp
        <div class="bg-surface rounded-2xl card-shadow border-l-4 border-emerald-500 border border-border p-6 space-y-3">
            <h3 class="font-bold text-emerald-800 text-base border-b border-border pb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-xl">clinical_notes</span>
                <span>Diagnóstico y Tratamiento Médico</span>
            </h3>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="font-bold text-text-primary block mb-1">Diagnóstico Clínico:</span>
                    <p class="text-text-secondary bg-emerald-50/50 p-3 rounded-xl border border-emerald-100">
                        {{ $nota['diagnostico'] ?? $nota['nota'] ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <span class="font-bold text-text-primary block mb-1">Tratamiento Indicado:</span>
                    <p class="text-text-secondary bg-emerald-50/50 p-3 rounded-xl border border-emerald-100">
                        {{ $nota['tratamiento'] ?? 'N/A' }}
                    </p>
                </div>
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
