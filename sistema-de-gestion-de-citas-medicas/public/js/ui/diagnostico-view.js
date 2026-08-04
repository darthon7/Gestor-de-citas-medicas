// js/ui/diagnostico-view.js - Lógica del Formulario de Diagnóstico Médico
import { checkRole, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { citasService } from '../api/citas-service.js';
import { notasService } from '../api/notas-service.js';
import { formatTime } from '../utils/formatters.js';
import { notify } from '../utils/notifications.js';

let currentCitaId = null;

document.addEventListener('DOMContentLoaded', async () => {
    if (!checkRole(['doctor'])) return;

    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    const params = new URLSearchParams(window.location.search);
    currentCitaId = params.get('citaId');

    if (!currentCitaId) {
        window.location.href = '/mi-agenda.html';
        return;
    }

    await cargarInfoCita();

    // Guardar Borrador
    document.getElementById('btn_guardar_borrador').addEventListener('click', async () => {
        await enviarNota(false);
    });

    // Completar Consulta
    document.getElementById('form_diagnostico').addEventListener('submit', async (e) => {
        e.preventDefault();
        await enviarNota(true);
    });
});

async function cargarInfoCita() {
    try {
        const res = await citasService.obtenerCita(currentCitaId);
        if (res.ok && res.data) {
            const cita = res.data.data || res.data;
            const pNombre = cita.paciente?.nombre || 'Paciente';

            document.getElementById('lbl_pac_nombre').textContent = pNombre;
            document.getElementById('lbl_pac_avatar').textContent = pNombre.charAt(0).toUpperCase();
            document.getElementById('lbl_pac_meta').textContent =
                `Exp: ${cita.paciente?.numero_expediente || 'EXP-' + String(cita.paciente_id).padStart(4, '0')} | Cita: ${formatTime(cita.fecha_hora)}`;

            // Si existen notas previas
            if (cita.nota_consulta) {
                document.getElementById('txt_diagnostico').value = cita.nota_consulta.diagnostico || '';
                document.getElementById('txt_tratamiento').value = cita.nota_consulta.tratamiento || '';
                document.getElementById('txt_notas_adicionales').value = cita.nota_consulta.notas_adicionales || '';
                if (cita.nota_consulta.signos_vitales) {
                    document.getElementById('inp_presion').value = cita.nota_consulta.signos_vitales.presion_arterial || '';
                    document.getElementById('inp_frecuencia').value = cita.nota_consulta.signos_vitales.frecuencia_cardiaca || '';
                    document.getElementById('inp_temperatura').value = cita.nota_consulta.signos_vitales.temperatura || '';
                    document.getElementById('inp_peso').value = cita.nota_consulta.signos_vitales.peso || '';
                }
            }
        }
    } catch (e) {
        notify.error('Error al cargar los datos de la cita.');
    }
}

async function enviarNota(completar = false) {
    const diagnostico = document.getElementById('txt_diagnostico').value.trim();
    const tratamiento = document.getElementById('txt_tratamiento').value.trim();
    const notas_adicionales = document.getElementById('txt_notas_adicionales').value.trim();

    const presion = document.getElementById('inp_presion').value.trim();
    const frecuencia = document.getElementById('inp_frecuencia').value.trim();
    const temperatura = document.getElementById('inp_temperatura').value.trim();
    const peso = document.getElementById('inp_peso').value.trim();

    const payload = {
        diagnostico,
        tratamiento,
        notas_adicionales,
        signos_vitales: {
            presion_arterial: presion,
            frecuencia_cardiaca: frecuencia,
            temperatura,
            peso
        }
    };

    try {
        const resNota = await notasService.registrarNota(currentCitaId, payload);
        if (resNota.ok) {
            if (completar) {
                const resComp = await notasService.completarCita(currentCitaId);
                if (resComp.ok) {
                    notify.success('¡Consulta completada con éxito!');
                    setTimeout(() => {
                        window.location.href = '/mi-agenda.html';
                    }, 1500);
                } else {
                    notify.error('Nota guardada pero no se pudo finalizar la cita.');
                }
            } else {
                notify.success('Borrador de nota médica guardado.');
            }
        } else {
            notify.error(resNota.data?.mensaje || 'Error al guardar la nota médica.');
        }
    } catch (e) {
        notify.error('Ocurrió un error al procesar el envío.');
    }
}
