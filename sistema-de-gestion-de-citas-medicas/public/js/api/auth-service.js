// js/api/auth-service.js - Servicio de Autenticación contra Laravel REST API
import { apiFetch } from './config.js';

export const authService = {
    // POST /api/auth/login
    async login(email, password) {
        const respuesta = await apiFetch('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });

        if (respuesta.ok && respuesta.data.token) {
            localStorage.setItem('token_sanctum', respuesta.data.token);
            localStorage.setItem('usuario_nombre', respuesta.data.usuario.nombre);
            localStorage.setItem('usuario_rol', respuesta.data.usuario.rol);
            localStorage.setItem('usuario_id', respuesta.data.usuario.id);
        }

        return respuesta;
    },

    // POST /api/auth/registrarPaciente
    async registrarPaciente(datos) {
        return await apiFetch('/auth/registrarPaciente', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    },

    // POST /api/auth/registrarMedico
    async registrarMedico(datos) {
        return await apiFetch('/auth/registrarMedico', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    },

    // POST /api/auth/solicitarRecuperacion
    async solicitarRecuperacion(email) {
        return await apiFetch('/auth/solicitarRecuperacion', {
            method: 'POST',
            body: JSON.stringify({ email })
        });
    },

    // POST /api/auth/verificarCodigo
    async verificarCodigo(email, codigo) {
        return await apiFetch('/auth/verificarCodigo', {
            method: 'POST',
            body: JSON.stringify({ email, codigo })
        });
    },

    // POST /api/auth/restablecerPassword
    async restablecerPassword(email, codigo, password, password_confirmation) {
        return await apiFetch('/auth/restablecerPassword', {
            method: 'POST',
            body: JSON.stringify({ email, codigo, password, password_confirmation })
        });
    },

    // GET /api/miPerfil
    async obtenerPerfil() {
        return await apiFetch('/miPerfil', { method: 'GET' });
    },

    // PUT /api/actualizarMiPerfil
    async actualizarPerfil(datos) {
        return await apiFetch('/actualizarMiPerfil', {
            method: 'PUT',
            body: JSON.stringify(datos)
        });
    },

    // POST /api/cambiarPassword
    async cambiarPassword(password_actual, nueva_password, nueva_password_confirmation) {
        return await apiFetch('/cambiarPassword', {
            method: 'POST',
            body: JSON.stringify({
                password_actual,
                password: nueva_password,
                password_confirmation: nueva_password_confirmation
            })
        });
    },

    // POST /api/auth/cerrarSesion
    async logout() {
        try {
            await apiFetch('/auth/cerrarSesion', { method: 'POST' });
        } finally {
            localStorage.clear();
            window.location.href = '/login.html';
        }
    }
};
