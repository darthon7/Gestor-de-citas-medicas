// js/ui/login-view.js - Lógica de la pantalla de login
import { authService } from '../api/auth-service.js';

document.addEventListener('DOMContentLoaded', () => {
    // Si ya existe sesión activa, redirigir al panel
    const token = localStorage.getItem('token_sanctum');
    const rol = localStorage.getItem('usuario_rol');
    if (token && rol) {
        redirigirPorRol(rol);
        return;
    }

    const formLogin = document.getElementById('form_login');
    const txtEmail = document.getElementById('txt_email');
    const txtPassword = document.getElementById('txt_password');
    const btnIngresar = document.getElementById('btn_ingresar');
    const btnTogglePass = document.getElementById('btn_toggle_pass');
    const divAlertaError = document.getElementById('div_alerta_error');

    // Toggle ocultar/mostrar contraseña
    btnTogglePass.addEventListener('click', () => {
        const isPassword = txtPassword.getAttribute('type') === 'password';
        txtPassword.setAttribute('type', isPassword ? 'text' : 'password');
        const eyeIcon = btnTogglePass.querySelector('i');
        if (eyeIcon) {
            eyeIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
            if (window.lucide) lucide.createIcons();
        }
    });

    // Envío del formulario
    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();
        divAlertaError.classList.add('oculto');

        const email = txtEmail.value.trim();
        const password = txtPassword.value.trim();

        if (!email || !password) {
            divAlertaError.textContent = 'Por favor complete todos los campos.';
            divAlertaError.classList.remove('oculto');
            return;
        }

        // Estado cargando
        btnIngresar.disabled = true;
        btnIngresar.innerHTML = '<span class="spinner"></span> <span>Verificando...</span>';

        try {
            const res = await authService.login(email, password);

            if (res.ok) {
                const userRol = localStorage.getItem('usuario_rol');
                redirigirPorRol(userRol);
            } else {
                const mensaje = res.data?.mensaje || res.data?.msj || 'Credenciales de acceso incorrectas.';
                divAlertaError.textContent = mensaje;
                divAlertaError.classList.remove('oculto');
            }
        } catch (error) {
            divAlertaError.textContent = 'No se pudo establecer conexión con el servidor backend.';
            divAlertaError.classList.remove('oculto');
        } finally {
            btnIngresar.disabled = false;
            btnIngresar.innerHTML = '<span>Ingresar</span>';
        }
    });
});

function redirigirPorRol(rol) {
    if (rol === 'doctor') {
        window.location.href = '/mi-agenda.html';
    } else {
        window.location.href = '/dashboard.html';
    }
}
