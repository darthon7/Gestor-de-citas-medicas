// js/api/notas-service.js - Servicio de Notas y Diagnósticos Médicos
import { apiFetch } from './config.js';

export const notasService = {
    // POST /api/registrarNota/{citaId}
    async registrarNota(citaId, datos) {
        return await apiFetch(`/registrarNota/${citaId}`, {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    },

    // GET /api/obtenerNotas/{citaId}
    async obtenerNotas(citaId) {
        return await apiFetch(`/obtenerNotas/${citaId}`, { method: 'GET' });
    },

    // PATCH /api/iniciarConsulta/{id}
    async iniciarConsulta(id) {
        return await apiFetch(`/iniciarConsulta/${id}`, { method: 'PATCH' });
    },

    // PATCH /api/completarCita/{id}
    async completarCita(id) {
        return await apiFetch(`/completarCita/${id}`, { method: 'PATCH' });
    }
};
