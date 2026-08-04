// js/api/doctores-service.js - Servicio de Doctores y Disponibilidad
import { apiFetch } from './config.js';

export const doctoresService = {
    // GET /api/obtenerEspecialidades
    async obtenerEspecialidades() {
        return await apiFetch('/obtenerEspecialidades', { method: 'GET' });
    },

    // GET /api/obtenerDoctores?especialidad_id=
    async obtenerDoctores(especialidadId = null) {
        const query = especialidadId ? `?especialidad_id=${especialidadId}` : '';
        return await apiFetch(`/obtenerDoctores${query}`, { method: 'GET' });
    },

    // GET /api/obtenerDoctor/{id}
    async obtenerDoctor(id) {
        return await apiFetch(`/obtenerDoctor/${id}`, { method: 'GET' });
    },

    // GET /api/obtenerDisponibilidad/{doctorId}?fecha=YYYY-MM-DD
    async obtenerDisponibilidad(doctorId, fecha) {
        return await apiFetch(`/obtenerDisponibilidad/${doctorId}?fecha=${fecha}`, { method: 'GET' });
    },

    // POST /api/registrarDoctor
    async registrarDoctor(datos) {
        return await apiFetch('/registrarDoctor', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    },

    // PUT /api/actualizarDoctor/{id}
    async actualizarDoctor(id, datos) {
        return await apiFetch(`/actualizarDoctor/${id}`, {
            method: 'PUT',
            body: JSON.stringify(datos)
        });
    },

    // PATCH /api/validarDoctor/{id}
    async validarDoctor(id) {
        return await apiFetch(`/validarDoctor/${id}`, { method: 'PATCH' });
    }
};
