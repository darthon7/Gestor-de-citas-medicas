// js/utils/validators.js - Validaciones de formulario (CURP, email, teléfono, contraseña)

export function validarCURP(curp) {
    const re = /^[A-Z]{1}[AEIOU]{1}[A-Z]{2}\d{6}[HM]{1}(AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}\d{1}$/i;
    return re.test(curp.trim());
}

export function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email.trim());
}

export function validarTelefono(tel) {
    const re = /^\d{10}$/;
    return re.test(tel.trim());
}

export function validarPassword(password) {
    return password && password.length >= 8;
}

export function validarFechaNoFutura(fechaStr) {
    if (!fechaStr) return false;
    const fecha = new Date(fechaStr);
    const hoy = new Date();
    return fecha <= hoy;
}
