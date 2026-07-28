// js/api/pacientes-service.js - Servicio de Pacientes
import { apiFetch } from './config.js';

export const pacientesService = {
    // GET /api/obtenerPacientes?q=
    async obtenerPacientes(busqueda = '', pagina = 1) {
        const query = busqueda ? `?q=${encodeURIComponent(busqueda)}&page=${pagina}` : `?page=${pagina}`;
        return await apiFetch(`/obtenerPacientes${query}`, { method: 'GET' });
    },

    // POST /api/registrarPaciente
    async registrarPaciente(datos) {
        return await apiFetch('/registrarPaciente', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    },

    // GET /api/obtenerPaciente/{id}
    async obtenerPaciente(id) {
        return await apiFetch(`/obtenerPaciente/${id}`, { method: 'GET' });
    },

    // PUT /api/actualizarPaciente/{id}
    async actualizarPaciente(id, datos) {
        return await apiFetch(`/actualizarPaciente/${id}`, {
            method: 'PUT',
            body: JSON.stringify(datos)
        });
    },

    // PATCH /api/desactivarPaciente/{id}
    async desactivarPaciente(id) {
        return await apiFetch(`/desactivarPaciente/${id}`, { method: 'PATCH' });
    }
};
