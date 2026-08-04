// js/api/config.js - Helper HTTP Centralizado
export const API_BASE_URL = 'http://localhost:8000/api';

/**
 * Realiza peticiones HTTP centralizadas a la API REST en Laravel 11
 * @param {string} endpoint - Ruta relativa (ej: '/auth/login')
 * @param {object} options - Opciones de fetch (method, body, headers)
 */
export async function apiFetch(endpoint, options = {}) {
    const token = localStorage.getItem('token_sanctum');

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const config = {
        ...options,
        headers
    };

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
        const data = await response.json().catch(() => ({}));

        if (response.status === 401) {
            localStorage.clear();
            if (!window.location.pathname.endsWith('login.html')) {
                window.location.href = '/login.html';
            }
            throw new Error('Sesión expirada. Inicie sesión nuevamente.');
        }

        return {
            ok: response.ok,
            status: response.status,
            data
        };
    } catch (error) {
        console.error(`[API Error] ${endpoint}:`, error.message);
        throw error;
    }
}
