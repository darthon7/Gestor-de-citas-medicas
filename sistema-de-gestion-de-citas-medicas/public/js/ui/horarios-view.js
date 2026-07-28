// js/ui/horarios-view.js - Lógica de Configuración de Horarios de Doctor
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { doctoresService } from '../api/doctores-service.js';
import { horariosService } from '../api/horarios-service.js';
import { modal } from '../utils/modal.js';
import { notify } from '../utils/notifications.js';

let currentDoctorId = null;

document.addEventListener('DOMContentLoaded', async () => {
    if (!checkRole(['admin'])) return;

    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    const params = new URLSearchParams(window.location.search);
    currentDoctorId = params.get('doctor');

    if (!currentDoctorId) {
        window.location.href = '/doctores.html';
        return;
    }

    await cargarDoctorInfo();
    await cargarHorariosGrid();

    // Modal Abrir
    document.getElementById('btn_abrir_modal_horario').addEventListener('click', () => {
        document.getElementById('form_horario').reset();
        modal.open('modal_horario');
    });

    // Guardar Horario
    document.getElementById('form_horario').addEventListener('submit', async (e) => {
        e.preventDefault();

        const dia_semana = document.getElementById('sel_dia_semana').value;
        const hora_inicio = document.getElementById('inp_hora_inicio').value;
        const hora_fin = document.getElementById('inp_hora_fin').value;
        const duracion_minutos = document.getElementById('sel_duracion_consulta').value;

        if (hora_inicio >= hora_fin) {
            notify.error('La hora de inicio debe ser menor a la hora de fin.');
            return;
        }

        const datos = { dia_semana, hora_inicio, hora_fin, duracion_minutos };
        const btn = document.getElementById('btn_guardar_horario');
        btn.disabled = true;

        try {
            const res = await horariosService.registrarHorario(currentDoctorId, datos);
            if (res.ok) {
                notify.success('Horario agregado exitosamente.');
                modal.close('modal_horario');
                await cargarHorariosGrid();
            } else {
                notify.error(res.data?.mensaje || res.data?.msj || 'No se pudo agregar el horario.');
            }
        } catch (err) {
            notify.error('Error al guardar el horario.');
        } finally {
            btn.disabled = false;
        }
    });

    // Guardar Bloqueo
    document.getElementById('form_bloqueo').addEventListener('submit', async (e) => {
        e.preventDefault();

        const fecha_inicio = document.getElementById('inp_bloqueo_desde').value;
        const fecha_fin = document.getElementById('inp_bloqueo_hasta').value;
        const motivo = document.getElementById('sel_bloqueo_motivo').value;

        if (fecha_inicio > fecha_fin) {
            notify.error('La fecha inicial debe ser menor o igual a la final.');
            return;
        }

        const datos = { fecha_inicio, fecha_fin, motivo };
        const btn = document.getElementById('btn_guardar_bloqueo');
        btn.disabled = true;

        try {
            const res = await horariosService.registrarBloqueo(currentDoctorId, datos);
            if (res.ok) {
                notify.success('Bloqueo registrado correctamente.');
                document.getElementById('form_bloqueo').reset();
                await cargarHorariosGrid();
            } else {
                notify.error(res.data?.mensaje || 'Error al registrar el bloqueo.');
            }
        } catch (err) {
            notify.error('Ocurrió un error al procesar el bloqueo.');
        } finally {
            btn.disabled = false;
        }
    });
});

async function cargarDoctorInfo() {
    try {
        const res = await doctoresService.obtenerDoctor(currentDoctorId);
        if (res.ok && res.data) {
            const d = res.data.data || res.data;
            const nombre = d.nombre || d.usuario?.nombre || 'Dr. Médico';
            document.getElementById('lbl_doc_nombre').textContent = nombre;
            document.getElementById('lbl_doc_avatar').textContent = nombre.replace('Dr. ', '').substring(0, 2).toUpperCase();
            document.getElementById('lbl_doc_especialidad').textContent = d.especialidad?.nombre || 'General';
        }
    } catch (e) {
        notify.error('Error al cargar la información del médico.');
    }
}

async function cargarHorariosGrid() {
    const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    dias.forEach(d => {
        const col = document.querySelector(`#col_${d} .day-content`);
        if (col) col.innerHTML = '<span style="font-size:11px; color:var(--color-text-muted);">Sin horarios</span>';
    });

    try {
        const res = await horariosService.obtenerHorarios(currentDoctorId);
        if (res.ok && Array.isArray(res.data.data)) {
            const horarios = res.data.data;

            dias.forEach(dia => {
                const bloques = horarios.filter(h => h.dia_semana.toLowerCase() === dia);
                const col = document.querySelector(`#col_${dia} .day-content`);

                if (col && bloques.length > 0) {
                    col.innerHTML = bloques.map(h => `
                        <div class="time-block">
                            <span onclick="eliminarHorario(${h.id})" class="time-block__delete" title="Eliminar">&times;</span>
                            <strong style="font-size:11px;">${h.hora_inicio.substring(0,5)} - ${h.hora_fin.substring(0,5)}</strong>
                            <span>${h.duracion_minutos || 30} min/consulta</span>
                        </div>
                    `).join('');
                }
            });
        }
    } catch (e) {
        console.error(e);
    }
}

window.eliminarHorario = async (id) => {
    if (confirm('¿Desea eliminar este bloque de horario?')) {
        try {
            const res = await horariosService.eliminarHorario(id);
            if (res.ok) {
                notify.success('Horario eliminado.');
                cargarHorariosGrid();
            }
        } catch (e) {
            notify.error('Error al eliminar el horario.');
        }
    }
};
