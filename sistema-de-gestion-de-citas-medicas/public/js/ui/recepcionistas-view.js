// js/ui/recepcionistas-view.js - Lógica de Gestión de Recepcionistas
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { apiFetch } from '../api/config.js';
import { modal } from '../utils/modal.js';
import { notify } from '../utils/notifications.js';
import { validarEmail } from '../utils/validators.js';

document.addEventListener('DOMContentLoaded', async () => {
    if (!checkRole(['admin'])) return;

    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    await cargarRecepcionistas();

    document.getElementById('btn_abrir_modal_recep').addEventListener('click', () => {
        document.getElementById('form_recep').reset();
        modal.open('modal_recep');
    });

    document.getElementById('form_recep').addEventListener('submit', async (e) => {
        e.preventDefault();

        const nombre = document.getElementById('txt_nombre_recep').value.trim();
        const email = document.getElementById('txt_email_recep').value.trim();
        const password = document.getElementById('txt_pass_recep').value.trim();
        const password_confirmation = document.getElementById('txt_pass_conf_recep').value.trim();

        if (!validarEmail(email)) {
            notify.error('Ingrese un correo institucional válido.');
            return;
        }

        if (password !== password_confirmation) {
            notify.error('Las contraseñas no coinciden.');
            return;
        }

        const btn = document.getElementById('btn_guardar_recep');
        btn.disabled = true;

        try {
            const res = await apiFetch('/auth/registrarRecepcionista', {
                method: 'POST',
                body: JSON.stringify({ nombre, email, password, password_confirmation })
            });

            if (res.ok) {
                notify.success('Cuenta de recepcionista creada exitosamente.');
                modal.close('modal_recep');
                await cargarRecepcionistas();
            } else {
                notify.error(res.data?.mensaje || res.data?.msj || 'Error al registrar la recepcionista.');
            }
        } catch (err) {
            notify.error('Ocurrió un error en la solicitud.');
        } finally {
            btn.disabled = false;
        }
    });
});

async function cargarRecepcionistas() {
    const tbody = document.getElementById('tabla_recep_body');
    try {
        // Consultar usuarios con rol recepcionista o filtro
        const res = await apiFetch('/obtenerPacientes?rol=recepcionista', { method: 'GET' });
        // Si el backend expone un endpoint especifico o filtrado
        const lista = (res.ok && res.data?.data) ? res.data.data : [];

        if (lista.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--color-text-muted);">No hay recepcionistas registradas.</td></tr>';
            return;
        }

        tbody.innerHTML = lista.map(r => `
            <tr>
                <td style="font-weight:600;">${r.nombre || r.usuario?.nombre || 'Recepcionista'}</td>
                <td>${r.email || r.usuario?.email || 'N/A'}</td>
                <td><span class="badge badge--info">Recepcionista</span></td>
                <td><span class="badge badge--success">Activo</span></td>
                <td style="text-align:right;">
                    <span style="font-size:12px; color:var(--color-text-muted);">Registrado</span>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--color-danger);">Error al cargar recepcionistas.</td></tr>';
    }
}
