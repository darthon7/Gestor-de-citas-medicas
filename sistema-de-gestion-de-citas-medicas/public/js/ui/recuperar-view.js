// js/ui/recuperar-view.js - Lógica del flujo de recuperación de contraseña
import { authService } from '../api/auth-service.js';

document.addEventListener('DOMContentLoaded', () => {
    const divAlerta = document.getElementById('div_alerta');

    // Paso 1: Solicitar código
    const formSolicitar = document.getElementById('form_solicitar');
    if (formSolicitar) {
        formSolicitar.addEventListener('submit', async (e) => {
            e.preventDefault();
            divAlerta.classList.add('oculto');
            const email = document.getElementById('txt_email').value.trim();

            const btn = document.getElementById('btn_enviar');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> <span>Enviando...</span>';

            try {
                const res = await authService.solicitarRecuperacion(email);
                if (res.ok) {
                    sessionStorage.setItem('reset_email', email);
                    window.location.href = '/verificar-codigo.html';
                } else {
                    divAlerta.textContent = res.data?.mensaje || res.data?.msj || 'No se pudo enviar el correo de recuperación.';
                    divAlerta.classList.remove('oculto');
                }
            } catch (err) {
                divAlerta.textContent = 'Error de conexión con el servidor.';
                divAlerta.classList.remove('oculto');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>Enviar Código de Recuperación</span>';
            }
        });
    }

    // Paso 2: Verificar código
    const formVerificar = document.getElementById('form_verificar');
    if (formVerificar) {
        const resetEmail = sessionStorage.getItem('reset_email');
        if (!resetEmail) {
            window.location.href = '/recuperar-password.html';
            return;
        }

        formVerificar.addEventListener('submit', async (e) => {
            e.preventDefault();
            divAlerta.classList.add('oculto');
            const codigo = document.getElementById('txt_codigo').value.trim();

            const btn = document.getElementById('btn_verificar');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> <span>Verificando...</span>';

            try {
                const res = await authService.verificarCodigo(resetEmail, codigo);
                if (res.ok) {
                    sessionStorage.setItem('reset_codigo', codigo);
                    window.location.href = '/restablecer-password.html';
                } else {
                    divAlerta.textContent = res.data?.mensaje || res.data?.msj || 'Código incorrecto o expirado.';
                    divAlerta.classList.remove('oculto');
                }
            } catch (err) {
                divAlerta.textContent = 'Error de conexión con el servidor.';
                divAlerta.classList.remove('oculto');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>Verificar Código</span>';
            }
        });
    }

    // Paso 3: Restablecer contraseña
    const formRestablecer = document.getElementById('form_restablecer');
    if (formRestablecer) {
        const resetEmail = sessionStorage.getItem('reset_email');
        const resetCodigo = sessionStorage.getItem('reset_codigo');

        if (!resetEmail || !resetCodigo) {
            window.location.href = '/recuperar-password.html';
            return;
        }

        formRestablecer.addEventListener('submit', async (e) => {
            e.preventDefault();
            divAlerta.classList.add('oculto');

            const pass1 = document.getElementById('txt_new_pass').value.trim();
            const pass2 = document.getElementById('txt_confirm_pass').value.trim();

            if (pass1 !== pass2) {
                divAlerta.textContent = 'Las contraseñas no coinciden.';
                divAlerta.classList.remove('oculto');
                return;
            }

            const btn = document.getElementById('btn_restablecer');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> <span>Guardando...</span>';

            try {
                const res = await authService.restablecerPassword(resetEmail, resetCodigo, pass1, pass2);
                if (res.ok) {
                    sessionStorage.removeItem('reset_email');
                    sessionStorage.removeItem('reset_codigo');
                    divAlerta.style.backgroundColor = 'var(--color-secondary-light)';
                    divAlerta.style.color = '#1b685f';
                    divAlerta.textContent = '¡Contraseña actualizada correctamente! Redirigiendo al login...';
                    divAlerta.classList.remove('oculto');

                    setTimeout(() => {
                        window.location.href = '/login.html';
                    }, 2500);
                } else {
                    divAlerta.textContent = res.data?.mensaje || res.data?.msj || 'No se pudo restablecer la contraseña.';
                    divAlerta.classList.remove('oculto');
                }
            } catch (err) {
                divAlerta.textContent = 'Error de conexión con el servidor.';
                divAlerta.classList.remove('oculto');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>Guardar Nueva Contraseña</span>';
            }
        });
    }
});
