// js/api/citas-service.js - Servicio de Gestión de Citas Médicas
import { apiFetch } from './config.js';

export const citasService = {
    // GET /api/obtenerCitas?fecha_inicio=&fecha_fin=&doctor_id=&especialidad_id=
    async obtenerCitas(params = {}) {
        const query = new URLSearchParams(params).toString();
        const endpoint = query ? `/obtenerCitas?${query}` : '/obtenerCitas';
        return await apiFetch(endpoint, { method: 'GET' });
    },

    // POST /api/registrarCita
    async registrarCita(datos) {
        return await apiFetch('/registrarCita', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    },

    // GET /api/obtenerCita/{id}
    async obtenerCita(id) {
        return await apiFetch(`/obtenerCita/${id}`, { method: 'GET' });
    },

    // PUT /api/reprogramarCita/{id}
    async reprogramarCita(id, datos) {
        return await apiFetch(`/reprogramarCita/${id}`, {
            method: 'PUT',
            body: JSON.stringify(datos)
        });
    },

    // PATCH /api/cancelarCita/{id}
    async cancelarCita(id, motivo) {
        return await apiFetch(`/cancelarCita/${id}`, {
            method: 'PATCH',
            body: JSON.stringify({ motivo_cancelacion: motivo })
        });
    },

    // PATCH /api/checkInCita/{id}
    async checkInCita(id) {
        return await apiFetch(`/checkInCita/${id}`, { method: 'PATCH' });
    }
};
