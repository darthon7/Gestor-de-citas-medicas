// js/ui/perfil-view.js - Lógica del Perfil de Usuario y Cambio de Contraseña
import { checkAuth, getCurrentUser } from '../utils/router.js';
import { authService } from '../api/auth-service.js';
import { notify } from '../utils/notifications.js';
import { validarTelefono } from '../utils/validators.js';

document.addEventListener('DOMContentLoaded', async () => {
    if (!checkAuth()) return;

    const user = getCurrentUser();
    document.getElementById('lbl_user_name').textContent = user.nombre;
    document.getElementById('lbl_user_role').textContent = user.rol;
    document.getElementById('lbl_user_avatar').textContent = user.nombre.charAt(0).toUpperCase();

    if (user.rol === 'doctor') {
        document.querySelectorAll('.nav-admin-only').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.nav-non-doctor').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.nav-doctor-only').forEach(el => el.classList.remove('hidden'));
    } else if (user.rol === 'recepcionista') {
        document.querySelectorAll('.nav-admin-only').forEach(el => el.classList.add('oculto'));
    }

    document.getElementById('btn_logout').addEventListener('click', () => authService.logout());

    await cargarPerfilDatos();

    // Actualizar Teléfono
    document.getElementById('form_perfil').addEventListener('submit', async (e) => {
        e.preventDefault();
        const telefono = document.getElementById('txt_perfil_telefono').value.trim();

        if (!validarTelefono(telefono)) {
            notify.error('El teléfono debe contener 10 dígitos.');
            return;
        }

        const btn = document.getElementById('btn_guardar_perfil');
        btn.disabled = true;

        try {
            const res = await authService.actualizarPerfil({ telefono });
            if (res.ok) {
                notify.success('Datos personales actualizados correctamente.');
            } else {
                notify.error(res.data?.mensaje || 'Error al actualizar perfil.');
            }
        } catch (e) {
            notify.error('No se pudo conectar con el servidor.');
        } finally {
            btn.disabled = false;
        }
    });

    // Indicador fortaleza de contraseña
    document.getElementById('txt_pass_nueva').addEventListener('input', (e) => {
        evaluarFortalezaPassword(e.target.value);
    });

    // Cambiar Contraseña
    document.getElementById('form_password').addEventListener('submit', async (e) => {
        e.preventDefault();

        const passActual = document.getElementById('txt_pass_actual').value.trim();
        const passNueva = document.getElementById('txt_pass_nueva').value.trim();
        const passConfirm = document.getElementById('txt_pass_confirm').value.trim();

        if (passNueva !== passConfirm) {
            notify.error('La nueva contraseña y su confirmación no coinciden.');
            return;
        }

        const btn = document.getElementById('btn_cambiar_pass');
        btn.disabled = true;

        try {
            const res = await authService.cambiarPassword(passActual, passNueva, passConfirm);
            if (res.ok) {
                notify.success('¡Contraseña actualizada con éxito!');
                document.getElementById('form_password').reset();
                evaluarFortalezaPassword('');
            } else {
                notify.error(res.data?.mensaje || res.data?.msj || 'No se pudo cambiar la contraseña.');
            }
        } catch (e) {
            notify.error('Error al cambiar la contraseña.');
        } finally {
            btn.disabled = false;
        }
    });
});

async function cargarPerfilDatos() {
    try {
        const res = await authService.obtenerPerfil();
        if (res.ok && res.data) {
            const u = res.data.data || res.data;
            const nombre = u.nombre || u.name || 'Usuario';

            document.getElementById('lbl_hero_nombre').textContent = nombre;
            document.getElementById('lbl_hero_rol').textContent = u.rol || 'Rol';
            document.getElementById('lbl_hero_avatar').textContent = nombre.charAt(0).toUpperCase();

            document.getElementById('txt_perfil_nombre').value = nombre;
            document.getElementById('txt_perfil_email').value = u.email || '';
            document.getElementById('txt_perfil_telefono').value = u.telefono || '';
        }
    } catch (e) {
        console.error(e);
    }
}

function evaluarFortalezaPassword(pass) {
    const bars = [
        document.getElementById('bar_strength_1'),
        document.getElementById('bar_strength_2'),
        document.getElementById('bar_strength_3'),
        document.getElementById('bar_strength_4')
    ];

    bars.forEach(b => b.style.backgroundColor = 'var(--color-border)');

    if (!pass) return;

    let score = 0;
    if (pass.length >= 8) score++;
    if (/[A-Z]/.test(pass)) score++;
    if (/[0-9]/.test(pass)) score++;
    if (/[^A-Za-z0-9]/.test(pass)) score++;

    const colors = ['var(--color-danger)', 'var(--color-accent)', 'var(--color-accent)', 'var(--color-secondary)'];

    for (let i = 0; i < score; i++) {
        if (bars[i]) bars[i].style.backgroundColor = colors[score - 1];
    }
}
