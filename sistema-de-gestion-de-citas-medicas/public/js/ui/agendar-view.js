// js/ui/agendar-view.js - Lógica del Wizard de Agendamiento de Cita
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { pacientesService } from '../api/pacientes-service.js';
import { doctoresService } from '../api/doctores-service.js';
import { citasService } from '../api/citas-service.js';
import { notify } from '../utils/notifications.js';
import { formatFullDateTime } from '../utils/formatters.js';

let state = {
    paciente: null,
    especialidadId: null,
    especialidadNombre: '',
    doctorId: null,
    doctorNombre: '',
    fecha: null,
    hora: null,
    motivo: ''
};

document.addEventListener('DOMContentLoaded', async () => {
    if (!checkRole(['admin', 'recepcionista'])) return;

    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    if (user.rol === 'recepcionista') {
        document.querySelectorAll('.nav-admin-only').forEach(el => el.classList.add('oculto'));
    }

    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    // Cargar Catálogos Iniciales
    await cargarEspecialidadesWizard();

    // Eventos Paso 1: Búsqueda Paciente
    let timer;
    document.getElementById('inp_buscar_pac_cita').addEventListener('input', (e) => {
        clearTimeout(timer);
        timer = setTimeout(() => buscarPacientesLocal(e.target.value.trim()), 300);
    });

    document.getElementById('btn_ir_paso_2').addEventListener('click', () => switchWizardStep(2));
    document.getElementById('btn_volver_paso_1').addEventListener('click', () => switchWizardStep(1));
    document.getElementById('btn_ir_paso_3').addEventListener('click', () => {
        state.motivo = document.getElementById('txt_motivo_cita').value.trim();
        if (!state.motivo) {
            notify.error('Por favor indique el motivo de la consulta.');
            return;
        }
        prepararResumenPaso3();
        switchWizardStep(3);
    });
    document.getElementById('btn_volver_paso_2').addEventListener('click', () => switchWizardStep(2));

    // Eventos Paso 2: Selección Doctor / Fecha
    document.getElementById('sel_especialidad_cita').addEventListener('change', async (e) => {
        state.especialidadId = e.target.value;
        const selText = e.target.options[e.target.selectedIndex].text;
        state.especialidadNombre = selText;

        const selDoctor = document.getElementById('sel_doctor_cita');
        selDoctor.disabled = true;
        document.getElementById('inp_fecha_cita').disabled = true;
        limpiarSlots();

        if (state.especialidadId) {
            await cargarDoctoresPorEspecialidad(state.especialidadId);
        }
    });

    document.getElementById('sel_doctor_cita').addEventListener('change', (e) => {
        state.doctorId = e.target.value;
        state.doctorNombre = e.target.options[e.target.selectedIndex].text;
        const inpFecha = document.getElementById('inp_fecha_cita');

        inpFecha.disabled = !state.doctorId;
        limpiarSlots();
        if (inpFecha.value && state.doctorId) {
            consultarSlotsDisponibles();
        }
    });

    document.getElementById('inp_fecha_cita').addEventListener('change', (e) => {
        state.fecha = e.target.value;
        if (state.doctorId && state.fecha) {
            consultarSlotsDisponibles();
        }
    });

    // Evento Final: Confirmar y Agendar
    document.getElementById('btn_confirmar_cita').addEventListener('click', async () => {
        const datos = {
            paciente_id: state.paciente.id,
            doctor_id: state.doctorId,
            especialidad_id: state.especialidadId,
            fecha_hora: `${state.fecha} ${state.hora}:00`,
            motivo_consulta: state.motivo
        };

        const btn = document.getElementById('btn_confirmar_cita');
        btn.disabled = true;

        try {
            const res = await citasService.registrarCita(datos);
            if (res.ok) {
                notify.success('¡Cita registrada correctamente!');
                setTimeout(() => {
                    window.location.href = '/citas.html';
                }, 1500);
            } else {
                notify.error(res.data?.mensaje || res.data?.msj || 'Error al agendar la cita.');
                btn.disabled = false;
            }
        } catch (err) {
            notify.error('No se pudo conectar con el servidor.');
            btn.disabled = false;
        }
    });
});

async function buscarPacientesLocal(query) {
    const ulResultados = document.getElementById('ul_resultados_pac');
    const tbody = document.getElementById('tbl_resultados_pac_body');

    if (!query) {
        ulResultados.style.display = 'none';
        return;
    }

    try {
        const res = await pacientesService.obtenerPacientes(query);
        if (res.ok && res.data) {
            const pacientes = Array.isArray(res.data.data) ? res.data.data : (res.data.data?.data || []);
            if (pacientes.length === 0) {
                tbody.innerHTML = '<tr><td style="color:var(--color-text-muted);">Sin resultados</td></tr>';
            } else {
                tbody.innerHTML = pacientes.map(p => `
                    <tr style="cursor:pointer;" onclick="seleccionarPaciente(${JSON.stringify(p).replace(/"/g, '&quot;')})">
                        <td><strong>${p.nombre || p.usuario?.nombre}</strong></td>
                        <td>${p.numero_expediente || 'EXP-' + String(p.id).padStart(4, '0')}</td>
                        <td>${p.curp || ''}</td>
                    </tr>
                `).join('');
            }
            ulResultados.style.display = 'block';
        }
    } catch (e) {
        console.error(e);
    }
}

