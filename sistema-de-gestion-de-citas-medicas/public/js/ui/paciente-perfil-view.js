// js/ui/paciente-perfil-view.js - Lógica del Perfil y Expediente de Paciente
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { pacientesService } from '../api/pacientes-service.js';
import { formatDate, formatTime, getStatusBadgeHtml } from '../utils/formatters.js';

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

    // Obtener ID del URL
    const params = new URLSearchParams(window.location.search);
    const pacienteId = params.get('id');

    if (!pacienteId) {
        window.location.href = '/pacientes.html';
        return;
    }

    await cargarDatosPaciente(pacienteId);
});

async function cargarDatosPaciente(id) {
    try {
        const res = await pacientesService.obtenerPaciente(id);
        if (res.ok && res.data) {
            const p = res.data.data || res.data;

            const nombre = p.nombre || p.usuario?.nombre || 'Paciente';
            document.getElementById('lbl_paciente_nombre').textContent = nombre;
            document.getElementById('lbl_profile_avatar').textContent = nombre.charAt(0).toUpperCase();
            document.getElementById('lbl_paciente_expediente').textContent = p.numero_expediente || 'EXP-' + String(p.id).padStart(4, '0');
            document.getElementById('lbl_paciente_curp').textContent = p.curp || 'N/A';
            document.getElementById('lbl_paciente_telefono').textContent = p.telefono || 'N/A';
            document.getElementById('lbl_paciente_email').textContent = p.email || p.usuario?.email || 'N/A';
            document.getElementById('lbl_paciente_estado').innerHTML = getStatusBadgeHtml(p.estado || 'Activo');

            // Tab 1 Info
            document.getElementById('lbl_info_nacimiento').textContent = formatDate(p.fecha_nacimiento);
            document.getElementById('lbl_info_sexo').textContent = p.sexo || 'N/A';
            document.getElementById('lbl_info_direccion').textContent = p.direccion || 'N/A';

            // Tab 2 Citas
            renderHistorialCitas(p.citas || []);

            // Tab 3 Diagnósticos
            renderDiagnosticos(p.citas || []);
        }
    } catch (e) {
        console.error(e);
    }
}

function renderHistorialCitas(citas) {
    const container = document.getElementById('container_historial_citas');
    if (!citas || citas.length === 0) {
        container.innerHTML = '<div class="card"><p style="color:var(--color-text-muted);">El paciente no registra citas anteriores.</p></div>';
        return;
    }

    container.innerHTML = citas.map(cita => {
        const dateObj = new Date(cita.fecha_hora);
        const day = dateObj.getDate();
        const month = dateObj.toLocaleDateString('es-MX', { month: 'short' });

        return `
            <div class="timeline-item">
                <div style="display:flex; align-items:center; gap:20px;">
                    <div class="timeline-date-box">
                        <span style="font-size:18px;">${day}</span>
                        <span style="text-transform:uppercase;">${month}</span>
                    </div>
                    <div>
                        <h4 style="font-size:16px;">Dr. ${cita.doctor?.nombre || 'Médico'} — ${cita.especialidad?.nombre || 'Consulta'}</h4>
                        <span style="font-size:13px; color:var(--color-text-secondary);">${formatTime(cita.fecha_hora)}</span>
                    </div>
                </div>
                <div>
                    ${getStatusBadgeHtml(cita.estado)}
                    <a href="cita-detalle.html?id=${cita.id}" class="btn btn-outline" style="margin-left:12px; padding:6px 12px; font-size:12px;">Ver Detalle</a>
                </div>
            </div>
        `;
    }).join('');
}

function renderDiagnosticos(citas) {
    const container = document.getElementById('container_diagnosticos');
    const citasConNotas = citas.filter(c => c.nota_consulta || c.diagnostico);

    if (citasConNotas.length === 0) {
        container.innerHTML = '<div class="card"><p style="color:var(--color-text-muted);">No se encuentran diagnósticos o notas médicas registradas.</p></div>';
        return;
    }

    container.innerHTML = citasConNotas.map(cita => `
        <div class="card" style="margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <strong>${formatDate(cita.fecha_hora)} - Dr. ${cita.doctor?.nombre || 'Médico'}</strong>
                <span class="badge badge--success">Completada</span>
            </div>
            <p><strong>Diagnóstico:</strong> ${cita.nota_consulta?.diagnostico || cita.diagnostico || 'Sin descripción'}</p>
            <p style="margin-top:6px;"><strong>Tratamiento:</strong> ${cita.nota_consulta?.tratamiento || cita.tratamiento || 'Sin tratamiento registrado'}</p>
        </div>
    `).join('');
}

// Tab Switcher
window.switchTab = (tabName) => {
    document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

    document.getElementById(`tab_btn_${tabName}`).classList.add('active');
    document.getElementById(`tab_content_${tabName}`).classList.remove('hidden');
};
