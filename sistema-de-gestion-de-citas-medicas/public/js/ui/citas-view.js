// js/ui/citas-view.js - Lógica del Calendario de Citas
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { doctoresService } from '../api/doctores-service.js';
import { citasService } from '../api/citas-service.js';
import { formatTime, getStatusBadgeHtml } from '../utils/formatters.js';

let currentDate = new Date();

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

    // Cargar Doctores en Filtro
    await cargarDoctoresFiltro();

    // Renderizar horas del eje vertical (8:00 a 18:00)
    renderTimeSlots();

    // Cargar Citas de la semana activa
    await cargarCitasSemana();

    // Controles de fecha
    document.getElementById('btn_anterior_semana').addEventListener('click', async () => {
        currentDate.setDate(currentDate.getDate() - 7);
        await cargarCitasSemana();
    });

    document.getElementById('btn_siguiente_semana').addEventListener('click', async () => {
        currentDate.setDate(currentDate.getDate() + 7);
        await cargarCitasSemana();
    });

    document.getElementById('sel_filtro_doctor_cal').addEventListener('change', async () => {
        await cargarCitasSemana();
    });
});

function renderTimeSlots() {
    const timeCol = document.getElementById('col_time');
    let html = '';
    for (let h = 8; h <= 18; h++) {
        const hourStr = h < 12 ? `${h}:00 AM` : (h === 12 ? '12:00 PM' : `${h-12}:00 PM`);
        html += `<div class="calendar-time-slot-label">${hourStr}</div>`;
    }
    timeCol.innerHTML = html;
}

async function cargarDoctoresFiltro() {
    try {
        const res = await doctoresService.obtenerDoctores();
        if (res.ok && Array.isArray(res.data.data)) {
            const select = document.getElementById('sel_filtro_doctor_cal');
            select.innerHTML = '<option value="">Todos los doctores</option>' +
                res.data.data.map(d => `<option value="${d.id}">Dr. ${d.nombre}</option>`).join('');
        }
    } catch (e) {
        console.error(e);
    }
}

async function cargarCitasSemana() {
    const doctorId = document.getElementById('sel_filtro_doctor_cal').value;

    // Calcular inicio (lunes) y fin (domingo) de la semana
    const start = new Date(currentDate);
    const day = start.getDay();
    const diff = start.getDate() - day + (day === 0 ? -6 : 1);
    start.setDate(diff);

    const end = new Date(start);
    end.setDate(end.getDate() + 6);

    // Texto de Rango
    const formatRange = new Intl.DateTimeFormat('es-MX', { day: 'numeric', month: 'short' });
    document.getElementById('lbl_rango_fecha').textContent =
        `${formatRange.format(start)} - ${formatRange.format(end)}, ${end.getFullYear()}`;

    // Limpiar columnas de días
    for (let i = 0; i < 7; i++) {
        const col = document.getElementById(`day_col_${i}`);
        if (col) col.innerHTML = '';
    }

    try {
        const params = {
            fecha_inicio: start.toISOString().split('T')[0],
            fecha_fin: end.toISOString().split('T')[0]
        };
        if (doctorId) params.doctor_id = doctorId;

        const res = await citasService.obtenerCitas(params);
        if (res.ok && Array.isArray(res.data.data)) {
            const citas = res.data.data;
            renderCitasEnGrid(citas, start);
            renderResumenPanel(citas);
        }
    } catch (e) {
        console.error(e);
    }
}

function renderCitasEnGrid(citas, weekStart) {
    citas.forEach(cita => {
        const date = new Date(cita.fecha_hora.replace(' ', 'T'));
        const dayDiff = Math.floor((date - weekStart) / (1000 * 60 * 60 * 24));

        if (dayDiff >= 0 && dayDiff < 7) {
            const col = document.getElementById(`day_col_${dayDiff}`);
            if (!col) return;

            const hour = date.getHours();
            const minutes = date.getMinutes();

            // 8:00 AM es offset 0px. Cada hora mide 60px.
            const topPx = ((hour - 8) * 60) + minutes;
            const estadoClass = (cita.estado || '').toLowerCase();

            const block = document.createElement('div');
            block.className = `appointment-block appointment-block--${estadoClass}`;
            block.style.top = `${topPx}px`;
            block.style.height = `52px`;
            block.innerHTML = `
                <strong style="font-size:10px;">${formatTime(cita.fecha_hora)}</strong>
                <span style="font-weight:600;">${cita.paciente?.nombre || 'Paciente'}</span>
                <span style="font-size:10px; opacity:0.8;">Dr. ${cita.doctor?.nombre || 'Doc'}</span>
            `;

            block.onclick = () => {
                window.location.href = `/cita-detalle.html?id=${cita.id}`;
            };

            col.appendChild(block);
        }
    });
}

function renderResumenPanel(citas) {
    document.getElementById('summary_total').textContent = citas.length;
    document.getElementById('summary_confirmadas').textContent = citas.filter(c => c.estado === 'confirmada' || c.estado === 'completada').length;
    document.getElementById('summary_canceladas').textContent = citas.filter(c => c.estado === 'cancelada').length;

    const container = document.getElementById('container_resumen_hoy');
    if (citas.length === 0) {
        container.innerHTML = '<p style="color:var(--color-text-muted); font-size:12px;">Sin citas para esta semana.</p>';
        return;
    }

    container.innerHTML = citas.slice(0, 5).map(cita => `
        <div style="background-color: var(--color-bg); padding: 10px; border-radius: 6px; border: 1px solid var(--color-border); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <strong style="font-size:13px;">${cita.paciente?.nombre || 'Paciente'}</strong>
                <div style="font-size:11px; color:var(--color-text-secondary);">Dr. ${cita.doctor?.nombre || 'Médico'}</div>
            </div>
            <span style="font-weight:700; font-size:12px; color:var(--color-primary);">${formatTime(cita.fecha_hora)}</span>
        </div>
    `).join('');
}
