// js/ui/doctores-view.js - Lógica de Gestión de Doctores
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { doctoresService } from '../api/doctores-service.js';
import { modal } from '../utils/modal.js';
import { notify } from '../utils/notifications.js';

document.addEventListener('DOMContentLoaded', async () => {
    // Exclusivo de Admin
    if (!checkRole(['admin'])) return;

    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    // Cargar Catálogos
    await cargarEspecialidadesSelects();
    await cargarDoctores();

    // Filtros
    document.getElementById('sel_filtro_especialidad').addEventListener('change', (e) => {
        cargarDoctores(e.target.value);
    });

    let timer;
    document.getElementById('inp_buscar_doctor').addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const query = document.getElementById('inp_buscar_doctor').value.toLowerCase().trim();
            filtrarCardsLocal(query);
        }, 300);
    });

    // Modal Crear
    document.getElementById('btn_abrir_modal_doctor').addEventListener('click', () => {
        document.getElementById('form_doctor').reset();
        document.getElementById('txt_doctor_id').value = '';
        document.getElementById('modal_doctor_title').textContent = 'Registrar Nuevo Doctor';
        document.getElementById('btn_guardar_doctor').textContent = 'Registrar Doctor';
        modal.open('modal_doctor');
    });

    // Guardar (Crear / Editar)
    document.getElementById('form_doctor').addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.getElementById('txt_doctor_id').value;
        const nombre = document.getElementById('txt_nombre_doc').value.trim();
        const especialidad_id = document.getElementById('sel_especialidad_doc').value;
        const cedula_profesional = document.getElementById('txt_cedula').value.trim();
        const telefono = document.getElementById('txt_telefono_doc').value.trim();
        const email = document.getElementById('txt_email_doc').value.trim();

        const datos = { nombre, especialidad_id, cedula_profesional, telefono, email };
        const btnSave = document.getElementById('btn_guardar_doctor');
        btnSave.disabled = true;

        try {
            let res;
            if (id) {
                res = await doctoresService.actualizarDoctor(id, datos);
            } else {
                res = await doctoresService.registrarDoctor(datos);
            }

            if (res.ok) {
                notify.success(id ? 'Doctor actualizado correctamente.' : 'Doctor registrado con éxito.');
                modal.close('modal_doctor');
                await cargarDoctores();
            } else {
                notify.error(res.data?.mensaje || res.data?.msj || 'Error al guardar los datos del médico.');
            }
        } catch (err) {
            notify.error('Error al procesar la solicitud.');
        } finally {
            btnSave.disabled = false;
        }
    });
});

let listaDoctoresMemoria = [];

async function cargarEspecialidadesSelects() {
    try {
        const res = await doctoresService.obtenerEspecialidades();
        if (res.ok && Array.isArray(res.data.data)) {
            const options = res.data.data.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
            document.getElementById('sel_filtro_especialidad').innerHTML = '<option value="">Todas las Especialidades</option>' + options;
            document.getElementById('sel_especialidad_doc').innerHTML = '<option value="">Seleccione...</option>' + options;
        }
    } catch (e) {
        console.error('Error cargando especialidades:', e);
    }
}

async function cargarDoctores(especialidadId = null) {
    const grid = document.getElementById('grid_doctores');
    try {
        const res = await doctoresService.obtenerDoctores(especialidadId);
        if (res.ok && Array.isArray(res.data.data)) {
            listaDoctoresMemoria = res.data.data;
            renderGridDoctores(listaDoctoresMemoria);
        }
    } catch (e) {
        grid.innerHTML = '<p style="color: var(--color-danger);">Error al cargar la lista de doctores.</p>';
    }
}

function renderGridDoctores(doctores) {
    const grid = document.getElementById('grid_doctores');
    if (doctores.length === 0) {
        grid.innerHTML = '<p style="color: var(--color-text-muted); grid-column: 1/-1;">No se encontraron doctores registrados.</p>';
        return;
    }

    grid.innerHTML = doctores.map(doc => {
        const nombre = doc.nombre || doc.usuario?.nombre || 'Dr. Médico';
        const iniciales = nombre.replace('Dr. ', '').substring(0, 2).toUpperCase();
        const espNombre = doc.especialidad?.nombre || 'Medicina General';
        const cedula = doc.cedula_profesional || 'Céd. N/A';
        const isInactive = doc.estado === 'Inactivo';

        return `
            <div class="doctor-card ${isInactive ? 'doctor-card--inactive' : ''}">
                <div class="doctor-card__avatar">${iniciales}</div>
                <h3 class="doctor-card__name">${nombre}</h3>
                <span class="badge badge--success" style="margin-bottom: 8px;">${espNombre}</span>
                <span class="doctor-card__cedula">Céd. Prof. ${cedula}</span>

                <div class="doctor-card__contact">
                    <span><i data-lucide="phone" style="font-size: 14px;"></i> ${doc.telefono || 'N/A'}</span>
                    <span><i data-lucide="mail" style="font-size: 14px;"></i> ${doc.email || doc.usuario?.email || 'N/A'}</span>
                </div>

                <div class="doctor-card__actions">
                    <a href="horarios.html?doctor=${doc.id}" class="btn btn-outline-primary" style="padding: 6px 12px; font-size: 12px;">
                        <i data-lucide="calendar"></i> Horarios
                    </a>
                    <button onclick="prepararEdicionDoctor(${doc.id})" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; color: var(--color-accent);">
                        <i data-lucide="pencil"></i> Editar
                    </button>
                </div>
            </div>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

function filtrarCardsLocal(query) {
    if (!query) {
        renderGridDoctores(listaDoctoresMemoria);
        return;
    }
    const filtrados = listaDoctoresMemoria.filter(d =>
        (d.nombre || '').toLowerCase().includes(query) ||
        (d.especialidad?.nombre || '').toLowerCase().includes(query)
    );
    renderGridDoctores(filtrados);
}

window.prepararEdicionDoctor = async (id) => {
    try {
        const res = await doctoresService.obtenerDoctor(id);
        if (res.ok && res.data) {
            const d = res.data.data || res.data;
            document.getElementById('txt_doctor_id').value = d.id;
            document.getElementById('txt_nombre_doc').value = d.nombre || d.usuario?.nombre || '';
            document.getElementById('sel_especialidad_doc').value = d.especialidad_id || '';
            document.getElementById('txt_cedula').value = d.cedula_profesional || '';
            document.getElementById('txt_telefono_doc').value = d.telefono || '';
            document.getElementById('txt_email_doc').value = d.email || d.usuario?.email || '';

            document.getElementById('modal_doctor_title').textContent = 'Editar Doctor';
            document.getElementById('btn_guardar_doctor').textContent = 'Guardar Cambios';
            modal.open('modal_doctor');
        }
    } catch (e) {
        notify.error('Error al obtener datos del médico.');
    }
};