window.seleccionarPaciente = (p) => {
    state.paciente = p;
    document.getElementById('sel_pac_nombre').textContent = p.nombre || p.usuario?.nombre;
    document.getElementById('sel_pac_exp').textContent = p.numero_expediente || 'EXP-' + String(p.id).padStart(4, '0');
    document.getElementById('sel_pac_curp').textContent = p.curp || 'N/A';

    document.getElementById('card_pac_seleccionado').style.display = 'block';
    document.getElementById('ul_resultados_pac').style.display = 'none';
    document.getElementById('btn_ir_paso_2').disabled = false;
};

async function cargarEspecialidadesWizard() {
    try {
        const res = await doctoresService.obtenerEspecialidades();
        if (res.ok && Array.isArray(res.data.data)) {
            const select = document.getElementById('sel_especialidad_cita');
            select.innerHTML = '<option value="">Seleccione Especialidad...</option>' +
                res.data.data.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
        }
    } catch (e) {
        console.error(e);
    }
}

async function cargarDoctoresPorEspecialidad(especialidadId) {
    try {
        const res = await doctoresService.obtenerDoctores(especialidadId);
        const selDoctor = document.getElementById('sel_doctor_cita');
        if (res.ok && Array.isArray(res.data.data)) {
            selDoctor.innerHTML = '<option value="">Seleccione Doctor...</option>' +
                res.data.data.map(d => `<option value="${d.id}">Dr. ${d.nombre}</option>`).join('');
            selDoctor.disabled = false;
        }
    } catch (e) {
        console.error(e);
    }
}

async function consultarSlotsDisponibles() {
    const divSlots = document.getElementById('div_slots_hora');
    divSlots.innerHTML = '<p style="color:var(--color-text-muted); font-size:13px; grid-column:1/-1;">Consultando horarios disponibles...</p>';

    try {
        const res = await doctoresService.obtenerDisponibilidad(state.doctorId, state.fecha);
        if (res.ok && Array.isArray(res.data.slots_disponibles)) {
            const slots = res.data.slots_disponibles;

            if (slots.length === 0) {
                divSlots.innerHTML = '<p style="color:var(--color-danger); font-size:13px; grid-column:1/-1;">El médico no cuenta con horarios libres en la fecha seleccionada.</p>';
                return;
            }

            divSlots.innerHTML = slots.map(hora => `
                <div class="time-slot-pill" onclick="seleccionarHoraSlot(this, '${hora}')">${hora}</div>
            `).join('');
        } else {
            divSlots.innerHTML = '<p style="color:var(--color-danger); font-size:13px; grid-column:1/-1;">Sin horarios disponibles.</p>';
        }
    } catch (e) {
        divSlots.innerHTML = '<p style="color:var(--color-danger); font-size:13px; grid-column:1/-1;">Error al consultar horarios.</p>';
    }
}

window.seleccionarHoraSlot = (el, hora) => {
    document.querySelectorAll('.time-slot-pill').forEach(pill => pill.classList.remove('time-slot-pill--selected'));
    el.classList.add('time-slot-pill--selected');
    state.hora = hora;
    document.getElementById('btn_ir_paso_3').disabled = false;
};

function limpiarSlots() {
    state.hora = null;
    document.getElementById('div_slots_hora').innerHTML = '<p style="color:var(--color-text-muted); font-size:13px; grid-column:1/-1;">Seleccione un doctor y una fecha para consultar la disponibilidad.</p>';
    document.getElementById('btn_ir_paso_3').disabled = true;
}

function switchWizardStep(step) {
    for (let i = 1; i <= 3; i++) {
        document.getElementById(`step_container_${i}`).classList.add('hidden');
        document.getElementById(`step_indicator_${i}`).classList.remove('wizard-step--active');
    }

    document.getElementById(`step_container_${step}`).classList.remove('hidden');
    document.getElementById(`step_indicator_${step}`).classList.add('wizard-step--active');
}

function prepararResumenPaso3() {
    document.getElementById('conf_paciente').textContent = state.paciente?.nombre || 'Paciente';
    document.getElementById('conf_doctor').textContent = state.doctorNombre;
    document.getElementById('conf_especialidad').textContent = state.especialidadNombre;
    document.getElementById('conf_fecha_hora').textContent = `${state.fecha} a las ${state.hora}`;
    document.getElementById('conf_motivo').textContent = state.motivo;
}
