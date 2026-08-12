{{-- Componente: Motivo de la Consulta (asunto de la cita) — reutilizable por sufijo --}}
@php
    $motivosConsulta = config('citas.motivos_consulta', [
        'Consulta general / chequeo de rutina',
        'Dolor o molestia',
        'Fiebre',
        'Síntomas respiratorios (tos, gripe, congestión)',
        'Problemas digestivos',
        'Problemas de la piel o alergias',
        'Control cardíaco / presión arterial',
        'Control de enfermedad crónica',
        'Resultados de estudios o laboratorio',
        'Vacunación',
        'Seguimiento de tratamiento',
    ]);
@endphp
<div class="space-y-1">
    <label for="sel_motivo{{ $suf }}" class="text-xs font-semibold text-text-secondary block">Motivo de la Consulta *</label>
    <select id="sel_motivo{{ $suf }}" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
        <option value="">Seleccione un motivo...</option>
        @foreach($motivosConsulta as $motivo)
            <option value="{{ $motivo }}">{{ $motivo }}</option>
        @endforeach
        <option value="__otro__">Otro (especificar)</option>
    </select>
    <input type="text" id="inp_motivo_otro{{ $suf }}" placeholder="Especifica el motivo..." maxlength="200"
           class="hidden w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
    <input type="hidden" id="inp_motivo_consulta{{ $suf }}" name="motivo_consulta" value="{{ $valorInicial ?? '' }}">
    <p id="msg_motivo{{ $suf }}" class="hidden text-[11px] text-danger font-medium">Por favor selecciona el motivo de la consulta.</p>
</div>

<script>
    function syncMotivo(suf) {
        const sel = document.getElementById('sel_motivo' + suf);
        const otro = document.getElementById('inp_motivo_otro' + suf);
        const hidden = document.getElementById('inp_motivo_consulta' + suf);
        if (!sel || !hidden) return '';
        const esOtro = sel.value === '__otro__';
        if (otro) otro.classList.toggle('hidden', !esOtro);
        hidden.value = esOtro ? (otro ? otro.value.trim() : '') : sel.value;
        limpiarErrorMotivo(suf);
        return hidden.value;
    }

    function motivoTieneValor(suf) {
        const hidden = document.getElementById('inp_motivo_consulta' + suf);
        return !!hidden && hidden.value.trim() !== '';
    }

    function marcarErrorMotivo(suf, mostrar) {
        const sel = document.getElementById('sel_motivo' + suf);
        const msg = document.getElementById('msg_motivo' + suf);
        if (sel) sel.classList.toggle('border-danger', mostrar);
        if (msg) msg.classList.toggle('hidden', !mostrar);
    }

    function limpiarErrorMotivo(suf) {
        marcarErrorMotivo(suf, false);
    }

    function initMotivo(suf, valorInicial) {
        const sel = document.getElementById('sel_motivo' + suf);
        const otro = document.getElementById('inp_motivo_otro' + suf);
        if (!sel) return;
        if (valorInicial && !sel.value) {
            let encontrado = false;
            for (const opt of sel.options) {
                if (opt.value === valorInicial) {
                    sel.value = valorInicial;
                    encontrado = true;
                    break;
                }
            }
            if (!encontrado) {
                sel.value = '__otro__';
                if (otro) {
                    otro.value = valorInicial;
                    otro.classList.remove('hidden');
                }
            }
        }
        syncMotivo(suf);
    }
</script>
