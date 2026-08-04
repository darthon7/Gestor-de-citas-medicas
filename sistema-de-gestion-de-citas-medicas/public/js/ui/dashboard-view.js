// js/ui/dashboard-view.js - Lógica del Dashboard Principal
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { apiFetch } from '../api/config.js';
import { formatTime, getStatusBadgeHtml } from '../utils/formatters.js';

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Guard de acceso (admin o recepcionista)
    if (!checkRole(['admin', 'recepcionista'])) return;

    // 2. Cargar datos de usuario
    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    // Ocultar elementos exclusivos de Admin si es Recepcionista
    if (user.rol === 'recepcionista') {
        document.querySelectorAll('.nav-admin-only').forEach(el => el.classList.add('oculto'));
    }

    // Botón logout
    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    // Fecha actual
    const hoy = new Date();
    document.getElementById('lbl_current_date').textContent = new Intl.DateTimeFormat('es-MX', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    }).format(hoy);

    // 3. Cargar Resumen Estadístico
    await cargarResumenStats();

    // 4. Cargar Tabla Agenda del Día
    await cargarAgendaDia();
});

async function cargarResumenStats() {
    try {
        const res = await apiFetch('/resumenDiario', { method: 'GET' });
        if (res.ok && res.data) {
            document.getElementById('stat_total_dia').textContent = res.data.total_citas || 0;
            document.getElementById('stat_completadas').textContent = res.data.completadas || 0;
            document.getElementById('stat_pendientes').textContent = res.data.pendientes || 0;
            document.getElementById('stat_canceladas').textContent = res.data.canceladas || 0;
        }
    } catch (e) {
        console.error('Error cargando stats resumen:', e);
    }
}

async function cargarAgendaDia() {
    const tablaBody = document.getElementById('tabla_agenda_body');
    const listaProximas = document.getElementById('lista_proximas_citas');

    try {
        const res = await apiFetch('/obtenerCitas', { method: 'GET' });
        if (res.ok && Array.isArray(res.data.data)) {
            const citas = res.data.data;

            if (citas.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--color-text-muted);">No hay citas programadas para hoy.</td></tr>';
                listaProximas.innerHTML = '<p style="color:var(--color-text-muted); font-size:13px;">No hay próximas citas.</p>';
                return;
            }

            // Render Tabla Agenda
            tablaBody.innerHTML = citas.map(cita => `
                <tr>
                    <td style="font-weight: 600;">${formatTime(cita.fecha_hora)}</td>
                    <td>${cita.paciente?.nombre || 'N/A'}</td>
                    <td>Dr. ${cita.doctor?.nombre || 'N/A'}</td>
                    <td>${cita.especialidad?.nombre || 'General'}</td>
                    <td>${getStatusBadgeHtml(cita.estado)}</td>
                </tr>
            `).join('');

            // Render Próximas Citas (primeras 4)
            const proximas = citas.slice(0, 4);
            listaProximas.innerHTML = proximas.map(cita => `
                <div class="upcoming-item">
                    <div class="upcoming-item__info">
                        <span class="upcoming-item__patient">${cita.paciente?.nombre || 'Paciente'}</span>
                        <span class="upcoming-item__doctor">Dr. ${cita.doctor?.nombre || 'Doctor'} — ${cita.especialidad?.nombre || 'Especialidad'}</span>
                    </div>
                    <span class="upcoming-item__time">${formatTime(cita.fecha_hora)}</span>
                </div>
            `).join('');
        }
    } catch (e) {
        tablaBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--color-danger);">Error al cargar la agenda del día.</td></tr>';
        listaProximas.innerHTML = '<p style="color:var(--color-danger); font-size:13px;">Error al cargar datos.</p>';
    }
}
