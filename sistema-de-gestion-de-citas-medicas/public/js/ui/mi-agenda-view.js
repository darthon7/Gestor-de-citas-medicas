// js/ui/mi-agenda-view.js - Lógica de la Agenda Médica del Doctor
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { citasService } from '../api/citas-service.js';
import { formatTime, getStatusBadgeHtml } from '../utils/formatters.js';
import { notify } from '../utils/notifications.js';

document.addEventListener('DOMContentLoaded', async () => {
    if (!checkRole(['doctor'])) return;

    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    // Saludo
    const horaActual = new Date().getHours();
    const saludo = horaActual < 12 ? 'Buenos días' : (horaActual < 19 ? 'Buenas tardes' : 'Buenas noches');
    document.getElementById('lbl_saludo_doc').textContent = `${saludo}, ${user.nombre}`;

    const hoyStr = new Intl.DateTimeFormat('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date());
    document.getElementById('lbl_fecha_hoy').textContent = hoyStr;

    await cargarAgendaDiaDoctor();
});

async function cargarAgendaDiaDoctor() {
    const container = document.getElementById('container_agenda_doctor');
    const lblResumen = document.getElementById('lbl_resumen_consultas');
    const user = getCurrentUser();

    try {
        const hoy = new Date().toISOString().split('T')[0];
        const res = await citasService.obtenerCitas({ doctor_id: user.id, fecha: hoy });

        if (res.ok && Array.isArray(res.data.data)) {
            const citas = res.data.data;
            lblResumen.textContent = `Hoy tienes ${citas.length} consultas programadas.`;

            if (citas.length === 0) {
                container.innerHTML = '<div class="card"><p style="color:var(--color-text-muted);">No registra consultas agendadas para el día de hoy.</p></div>';
                return;
            }

            container.innerHTML = citas.map(cita => {
                const estado = (cita.estado || '').toLowerCase();

                let botonAccion = '';
                if (estado === 'confirmada') {
                    botonAccion = `<button onclick="iniciarConsultaDoctor(${cita.id})" class="btn btn-secondary"><i data-lucide="play-circle"></i> Iniciar Consulta</button>`;
                } else if (estado === 'en_consulta') {
                    botonAccion = `<a href="diagnostico.html?citaId=${cita.id}" class="btn btn-primary"><span class="pulsing-dot"></span> Registrar Diagnóstico</a>`;
                } else if (estado === 'completada') {
                    botonAccion = `<a href="cita-detalle.html?id=${cita.id}" class="btn btn-outline" style="font-size:12px;">Ver Expediente</a>`;
                } else {
                    botonAccion = `<span class="badge badge--warning">Pendiente Check-in</span>`;
                }

                return `
                    <div class="agenda-card agenda-card--${estado}">
                        <div class="agenda-card__time">${formatTime(cita.fecha_hora)}</div>

                        <div class="agenda-card__patient-info">
                            <span class="agenda-card__patient-name">${cita.paciente?.nombre || 'Paciente'}</span>
                            <span class="agenda-card__reason">Motivo: ${cita.motivo_consulta || 'Consulta general'}</span>
                        </div>

                        <div style="display:flex; align-items:center; gap:16px;">
                            ${getStatusBadgeHtml(cita.estado)}
                            ${botonAccion}
                        </div>
                    </div>
                `;
            }).join('');

            if (window.lucide) lucide.createIcons();
        }
    } catch (e) {
        container.innerHTML = '<p style="color:var(--color-danger);">Error al cargar la agenda médica.</p>';
    }
}

window.iniciarConsultaDoctor = async (id) => {
    try {
        const res = await citasService.iniciarConsulta(id);
        if (res.ok) {
            notify.success('Consulta iniciada.');
            window.location.href = `/diagnostico.html?citaId=${id}`;
        }
    } catch (e) {
        notify.error('Error al iniciar la consulta.');
    }
};
