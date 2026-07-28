// js/ui/especialidades-view.js - Lógica de Especialidades Médicas
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { doctoresService } from '../api/doctores-service.js';
import { apiFetch } from '../api/config.js';
import { modal } from '../utils/modal.js';
import { notify } from '../utils/notifications.js';

document.addEventListener('DOMContentLoaded', async () => {
    if (!checkRole(['admin'])) return;

    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    await cargarEspecialidades();

    document.getElementById('btn_abrir_modal_especialidad').addEventListener('click', () => {
        document.getElementById('form_especialidad').reset();
        modal.open('modal_especialidad');
    });

    document.getElementById('form_especialidad').addEventListener('submit', async (e) => {
        e.preventDefault();
        const nombre = document.getElementById('txt_nombre_esp').value.trim();

        if (!nombre) return;

        const btn = document.getElementById('btn_guardar_esp');
        btn.disabled = true;

        try {
            const res = await apiFetch('/registrarEspecialidad', {
                method: 'POST',
                body: JSON.stringify({ nombre })
            });

            if (res.ok) {
                notify.success('Especialidad registrada correctamente.');
                modal.close('modal_especialidad');
                await cargarEspecialidades();
            } else {
                notify.error(res.data?.mensaje || 'Error al registrar especialidad.');
            }
        } catch (err) {
            notify.error('Ocurrió un error al procesar el registro.');
        } finally {
            btn.disabled = false;
        }
    });
});

async function cargarEspecialidades() {
    const tbody = document.getElementById('tabla_especialidades_body');
    try {
        const res = await doctoresService.obtenerEspecialidades();
        if (res.ok && Array.isArray(res.data.data)) {
            const especialidades = res.data.data;
            if (especialidades.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--color-text-muted);">No hay especialidades registradas.</td></tr>';
                return;
            }

            tbody.innerHTML = especialidades.map(e => `
                <tr>
                    <td style="font-weight:700; color:var(--color-primary);">#${e.id}</td>
                    <td style="font-weight:600;">${e.nombre}</td>
                    <td>${e.doctores_count || e.doctores?.length || 0} doctores</td>
                    <td style="text-align:right;"><span class="badge badge--success">Activa</span></td>
                </tr>
            `).join('');
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--color-danger);">Error al cargar las especialidades.</td></tr>';
    }
}
