// js/utils/router.js - Auth guard y control de rutas por rol

/**
 * Verifica si existe un token de sesión activo. Si no existe, redirige al login.
 */
export function checkAuth() {
    const token = localStorage.getItem('token_sanctum');
    if (!token) {
        window.location.href = '/login.html';
        return false;
    }
    return true;
}

/**
 * Verifica si el usuario actual posee uno de los roles permitidos.
 * @param {Array<string>} allowedRoles - Arreglo de roles (ej: ['admin', 'recepcionista'])
 */
export function checkRole(allowedRoles = []) {
    if (!checkAuth()) return false;

    const userRole = localStorage.getItem('usuario_rol');
    if (!allowedRoles.includes(userRole)) {
        console.warn(`[Access Denied] Rol '${userRole}' no autorizado para esta vista.`);
        if (userRole === 'doctor') {
            window.location.href = '/mi-agenda.html';
        } else {
            window.location.href = '/dashboard.html';
        }
        return false;
    }
    return true;
}

/**
 * Obtiene los datos del usuario guardados en localStorage
 */
export function getCurrentUser() {
    return {
        id: localStorage.getItem('usuario_id'),
        nombre: localStorage.getItem('usuario_nombre') || 'Usuario',
        rol: localStorage.getItem('usuario_rol') || 'paciente',
        token: localStorage.getItem('token_sanctum')
    };
}
