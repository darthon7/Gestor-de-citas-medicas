// js/api/reportes-service.js - Servicio de Reportes y Estadísticas
import { apiFetch, API_BASE_URL } from './config.js';

export const reportesService = {
    // GET /api/reporteCitas?fecha_inicio=&fecha_fin=&doctor_id=&especialidad_id=
    async reporteCitas(params = {}) {
        const query = new URLSearchParams(params).toString();
        return await apiFetch(`/reporteCitas?${query}`, { method: 'GET' });
    },

    // GET /api/reporteDoctores
    async reporteDoctores(params = {}) {
        const query = new URLSearchParams(params).toString();
        return await apiFetch(`/reporteDoctores?${query}`, { method: 'GET' });
    },

    // GET /api/reporteEspecialidades
    async reporteEspecialidades() {
        return await apiFetch('/reporteEspecialidades', { method: 'GET' });
    },

    // GET /api/reportePacientes
    async reportePacientes() {
        return await apiFetch('/reportePacientes', { method: 'GET' });
    },

    // GET /api/exportarReporte/{tipo} (pdf | excel)
    exportarUrl(tipo) {
        const token = localStorage.getItem('token_sanctum');
        return `${API_BASE_URL}/exportarReporte/${tipo}?token=${token}`;
    }
};
