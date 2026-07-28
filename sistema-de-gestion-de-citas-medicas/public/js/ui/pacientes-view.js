// js/ui/pacientes-view.js - Lógica de Gestión de Pacientes
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { pacientesService } from '../api/pacientes-service.js';
import { modal } from '../utils/modal.js';
import { notify } from '../utils/notifications.js';
import { getStatusBadgeHtml } from '../utils/formatters.js';
import { validarCURP, validarEmail, validarTelefono } from '../utils/validators.js';

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

    // Cargar pacientes
    await cargarPacientes();

    // Búsqueda con Debounce
    let timerBusqueda;
    document.getElementById('inp_buscar_paciente').addEventListener('input', (e) => {
        clearTimeout(timerBusqueda);
        timerBusqueda = setTimeout(() => {
            cargarPacientes(e.target.value.trim());
        }, 400);
    });

    // Abrir Modal para Crear
    document.getElementById('btn_abrir_modal_paciente').addEventListener('click', () => {
        document.getElementById('form_paciente').reset();
        document.getElementById('txt_paciente_id').value = '';
        document.getElementById('modal_paciente_title').textContent = 'Registrar Nuevo Paciente';
        document.getElementById('btn_guardar_paciente').textContent = 'Registrar Paciente';
        modal.open('modal_paciente');
    });

    // Guardar (Crear / Editar)
    document.getElementById('form_paciente').addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.getElementById('txt_paciente_id').value;
        const nombre = document.getElementById('txt_nombre_pac').value.trim();
        const fecha_nacimiento = document.getElementById('inp_fecha_nac').value;
        const sexo = document.getElementById('sel_sexo').value;
        const curp = document.getElementById('txt_curp').value.trim().toUpperCase();
        const telefono = document.getElementById('txt_telefono_pac').value.trim();
        const email = document.getElementById('txt_email_pac').value.trim();
        const direccion = document.getElementById('txt_direccion').value.trim();

        // Validaciones
        if (!validarCURP(curp)) {
            notify.error('La CURP ingresada no tiene un formato válido.');
            return;
        }
        if (!validarTelefono(telefono)) {
            notify.error('El teléfono debe contener exactamente 10 dígitos.');
            return;
        }
        if (!validarEmail(email)) {
            notify.error('Ingrese un correo electrónico válido.');
            return;
        }

        const datos = { nombre, fecha_nacimiento, sexo, curp, telefono, email, direccion };
        const btnSave = document.getElementById('btn_guardar_paciente');
        btnSave.disabled = true;

        try {
            let res;
            if (id) {
                res = await pacientesService.actualizarPaciente(id, datos);
            } else {
                res = await pacientesService.registrarPaciente(datos);
            }

            if (res.ok) {
                notify.success(id ? 'Paciente actualizado con éxito.' : 'Paciente registrado correctamente.');
                modal.close('modal_paciente');
                await cargarPacientes();
            } else {
                notify.error(res.data?.mensaje || res.data?.msj || 'Error al guardar los datos del paciente.');
            }
        } catch (err) {
            notify.error('Ocurrió un error al procesar la solicitud.');
        } finally {
            btnSave.disabled = false;
        }
    });
});

async function cargarPacientes(query = '') {
    const tbody = document.getElementById('tabla_pacientes_body');
    const lblTotal = document.getElementById('lbl_total_pacientes');

    try {
        const res = await pacientesService.obtenerPacientes(query);
        if (res.ok && res.data) {
            const pacientes = Array.isArray(res.data.data) ? res.data.data : (res.data.data?.data || []);
            const total = res.data.total || pacientes.length;

            lblTotal.textContent = `${total} pacientes registrados`;

            if (pacientes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--color-text-muted);">No se encontraron pacientes.</td></tr>';
                return;
            }

            tbody.innerHTML = pacientes.map(p => `
                <tr>
                    <td style="font-weight: 700; color: var(--color-primary);">${p.numero_expediente || 'EXP-' + String(p.id).padStart(4, '0')}</td>
                    <td style="font-weight: 600;">${p.nombre || p.usuario?.nombre || 'N/A'}</td>
                    <td style="font-family: monospace; font-size: 13px;">${p.curp || 'N/A'}</td>
                    <td>${p.telefono || 'N/A'}</td>
                    <td>${p.email || p.usuario?.email || 'N/A'}</td>
                    <td>${getStatusBadgeHtml(p.estado || 'Activo')}</td>
                    <td style="text-align: right;">
                        <button onclick="verPerfilPaciente(${p.id})" class="btn-icon" title="Ver Perfil">
                            <i data-lucide="eye"></i>
                        </button>
                        <button onclick="prepararEdicionPaciente(${p.id})" class="btn-icon" title="Editar">
                            <i data-lucide="pencil"></i>
                        </button>
                        <button onclick="desactivarPaciente(${p.id})" class="btn-icon" title="Desactivar" style="color: var(--color-danger);">
                            <i data-lucide="user-x"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

            if (window.lucide) lucide.createIcons();
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--color-danger);">Error al cargar los pacientes.</td></tr>';
    }
}

// Funciones globales de tabla
window.verPerfilPaciente = (id) => {
    window.location.href = `/paciente-perfil.html?id=${id}`;
};

window.prepararEdicionPaciente = async (id) => {
    try {
        const res = await pacientesService.obtenerPaciente(id);
        if (res.ok && res.data) {
            const p = res.data.data || res.data;
            document.getElementById('txt_paciente_id').value = p.id;
            document.getElementById('txt_nombre_pac').value = p.nombre || p.usuario?.nombre || '';
            document.getElementById('inp_fecha_nac').value = p.fecha_nacimiento || '';
            document.getElementById('sel_sexo').value = p.sexo || '';
            document.getElementById('txt_curp').value = p.curp || '';
            document.getElementById('txt_telefono_pac').value = p.telefono || '';
            document.getElementById('txt_email_pac').value = p.email || p.usuario?.email || '';
            document.getElementById('txt_direccion').value = p.direccion || '';

            document.getElementById('modal_paciente_title').textContent = 'Editar Paciente';
            document.getElementById('btn_guardar_paciente').textContent = 'Guardar Cambios';
            modal.open('modal_paciente');
        }
    } catch (e) {
        notify.error('No se pudo cargar la información del paciente.');
    }
};

window.desactivarPaciente = async (id) => {
    if (confirm('¿Está seguro de que desea desactivar este paciente?')) {
        try {
            const res = await pacientesService.desactivarPaciente(id);
            if (res.ok) {
                notify.success('Paciente desactivado correctamente.');
                cargarPacientes();
            } else {
                notify.error(res.data?.mensaje || 'No se pudo desactivar el paciente.');
            }
        } catch (e) {
            notify.error('Error al procesar la desactivación.');
        }
    }
};
