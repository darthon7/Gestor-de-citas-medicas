// js/api/horarios-service.js - Servicio de Horarios y Bloqueos de Doctor
import { apiFetch } from './config.js';

export const horariosService = {
    // GET /api/obtenerHorarios/{doctorId}
    async obtenerHorarios(doctorId) {
        return await apiFetch(`/obtenerHorarios/${doctorId}`, { method: 'GET' });
    },

    // POST /api/registrarHorario/{doctorId}
    async registrarHorario(doctorId, datos) {
        return await apiFetch(`/registrarHorario/${doctorId}`, {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    },

    // PUT /api/actualizarHorario/{id}
    async actualizarHorario(id, datos) {
        return await apiFetch(`/actualizarHorario/${id}`, {
            method: 'PUT',
            body: JSON.stringify(datos)
        });
    },

    // DELETE /api/eliminarHorario/{id}
    async eliminarHorario(id) {
        return await apiFetch(`/eliminarHorario/${id}`, { method: 'DELETE' });
    },

    // GET /api/obtenerBloqueos/{doctorId}
    async obtenerBloqueos(doctorId) {
        return await apiFetch(`/obtenerBloqueos/${doctorId}`, { method: 'GET' });
    },

    // POST /api/registrarBloqueo/{doctorId}
    async registrarBloqueo(doctorId, datos) {
        return await apiFetch(`/registrarBloqueo/${doctorId}`, {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    },

    // DELETE /api/eliminarBloqueo/{id}
    async eliminarBloqueo(id) {
        return await apiFetch(`/eliminarBloqueo/${id}`, { method: 'DELETE' });
    }
};
