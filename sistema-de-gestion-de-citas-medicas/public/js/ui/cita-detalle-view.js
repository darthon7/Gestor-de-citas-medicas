// js/ui/cita-detalle-view.js - Lógica del Detalle de Cita
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { citasService } from '../api/citas-service.js';
import { formatFullDateTime, getStatusBadgeHtml } from '../utils/formatters.js';
import { modal } from '../utils/modal.js';
import { notify } from '../utils/notifications.js';

let currentCitaId = null;

document.addEventListener('DOMContentLoaded', async () => {
    if (!checkRole(['admin', 'recepcionista', 'doctor'])) return;

    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    if (user.rol === 'doctor') {
        document.querySelectorAll('.nav-admin-only').forEach(el => el.classList.add('oculto'));
    }

    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    const params = new URLSearchParams(window.location.search);
    currentCitaId = params.get('id');

    if (!currentCitaId) {
        window.location.href = '/citas.html';
        return;
    }

    await cargarDetalleCita();

    // Confirmar Cancelación
    document.getElementById('btn_confirmar_cancelacion').addEventListener('click', async () => {
        const motivo = document.getElementById('txt_motivo_cancelacion').value.trim();
        if (!motivo) {
            notify.error('Debe ingresar un motivo de cancelación.');
            return;
        }

        try {
            const res = await citasService.cancelarCita(currentCitaId, motivo);
            if (res.ok) {
                notify.success('Cita cancelada correctamente.');
                modal.close('modal_cancelar_cita');
                await cargarDetalleCita();
            } else {
                notify.error(res.data?.mensaje || 'Error al cancelar la cita.');
            }
        } catch (e) {
            notify.error('Error al procesar la cancelación.');
        }
    });
});

async function cargarDetalleCita() {
    try {
        const res = await citasService.obtenerCita(currentCitaId);
        if (res.ok && res.data) {
            const cita = res.data.data || res.data;
            const user = getCurrentUser();

            document.getElementById('lbl_detalle_estado').innerHTML = getStatusBadgeHtml(cita.estado);
            document.getElementById('lbl_codigo_ref').textContent = cita.codigo_referencia || `REF-2026-${String(cita.id).padStart(4, '0')}`;
            document.getElementById('lbl_det_fecha_hora').textContent = formatFullDateTime(cita.fecha_hora);
            document.getElementById('lbl_det_paciente').textContent = cita.paciente?.nombre || 'N/A';
            document.getElementById('lbl_det_doctor').textContent = `Dr. ${cita.doctor?.nombre || 'N/A'}`;
            document.getElementById('lbl_det_especialidad').textContent = cita.especialidad?.nombre || 'General';
            document.getElementById('lbl_det_motivo').textContent = cita.motivo_consulta || 'Consulta médica de seguimiento';

            // Notas médicas si está completada
            if (cita.nota_consulta || cita.diagnostico) {
                document.getElementById('card_notas_consulta').classList.remove('hidden');
                document.getElementById('lbl_nota_diag').textContent = cita.nota_consulta?.diagnostico || cita.diagnostico || 'Sin diagnóstico registrado';
                document.getElementById('lbl_nota_trat').textContent = cita.nota_consulta?.tratamiento || cita.tratamiento || 'Sin tratamiento registrado';
            }

            // Renderizar botones de acción según estado y rol
            renderAcciones(cita, user.rol);
        }
    } catch (e) {
        notify.error('Error al cargar el detalle de la cita.');
    }
}

function renderAcciones(cita, rol) {
    const container = document.getElementById('container_acciones');
    let html = '';

    const estado = (cita.estado || '').toLowerCase();

    if (rol === 'admin' || rol === 'recepcionista') {
        if (estado === 'agendada' || estado === 'pendiente') {
            html += `<button onclick="ejecutarCheckIn()" class="btn btn-primary" style="height:44px;"><i data-lucide="check-square"></i> Registrar Llegada de Paciente (Check-in)</button>`;
            html += `<button onclick="abrirModalCancelar()" class="btn btn-outline-danger" style="height:44px;"><i data-lucide="x-circle"></i> Cancelar Cita</button>`;
        } else if (estado === 'confirmada') {
            html += `<button onclick="abrirModalCancelar()" class="btn btn-outline-danger" style="height:44px;"><i data-lucide="x-circle"></i> Cancelar Cita</button>`;
        }
    }

    if (rol === 'doctor') {
        if (estado === 'confirmada') {
            html += `<a href="/diagnostico.html?citaId=${cita.id}" class="btn btn-secondary" style="height:44px;"><i data-lucide="play-circle"></i> Iniciar Consulta</a>`;
        } else if (estado === 'en_consulta') {
            html += `<a href="/diagnostico.html?citaId=${cita.id}" class="btn btn-primary" style="height:44px;"><i data-lucide="clipboard-edit"></i> Registrar Diagnóstico</a>`;
        }
    }

    container.innerHTML = html;
    if (window.lucide) lucide.createIcons();
}

window.ejecutarCheckIn = async () => {
    try {
        const res = await citasService.checkInCita(currentCitaId);
        if (res.ok) {
            notify.success('Llegada del paciente confirmada.');
            await cargarDetalleCita();
        }
    } catch (e) {
        notify.error('Error al hacer check-in.');
    }
};

window.abrirModalCancelar = () => {
    document.getElementById('txt_motivo_cancelacion').value = '';
    modal.open('modal_cancelar_cita');
};
