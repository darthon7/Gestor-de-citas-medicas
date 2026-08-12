{{-- Modal de Agendar Nueva Cita (WEB-08) --}}
@php
    // Proyección mínima de doctores para el cliente (mapeo corregido: nombre en usuario)
    $doctoresLista = $doctores instanceof \Illuminate\Contracts\Pagination\Paginator
        ? $doctores->items()
        : $doctores;

    $doctoresJson = collect($doctoresLista)->map(function ($d) {
        $especialidades = collect($d['especialidades'] ?? []);

        return [
            'id'                  => $d['id'] ?? null,
            'nombre'              => $d['usuario']['nombre'] ?? 'Médico',
            'especialidad_nombre' => $especialidades->first()['nombre'] ?? 'General',
            'especialidades'      => $especialidades->pluck('id'),
            'estado_validacion'   => $d['estado_validacion'] ?? 'pendiente',
        ];
    })->values();
@endphp
<div id="modal_nueva_cita" class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(26,26,46,0.5)] backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-[560px] overflow-hidden max-h-[90vh] flex flex-col">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <h3 class="font-semibold text-xl text-primary-dark flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">event_available</span>
                Agendar Nueva Cita
            </h3>
            <button type="button" onclick="cerrarModalCita()" class="text-text-muted hover:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        {{-- Step indicator --}}
        <div class="px-6 pt-5 flex items-center justify-center gap-0">
            @php
                $pasos = [
                    ['num' => 1, 'label' => 'Paciente'],
                    ['num' => 2, 'label' => 'Doctor y Horario'],
                    ['num' => 3, 'label' => 'Confirmación'],
                ];
            @endphp
            @foreach($pasos as $i => $paso)
                <div class="flex items-center {{ $i > 0 ? 'flex-1' : '' }}">
                    @if($i > 0)
                        <div id="paso_linea_{{ $i }}" class="h-0.5 flex-1 mx-2 bg-border mb-4"></div>
                    @endif
                    <div class="flex flex-col items-center">
                        <div id="paso_dot_{{ $paso['num'] }}" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2
                            {{ $paso['num'] === 1 ? 'bg-primary border-primary text-white' : 'border-border text-text-muted' }} transition-all">
                            {{ $paso['num'] }}
                        </div>
                        <span id="paso_label_{{ $paso['num'] }}" class="mt-1.5 text-[10px] font-semibold {{ $paso['num'] === 1 ? 'text-primary' : 'text-text-muted' }} transition-all">
                            {{ $paso['label'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Formulario --}}
        <form method="POST" action="{{ route('citas.store') }}" class="flex-1 flex flex-col overflow-hidden">
            @csrf

            {{-- PASO 1: Paciente --}}
            <div id="paso_panel_1" class="px-6 py-5 overflow-y-auto">
                @if(Auth::user()->rol === 'paciente')
                    <input type="hidden" name="perfil_paciente_id" value="{{ Auth::user()->perfilPaciente?->id }}">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-text-secondary block">Paciente</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-background border border-border rounded-xl text-sm font-semibold text-text-primary cursor-not-allowed"
                               value="{{ Auth::user()->nombre }} (Expediente: {{ Auth::user()->perfilPaciente?->numero_expediente ?? 'N/A' }})" readonly>
                    </div>
                @else
                    <div class="space-y-1">
                        <label for="sel_paciente_cita" class="text-xs font-semibold text-text-secondary block">Paciente *</label>
                        <select id="sel_paciente_cita" name="perfil_paciente_id" required class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                            <option value="">Seleccione Paciente...</option>
                            @forelse($pacientes as $pac)
                                <option value="{{ $pac->id }}">
                                    {{ $pac->usuario?->nombre }} ({{ $pac->numero_expediente ?? 'EXP-' . str_pad($pac->id, 4, '0', STR_PAD_LEFT) }}) - CURP: {{ $pac->usuario?->curp }}
                                </option>
                            @empty
                                <option value="">No hay pacientes registrados</option>
                            @endforelse
                        </select>
                    </div>
                @endif
            </div>

            {{-- PASO 2: Doctor y Horario --}}
            <div id="paso_panel_2" class="px-6 py-5 overflow-y-auto hidden">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="sel_especialidad_cita" class="text-xs font-semibold text-text-secondary block">Especialidad *</label>
                        <select id="sel_especialidad_cita" name="especialidad_id" required onchange="filtrarDoctoresCita()" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                            <option value="">Seleccione Especialidad...</option>
                            @foreach($especialidades as $esp)
                                <option value="{{ $esp['id'] }}">{{ $esp['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="sel_doctor_cita" class="text-xs font-semibold text-text-secondary block">Doctor *</label>
                        <select id="sel_doctor_cita" name="perfil_doctor_id" required onchange="consultarDisponibilidadCita()" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                            <option value="">Seleccione Doctor...</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1 mt-4">
                    <label for="inp_fecha_cita" class="text-xs font-semibold text-text-secondary block">Fecha de Cita *</label>
                    <input type="date" id="inp_fecha_cita" name="fecha_cita" value="{{ old('fecha_cita', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required onchange="consultarDisponibilidadCita()" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>

                <input type="hidden" id="inp_hora_cita" name="hora_cita" value="" required>
                <input type="hidden" name="duracion_minutos" id="inp_duracion_cita" value="30">

                <div class="space-y-1 mt-4">
                    <label class="text-xs font-semibold text-text-secondary block">Horarios Sugeridos Disponibles</label>
                    <div id="slots_container_cita" class="flex flex-wrap gap-2 pt-1">
                        <p class="text-xs text-text-muted">Seleccione doctor y fecha para consultar horarios disponibles.</p>
                    </div>
                </div>

                <div class="space-y-1 mt-4">
                    @include('components.motivo-consulta', ['suf' => '_cita'])
                </div>
            </div>

            {{-- PASO 3: Confirmación --}}
            <div id="paso_panel_3" class="px-6 py-5 overflow-y-auto hidden">
                <h4 class="font-bold text-text-primary text-sm mb-4 pb-2 border-b border-border">Confirma los datos de la cita</h4>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-text-secondary font-medium flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-primary">person</span> Paciente
                        </dt>
                        <dd id="res_paciente" class="text-text-primary font-semibold text-right">-</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-text-secondary font-medium flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-primary">stethoscope</span> Doctor
                        </dt>
                        <dd id="res_doctor" class="text-text-primary font-semibold text-right">-</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-text-secondary font-medium flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-primary">calendar_month</span> Fecha y Hora
                        </dt>
                        <dd id="res_fecha" class="text-text-primary font-semibold text-right">-</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-text-secondary font-medium flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-primary">notes</span> Motivo
                        </dt>
                        <dd id="res_motivo" class="text-text-primary font-semibold text-right max-w-[60%] text-xs">-</dd>
                    </div>
                </dl>
            </div>

            {{-- Footer / Navegación de pasos --}}
            <div class="px-6 py-4 border-t border-border bg-background/50 flex items-center justify-between">
                <button type="button" id="btn_anterior" onclick="cambiarPasoCita(-1)" class="px-5 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all invisible">
                    <span class="material-symbols-outlined text-base align-middle">arrow_back</span> Anterior
                </button>
                <button type="button" id="btn_siguiente" onclick="cambiarPasoCita(1)" disabled class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-primary">
                    Siguiente <span class="material-symbols-outlined text-base align-middle">arrow_forward</span>
                </button>
                <button type="submit" id="btn_confirmar" class="hidden px-6 py-2.5 rounded-xl bg-secondary hover:opacity-90 text-white text-xs font-semibold shadow-md transition-all">
                    <span class="material-symbols-outlined text-lg align-middle">check_circle</span> Confirmar Cita
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let pasoCitaActual = 1;
    const totalPasosCita = 3;
    const elId = (id) => document.getElementById(id);

    function abrirModalCita() {
        pasoCitaActual = 1;
        const modal = elId('modal_nueva_cita');
        if (modal) modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        actualizarPasosCita();
    }

    function cerrarModalCita() {
        const modal = elId('modal_nueva_cita');
        if (modal) modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function cambiarPasoCita(dir) {
        const target = pasoCitaActual + dir;
        if (target < 1 || target > totalPasosCita) return;

        if (target > pasoCitaActual) {
            const ok = validarPasoCita(pasoCitaActual);
            if (!ok) return;
        }
        if (target === totalPasosCita) llenarResumenCita();

        pasoCitaActual = target;
        actualizarPasosCita();
    }

    function validarPasoCita(paso) {
        switch (paso) {
            case 1: {
                const sel = elId('sel_paciente_cita');
                if (sel && !sel.value) {
                    sel.focus();
                    sel.classList.add('border-danger');
                    return false;
                }
                if (sel) sel.classList.remove('border-danger');
                return true;
            }
            case 2: {
                const checks = ['sel_especialidad_cita', 'sel_doctor_cita', 'inp_fecha_cita']
                    .map(elId)
                    .filter(Boolean);
                for (const el of checks) {
                    if (!el.value) {
                        el.classList.add('border-red-500');
                        el.focus();
                        return false;
                    }
                    el.classList.remove('border-red-500');
                }
                const horaCita = elId('inp_hora_cita');
                const slotsCita = elId('slots_container_cita');
                if (horaCita && !horaCita.value) {
                    if (slotsCita) slotsCita.classList.add('border-danger');
                    const chip = document.querySelector('.slot-chip-cita');
                    if (chip) {
                        chip.focus();
                    } else {
                        const selDoc = elId('sel_doctor_cita');
                        if (selDoc) selDoc.focus();
                    }
                    return false;
                }
                if (slotsCita) slotsCita.classList.remove('border-danger');
                if (!motivoTieneValor('_cita')) {
                    marcarErrorMotivo('_cita', true);
                    const selMotivo = elId('sel_motivo_cita');
                    if (selMotivo) selMotivo.focus();
                    return false;
                }
                return true;
            }
        }
        return true;
    }

    function actualizarPasosCita() {
        for (let i = 1; i <= totalPasosCita; i++) {
            const panel = elId('paso_panel_' + i);
            const dot = elId('paso_dot_' + i);
            const label = elId('paso_label_' + i);
            const activo = i <= pasoCitaActual;
            if (panel) panel.classList.toggle('hidden', i !== pasoCitaActual);
            if (dot) dot.className = 'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all ' +
                (activo ? 'bg-primary border-primary text-white' : 'border-border text-text-muted');
            if (label) label.className = 'mt-1.5 text-[10px] font-semibold transition-all ' + (activo ? 'text-primary' : 'text-text-muted');
            if (i > 1) {
                const linea = elId('paso_linea_' + i);
                if (linea) linea.className = 'h-0.5 flex-1 mx-2 mb-4 transition-all ' + (i <= pasoCitaActual ? 'bg-primary' : 'bg-border');
            }
        }
        const btnAnt = elId('btn_anterior');
        const btnSig = elId('btn_siguiente');
        const btnCon = elId('btn_confirmar');
        if (btnAnt) btnAnt.classList.toggle('invisible', pasoCitaActual === 1);
        if (btnSig) btnSig.classList.toggle('hidden', pasoCitaActual === totalPasosCita);
        if (btnCon) btnCon.classList.toggle('hidden', pasoCitaActual !== totalPasosCita);
        sincronizarBtnSiguiente();
    }

    // --- Motivo de la consulta (asunto): bloqueo del avance sin asunto ---
    function sincronizarBtnSiguiente() {
        const btn = elId('btn_siguiente');
        if (!btn) return;
        btn.disabled = pasoCitaActual === 2 && !motivoTieneValor('_cita');
    }

    (function () {
        const sel = elId('sel_motivo_cita');
        const otro = elId('inp_motivo_otro_cita');
        if (sel) sel.addEventListener('change', function () {
            syncMotivo('_cita');
            sincronizarBtnSiguiente();
        });
        if (otro) otro.addEventListener('input', function () {
            syncMotivo('_cita');
            sincronizarBtnSiguiente();
        });
        initMotivo('_cita', '');
        sincronizarBtnSiguiente();
    })();

    // --- Doctores: mapeo corregido + solo validados (filtro en estado local, sin backend) ---
    const doctoresModalCita = @json($doctoresJson);

    (function () {
        const select = elId('sel_doctor_cita');
        if (!select) return;

        const visibles = doctoresModalCita.filter(d => d.estado_validacion === 'validado');
        select.insertAdjacentHTML('beforeend', visibles.map(d =>
            '<option value="' + d.id + '" data-especialidades="' + d.especialidades.join(',') + '">Dr. ' +
            d.nombre + ' (' + d.especialidad_nombre + ')</option>'
        ).join(''));
    })();

    function filtrarDoctoresCita() {
        const selectEsp = elId('sel_especialidad_cita');
        const selectDoc = elId('sel_doctor_cita');
        const container = elId('slots_container_cita');
        if (!selectEsp || !selectDoc || !container) return;

        const espId = selectEsp.value;
        const currentVal = selectDoc.value;
        const options = selectDoc.querySelectorAll('option');
        let selectedVisible = false;

        options.forEach(opt => {
            if (!opt.value) return;
            const espIds = (opt.getAttribute('data-especialidades') || '').split(',').filter(Boolean);
            const match = !espId || espIds.includes(espId);
            opt.style.display = match ? '' : 'none';
            if (opt.value === currentVal && match) selectedVisible = true;
        });

        if (!selectedVisible) {
            selectDoc.value = '';
            const inp = elId('inp_hora_cita');
            if (inp) inp.value = '';
            const inpFecha = elId('inp_fecha_cita');
            if (inpFecha) inpFecha.value = '';
            container.classList.remove('border-danger');
        }
        container.innerHTML = '<p class="text-xs text-text-muted">Seleccione doctor y fecha para consultar horarios disponibles.</p>';
    }

    async function consultarDisponibilidadCita() {
        const selectDoc = elId('sel_doctor_cita');
        const inpFecha = elId('inp_fecha_cita');
        const container = elId('slots_container_cita');
        if (!selectDoc || !inpFecha || !container) return;

        const doctorId = selectDoc.value;
        const fecha = inpFecha.value;

        if (!doctorId || !fecha) {
            container.innerHTML = '<p class="text-xs text-text-muted">Seleccione doctor y fecha para consultar horarios disponibles.</p>';
            return;
        }

        try {
            const response = await fetch(`/api/obtenerDisponibilidad/${doctorId}?fecha=${fecha}`);
            const data = await response.json();
            const slots = data.data || [];

            if (!Array.isArray(slots) || slots.length === 0) {
                container.innerHTML = '<p class="text-xs text-amber-700 font-medium">No hay horarios configurados para esta fecha.</p>';
                return;
            }

            const disponibles = slots.filter(s => s && s.disponible);
            if (disponibles.length === 0) {
                container.innerHTML = '<p class="text-xs text-amber-700 font-medium">No hay horarios disponibles para esta fecha.</p>';
                return;
            }

            container.innerHTML = disponibles.map(s => `
                <button type="button" class="slot-chip-cita" data-hora="${s.hora}" onclick="seleccionarHoraCita('${s.hora}')">${s.hora}</button>
            `).join('');
            container.classList.remove('border-danger');

            const inp = elId('inp_hora_cita');
            if (inp) {
                const horas = disponibles.map(s => s.hora);
                if (inp.value && horas.includes(inp.value)) {
                    document.querySelectorAll('.slot-chip-cita').forEach(btn => {
                        btn.classList.toggle('active', btn.getAttribute('data-hora') === inp.value);
                    });
                } else if (inp.value) {
                    inp.value = '';
                }
            }
        } catch (e) {
            container.innerHTML = '<p class="text-xs text-text-muted">No fue posible consultar horarios. Ingresa la hora manualmente.</p>';
        }
    }

    function seleccionarHoraCita(hora) {
        const inp = elId('inp_hora_cita');
        if (inp) inp.value = hora;
        document.querySelectorAll('.slot-chip-cita').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-hora') === hora);
        });
        const slotsCita = elId('slots_container_cita');
        if (slotsCita) slotsCita.classList.remove('border-danger');
        sincronizarBtnSiguiente();
    }

    function llenarResumenCita() {
        const resPac = elId('res_paciente');
        const resDoc = elId('res_doctor');
        const resFecha = elId('res_fecha');
        const resMotivo = elId('res_motivo');

        const selPac = elId('sel_paciente_cita');
        let paciente = selPac && selPac.selectedIndex >= 0
            ? (selPac.options[selPac.selectedIndex]?.textContent || '').replace(/\s{2,}/g, ' ').trim()
            : '';
        if (!paciente) {
            const hiddenPac = document.querySelector('#modal_nueva_cita input[name="perfil_paciente_id"]');
            const readonly = hiddenPac?.closest('div')?.querySelector('input[readonly]');
            paciente = readonly?.value || '';
        }
        if (resPac) resPac.textContent = paciente || '-';

        const selDoc = elId('sel_doctor_cita');
        if (resDoc) resDoc.textContent = selDoc && selDoc.selectedIndex >= 0
            ? (selDoc.options[selDoc.selectedIndex]?.textContent || '').trim()
            : '-';

        const fecha = elId('inp_fecha_cita')?.value || '';
        const hora = elId('inp_hora_cita')?.value || '';
        if (resFecha) resFecha.textContent = (fecha
            ? new Date(fecha + 'T12:00:00').toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
            : '-') + ' ' + (hora || '');

        if (resMotivo) resMotivo.textContent = elId('inp_motivo_consulta_cita')?.value || '-';
    }

    document.addEventListener('keydown', function (e) {
        const modal = elId('modal_nueva_cita');
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            cerrarModalCita();
        }
    });

    const modalCita = elId('modal_nueva_cita');
    if (modalCita) {
        modalCita.addEventListener('click', function (e) {
            if (e.target === this) cerrarModalCita();
        });
    }
</script>

<style>
    .slot-chip-cita {
        padding: 6px 14px;
        border-radius: 20px;
        background-color: #ffffff;
        border: 1px solid var(--primary-container);
        font-size: 12px;
        font-weight: 600;
        color: var(--primary-container);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .slot-chip-cita:hover {
        background-color: var(--primary-light);
    }
    .slot-chip-cita.active {
        background-color: var(--primary-container);
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(var(--primary-container-rgb), 0.25);
    }
</style>