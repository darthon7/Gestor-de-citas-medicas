// js/ui/registro-view.js - Lógica del registro público de paciente
import { authService } from '../api/auth-service.js';
import { validarCURP, validarEmail, validarTelefono } from '../utils/validators.js';

document.addEventListener('DOMContentLoaded', () => {
    const formRegistro = document.getElementById('form_registro');
    const divAlertaError = document.getElementById('div_alerta_error');
    const btnRegistrar = document.getElementById('btn_registrar');

    formRegistro.addEventListener('submit', async (e) => {
        e.preventDefault();
        divAlertaError.classList.add('oculto');

        const nombre = document.getElementById('txt_nombre').value.trim();
        const curp = document.getElementById('txt_curp').value.trim().toUpperCase();
        const email = document.getElementById('txt_email').value.trim();
        const telefono = document.getElementById('txt_telefono').value.trim();
        const fecha_nacimiento = document.getElementById('inp_fecha_nac').value;
        const sexo = document.getElementById('sel_sexo').value;
        const nss = document.getElementById('txt_nss').value.trim();
        const password = document.getElementById('txt_password').value.trim();
        const password_confirmation = document.getElementById('txt_password_confirm').value.trim();

        // Validaciones en cliente
        if (!validarCURP(curp)) {
            divAlertaError.textContent = 'La CURP no tiene un formato válido (18 caracteres).';
            divAlertaError.classList.remove('oculto');
            return;
        }

        if (!validarEmail(email)) {
            divAlertaError.textContent = 'Ingrese un correo electrónico válido.';
            divAlertaError.classList.remove('oculto');
            return;
        }

        if (!validarTelefono(telefono)) {
            divAlertaError.textContent = 'El teléfono debe contener 10 dígitos numéricos.';
            divAlertaError.classList.remove('oculto');
            return;
        }

        if (password !== password_confirmation) {
            divAlertaError.textContent = 'Las contraseñas ingresadas no coinciden.';
            divAlertaError.classList.remove('oculto');
            return;
        }

        const datos = {
            nombre,
            curp,
            email,
            telefono,
            fecha_nacimiento: fecha_nacimiento || null,
            sexo: sexo || null,
            nss: nss || null,
            password,
            password_confirmation
        };

        btnRegistrar.disabled = true;
        btnRegistrar.innerHTML = '<span class="spinner"></span> <span>Registrando...</span>';

        try {
            const res = await authService.registrarPaciente(datos);

            if (res.ok) {
                divAlertaError.style.backgroundColor = 'var(--color-secondary-light)';
                divAlertaError.style.color = '#1b685f';
                divAlertaError.textContent = '¡Cuenta registrada con éxito! Redirigiendo al inicio de sesión...';
                divAlertaError.classList.remove('oculto');

                setTimeout(() => {
                    window.location.href = '/login.html';
                }, 2000);
            } else {
                const msj = res.data?.msj || res.data?.mensaje || 'Error al procesar el registro.';
                divAlertaError.style.backgroundColor = 'var(--color-danger-light)';
                divAlertaError.style.color = 'var(--color-danger)';
                divAlertaError.textContent = msj;
                divAlertaError.classList.remove('oculto');
            }
        } catch (err) {
            divAlertaError.style.backgroundColor = 'var(--color-danger-light)';
            divAlertaError.style.color = 'var(--color-danger)';
            divAlertaError.textContent = 'Error al conectar con el servidor backend.';
            divAlertaError.classList.remove('oculto');
        } finally {
            btnRegistrar.disabled = false;
            btnRegistrar.innerHTML = '<span>Registrar Cuenta</span>';
        }
    });
});
