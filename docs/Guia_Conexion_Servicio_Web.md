# 🌐 Conexión del Frontend Web a la REST API Backend (Sistema de Gestión de Citas Médicas)

## 🎯 Objetivo
Guiaremos paso a paso cómo conectar una interfaz web (Frontend HTML/JS) al **Backend REST API en Laravel 11 ya terminado**. Aprenderás a estructurar un cliente HTTP, manejar autenticación por Tokens Bearer con **Laravel Sanctum**, consumir endpoints de especialidades, doctores y disponibilidad, y renderizar datos en vivo en el DOM sin recargar la página.

---

## 🧠 Conceptos clave

- **Cliente HTTP Centralizado (Fetch API Helper)** — *Como el embajador diplomático de la aplicación:* Un módulo central en JavaScript que se encarga de enviar todas las peticiones a la API, adjuntando automáticamente las cabeceras de autorización y el formato JSON en cada mensaje.
- **Token Bearer & localStorage** — *Como la credencial VIP del club:* Cuando el backend responde al login con un token Sanctum, el navegador lo almacena en `localStorage` para presentarlo en cada petición posterior sin pedir la contraseña de nuevo.
- **Flujo de Peticiones Asíncronas (Async / Await)** — *Como pedir turno en la recepción y esperar cómodamente:* Te permite solicitar información al servidor y continuar respondiendo a eventos de la interfaz sin congelar o congelar la pantalla mientras llega la respuesta.
- **Manejo de Respuestas HTTP (200, 401, 403, 422, 500)** — *Como los semáforos en la vía pública:* Indicadores que informan al frontend si la petición fue exitosa (`200`), si la sesión expiró (`401`), si no tiene permisos de rol (`403`), si fallaron las validaciones de entrada (`422`) o si ocurrió un error en el servidor (`500`).
- **Renderizado Dinámico del DOM** — *Como la pantalla interactiva de llegadas en un aeropuerto:* Toma la respuesta en JSON del servidor y actualiza únicamente la tabla o tarjeta de citas en la pantalla sin necesidad de refrescar toda la página web.

---

## 🗺️ Mapa del proyecto

Estructura del cliente Web Frontend y su conexión con la API REST existente en Laravel 11:

```
sistema-de-gestion-de-citas-medicas/
├── public/                               <-- Raíz del servidor web / cliente frontend
│   ├── css/
│   │   └── dashboard.css                <-- Estilos visuales del cliente web
│   ├── js/
│   │   ├── api/
│   │   │   ├── config.js                <-- Configuración global y Helper Fetch API
│   │   │   ├── auth-service.js          <-- Conexión a /api/auth/* (Login, Perfil, Logout)
│   │   │   ├── doctores-service.js      <-- Conexión a /api/obtenerEspecialidades y Doctores
│   │   │   └── citas-service.js         <-- Conexión a /api/misCitas, agendar y cancelar
│   │   └── ui/
│   │       ├── login-view.js            <-- Manejo del formulario de Login en pantalla
│   │       └── dashboard-view.js        <-- Manejo de catálogo de citas y eventos en DOM
│   ├── login.html                       <-- Pantalla de inicio de sesión
│   └── dashboard.html                   <-- Panel principal de citas médicas
└── routes/
    └── api.php                           <-- Backend Laravel 11 (Endpoints ya existentes)
```

---

## 🔨 Paso a paso

---

### Paso 1: Crear el Helper de Peticiones HTTP (`js/api/config.js`)

**🤔 ¿Por qué este paso?**
El backend en Laravel exige que todas las peticiones lleven las cabeceras `'Content-Type': 'application/json'` y `'Accept': 'application/json'`. Además, los endpoints protegidos requieren enviar el Token Bearer almacenado. Crear un helper centralizado evita duplicar este código en cada llamada y gestiona automáticamente la expiración de sesión (HTTP 401).

**🛠️ ¿Cómo?**
Crea el archivo `public/js/api/config.js` definiendo la constante `API_BASE_URL` y la función asíncrona `apiFetch(endpoint, options)`.

**Código de referencia:**

```javascript
// public/js/api/config.js

// URL Base donde se encuentra ejecutándose la API REST en Laravel 11
export const API_BASE_URL = 'http://localhost:8000/api';

/**
 * Realiza peticiones HTTP centralizadas a la API REST de Laravel
 * @param {string} endpoint - Ruta relativa del recurso (ej: '/auth/login')
 * @param {object} options - Opciones de la petición fetch (method, body, headers)
 */
export async function apiFetch(endpoint, options = {}) {
    const token = localStorage.getItem('token_sanctum');

    // Inyección estandarizada de cabeceras requeridas por el Backend
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
    };

    // Si existe un token de sesión en localStorage, se adjunta como Bearer Token
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

        // Si el token expiró o es inválido, redirigir al login
        if (response.status === 401) {
            localStorage.clear();
            window.location.href = '/login.html';
            throw new Error('Sesión expirada. Por favor inicie sesión de nuevo.');
        }

        return { ok: response.ok, status: response.status, data };
    } catch (error) {
        console.error(`[API Error] ${endpoint}:`, error.message);
        throw error;
    }
}
```

> 💡 **Qué hace este fragmento:** Centraliza la URL base de la API, inyecta la cabecera `Authorization: Bearer` recuperada de `localStorage` y redirige automáticamente al login ante errores HTTP 401.

> ⚠️ **Error común:** Escribir `http://localhost:8000` sin el sufijo `/api`. Las rutas del backend Laravel están prefijadas en `routes/api.php` bajo `/api/`.

---

### Paso 2: Implementar el Servicio de Autenticación (`js/api/auth-service.js`)

**🤔 ¿Por qué este paso?**
El backend ya cuenta con los endpoints `/api/auth/login`, `/api/miPerfil` y `/api/auth/cerrarSesion`. Necesitamos un módulo en JavaScript que exponga funciones limpias para comunicarse con estos servicios y administrar el almacenamiento del token en `localStorage`.

**🛠️ ¿Cómo?**
Crea `public/js/api/auth-service.js` utilizando la función `apiFetch` configurada en el paso anterior.

**Código de referencia:**

```javascript
// public/js/api/auth-service.js
import { apiFetch } from './config.js';

export const authService = {
    // Iniciar sesión contra el endpoint /api/auth/login del backend
    async login(email, password) {
        const respuesta = await apiFetch('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });

        if (respuesta.ok && respuesta.data.token) {
            // Guardar token Sanctum y datos del usuario en la sesión del navegador
            localStorage.setItem('token_sanctum', respuesta.data.token);
            localStorage.setItem('usuario_nombre', respuesta.data.usuario.nombre);
            localStorage.setItem('usuario_rol', respuesta.data.usuario.rol);
        }

        return respuesta;
    },

    // Obtener información del usuario autenticado vía /api/miPerfil
    async obtenerPerfil() {
        return await apiFetch('/miPerfil', { method: 'GET' });
    },

    // Cerrar sesión e invalidar token mediante /api/auth/cerrarSesion
    async logout() {
        try {
            await apiFetch('/auth/cerrarSesion', { method: 'POST' });
        } finally {
            localStorage.clear();
            window.location.href = '/login.html';
        }
    }
};
```

> 💡 **Qué hace este fragmento:** Ofrece métodos para iniciar sesión, consultar el perfil y cerrar sesión contra la API en Laravel, gestionando de forma segura la persistencia del token Sanctum.

> ⚠️ **Error común:** No invocar `localStorage.clear()` al cerrar sesión. Si el token permanece en el navegador, otras sesiones en el mismo equipo podrían acceder a datos privados.

---

### Paso 3: Conectar el Formulario de Login en el Frontend (`js/ui/login-view.js`)

**🤔 ¿Por qué este paso?**
Conecta la interfaz visual `login.html` con el servicio `authService`. Captura los datos ingresados por el paciente o médico, procesa la respuesta del backend y maneje errores de credenciales (HTTP 401) o de validación (HTTP 422).

**🛠️ ¿Cómo?**
Crea el script de vista `public/js/ui/login-view.js` y vincúlalo al evento `submit` del formulario HTML.

**Código de referencia — Formulario HTML (`login.html`):**

```html
<!-- public/login.html -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Citas Médicas</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="login-container">
        <h2>Ingreso al Sistema de Citas</h2>
        <div id="alerta_error" class="alerta oculto"></div>

        <form id="form_login">
            <input type="email" id="txt_email" placeholder="Correo Electrónico" required>
            <input type="password" id="txt_password" placeholder="Contraseña" required>
            <button type="submit" id="btn_ingresar">Ingresar</button>
        </form>
    </div>
    <script type="module" src="js/ui/login-view.js"></script>
</body>
</html>
```

**Código de referencia — Lógica de Vista (`js/ui/login-view.js`):**

```javascript
// public/js/ui/login-view.js
import { authService } from '../api/auth-service.js';

const formLogin = document.getElementById('form_login');
const txtEmail = document.getElementById('txt_email');
const txtPassword = document.getElementById('txt_password');
const alertaError = document.getElementById('alerta_error');

formLogin.addEventListener('submit', async (e) => {
    e.preventDefault();
    alertaError.classList.add('oculto');

    const email = txtEmail.value.trim();
    const password = txtPassword.value.trim();

    try {
        const respuesta = await authService.login(email, password);

        if (respuesta.ok) {
            // Redirigir al dashboard según el éxito del backend
            window.location.href = '/dashboard.html';
        } else {
            // Manejar errores de credenciales incorrectas (401) o validación (422)
            const msj = respuesta.data.mensaje || respuesta.data.msj || 'Error de autenticación';
            alertaError.textContent = msj;
            alertaError.classList.remove('oculto');
        }
    } catch (error) {
        alertaError.textContent = 'No se pudo conectar con el servidor backend.';
        alertaError.classList.remove('oculto');
    }
});
```

> 💡 **Qué hace este fragmento:** Intercepta el envío del formulario, invoca la autenticación vía API REST y redirige al panel principal si las credenciales son válidas en la base de datos.

> ⚠️ **Error común:** Olvidar `e.preventDefault()`. Esto provoca que el formulario recargue la página completa mediante un envío HTTP tradicional sin ejecutar el código de la API.

---

### Paso 4: Módulo de Consulta de Especialidades y Médicos (`js/api/doctores-service.js`)

**🤔 ¿Por qué este paso?**
Para agendar una cita, la pantalla debe llenar dinámicamente un listado desplegable de especialidades médicas y médicos activos consultando los endpoints existentes `/api/obtenerEspecialidades` y `/api/obtenerDoctores`.

**🛠️ ¿Cómo?**
Crea `public/js/api/doctores-service.js` exponiendo métodos para consultar los catálogos públicos y la disponibilidad en tiempo real de un doctor.

**Código de referencia:**

```javascript
// public/js/api/doctores-service.js
import { apiFetch } from './config.js';

export const doctoresService = {
    // Obtener catálogo de especialidades activas desde /api/obtenerEspecialidades
    async obtenerEspecialidades() {
        return await apiFetch('/obtenerEspecialidades', { method: 'GET' });
    },

    // Obtener lista de doctores activos desde /api/obtenerDoctores
    async obtenerDoctores(especialidadId = null) {
        const query = especialidadId ? `?especialidad_id=${especialidadId}` : '';
        return await apiFetch(`/obtenerDoctores${query}`, { method: 'GET' });
    },

    // Consultar slots de horarios disponibles vía /api/obtenerDisponibilidad/{doctorId}?fecha=YYYY-MM-DD
    async obtenerDisponibilidad(doctorId, fecha) {
        return await apiFetch(`/obtenerDisponibilidad/${doctorId}?fecha=${fecha}`, { method: 'GET' });
    }
};
```

> 💡 **Qué hace este fragmento:** Proporciona funciones asíncronas para obtener especialidades, médicos filtrados y bloques de horarios disponibles desde el backend Laravel.

> ⚠️ **Error común:** No enviar el parámetro `fecha` en formato `YYYY-MM-DD`. El backend esperará esa estructura exacta para calcular la disponibilidad del médico.

---

### Paso 5: Módulo de Gestión de Citas Médicas (`js/api/citas-service.js`)

**🤔 ¿Por qué este paso?**
El backend cuenta con los endpoints `/api/misCitas`, `/api/agendarCita` y `/api/cancelarMiCita/{id}` para el rol `paciente`. Encapsularemos estas llamadas para ser reutilizadas desde cualquier vista del cliente web.

**🛠️ ¿Cómo?**
Crea `public/js/api/citas-service.js` con las peticiones `GET`, `POST` y `PATCH`.

**Código de referencia:**

```javascript
// public/js/api/citas-service.js
import { apiFetch } from './config.js';

export const citasService = {
    // Obtener historial y citas del paciente desde /api/misCitas
    async obtenerMisCitas() {
        return await apiFetch('/misCitas', { method: 'GET' });
    },

    // Agendar una nueva cita médica vía /api/agendarCita
    async agendarCita(datosCita) {
        return await apiFetch('/agendarCita', {
            method: 'POST',
            body: JSON.stringify(datosCita)
        });
    },

    // Cancelar cita propia vía /api/cancelarMiCita/{id} (método PATCH)
    async cancelarCita(citaId, motivo) {
        return await apiFetch(`/cancelarMiCita/${citaId}`, {
            method: 'PATCH',
            body: JSON.stringify({ motivo_cancelacion: motivo })
        });
    }
};
```

> 💡 **Qué hace este fragmento:** Mapea las operaciones CRUD de citas contra los endpoints protegidos del backend aplicando los verbos HTTP correctos (GET, POST, PATCH).

> ⚠️ **Error común:** Usar `DELETE` en lugar de `PATCH` para la cancelación de citas. El backend de este proyecto utiliza `PATCH /api/cancelarMiCita/{id}` para actualizar el estado a 'cancelada'.

---

### Paso 6: Renderizado Dinámico del Dashboard y Agendamiento (`js/ui/dashboard-view.js`)

**🤔 ¿Por qué este paso?**
Integra la vista principal `dashboard.html`. Carga los datos del perfil del usuario, llena los selectores de médicos, despliega la disponibilidad y renderiza dinámicamente la tabla de citas médicas agendadas en el DOM.

**🛠️ ¿Cómo?**
Crea `public/js/ui/dashboard-view.js` coordinando las llamadas a los servicios y actualizando los elementos HTML.

**Código de referencia — Lógica de Pantalla Dashboard:**

```javascript
// public/js/ui/dashboard-view.js
import { authService } from '../api/auth-service.js';
import { doctoresService } from '../api/doctores-service.js';
import { citasService } from '../api/citas-service.js';

// Referencias a elementos del DOM
const lblUsuario = document.getElementById('lbl_usuario');
const btnLogout = document.getElementById('btn_logout');
const tablaCitas = document.getElementById('tabla_citas_body');
const formAgendar = document.getElementById('form_agendar_cita');
const selectDoctor = document.getElementById('select_doctor');
const inputFecha = document.getElementById('input_fecha');
const selectHora = document.getElementById('select_hora');

// Inicialización de la pantalla al cargar
document.addEventListener('DOMContentLoaded', async () => {
    lblUsuario.textContent = localStorage.getItem('usuario_nombre') || 'Paciente';
    
    btnLogout.addEventListener('click', () => authService.logout());

    await cargarMedicos();
    await cargarCitasTabla();

    // Evento de cambio para cargar horarios disponibles dinámicamente
    inputFecha.addEventListener('change', consultarDisponibilidadHorarios);
    selectDoctor.addEventListener('change', consultarDisponibilidadHorarios);
});

// Cargar catálogo de médicos en el elemento <select>
async function cargarMedicos() {
    const res = await doctoresService.obtenerDoctores();
    if (res.ok && Array.isArray(res.data.data)) {
        selectDoctor.innerHTML = '<option value="">Seleccione un Médico</option>';
        res.data.data.forEach(doc => {
            selectDoctor.innerHTML += `<option value="${doc.id}">Dr. ${doc.nombre}</option>`;
        });
    }
}

// Consultar slots de disponibilidad al backend cuando cambie la fecha o doctor
async function consultarDisponibilidadHorarios() {
    const doctorId = selectDoctor.value;
    const fecha = inputFecha.value;

    if (!doctorId || !fecha) return;

    const res = await doctoresService.obtenerDisponibilidad(doctorId, fecha);
    selectHora.innerHTML = '<option value="">Seleccione Horario</option>';

    if (res.ok && Array.isArray(res.data.slots_disponibles)) {
        res.data.slots_disponibles.forEach(hora => {
            selectHora.innerHTML += `<option value="${hora}">${hora}</option>`;
        });
    }
}

// Renderizar tabla dinámica de citas médicas en el DOM
async function cargarCitasTabla() {
    const res = await citasService.obtenerMisCitas();
    tablaCitas.innerHTML = '';

    if (res.ok && Array.isArray(res.data.data)) {
        res.data.data.forEach(cita => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${cita.id}</td>
                <td>Dr. ${cita.doctor?.nombre || 'N/A'}</td>
                <td>${cita.fecha_hora}</td>
                <td><span class="badge ${cita.estado}">${cita.estado}</span></td>
                <td>
                    ${cita.estado === 'pendiente' ? `<button onclick="cancelarCitaId(${cita.id})" class="btn-danger">Cancelar</button>` : '-'}
                </td>
            `;
            tablaCitas.appendChild(fila);
        });
    }
}

// Evento de envío del formulario de agendamiento
formAgendar.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const datos = {
        doctor_id: selectDoctor.value,
        especialidad_id: 1, // Asignado según doctor
        fecha_hora: `${inputFecha.value} ${selectHora.value}:00`,
        motivo_consulta: document.getElementById('txt_motivo').value
    };

    const res = await citasService.agendarCita(datos);

    if (res.ok) {
        alert('Cita agendada correctamente.');
        formAgendar.reset();
        await cargarCitasTabla();
    } else {
        alert(res.data.mensaje || 'No se pudo agendar la cita.');
    }
});
```

> 💡 **Qué hace este fragmento:** Coordina la interfaz web al iniciar, cargando médicos, consultando la disponibilidad de horarios mediante la API y actualizando el DOM tras agendar o cancelar citas.

> ⚠️ **Error común:** No verificar si la respuesta contiene arreglos válidos con `Array.isArray()`. Si el backend devuelve un mensaje de error en JSON, el método `.forEach()` rompería la ejecución en JavaScript.

---

## 🔍 Preguntas de comprensión

1. **¿Por qué centralizamos las llamadas a la API dentro del módulo `apiFetch(endpoint, options)` en lugar de escribir `fetch()` directamente en los eventos de los botones HTML?**
2. **¿Cómo reconoce el backend en Laravel 11 qué usuario está realizando la petición en el endpoint `/api/misCitas`?**
3. **¿Qué sucede en el cliente web si el backend devuelve un código HTTP `401 Unauthorized` y cómo responde el helper de configuración?**
4. **¿Por qué la consulta de slots de disponibilidad (`/api/obtenerDisponibilidad`) debe volver a ejecutarse dinámicamente cuando el usuario cambia la fecha en el formulario web?**

---

## ✅ Cómo saber que funciona

1. **Flujo de Inicio de Sesión:**
   - Abre `login.html`, ingresa un correo de paciente válido (ej. `paciente@ejemplo.com`) y su contraseña. Haz clic en **Ingresar**. Debe guardar el token en `localStorage` y redirigir a `dashboard.html`.

2. **Carga Dinámica de Médicos y Horarios:**
   - En el formulario de agendamiento, elige un médico del desplegable `<select>` y selecciona una fecha. El selector de horas debe poblarse automáticamente con las horas libres devueltas por el servidor.

3. **Agendamiento y Renderizado en Tiempo Real:**
   - Agenda una cita. La nueva fila debe aparecer inmediatamente en la tabla HTML de `dashboard.html` con estado **'pendiente'** sin recargar la página.

---

## 🚀 Reto extra (opcional)

Implementa una función de **búsqueda y filtrado en tiempo real** en el cliente web para que el usuario pueda filtrar la tabla de citas por estado (`pendiente`, `atendida`, `cancelada`) utilizando la función `.filter()` de JavaScript sobre la colección devuelta por la API REST.

---

## 📚 Para profundizar (opcional)

- **JWT vs Laravel Sanctum Tokens** — Diferencias de expiración y revocación de tokens en aplicaciones SPA (Single Page Application).
- **Manejo Global de Errores con Axios/Fetch Interceptors** — Patrones avanzados de intercepción de peticiones en JavaScript.
- **WebSockets / Laravel Reverb** — Actualización en vivo del panel de citas médicas sin necesidad de refrescar o consultar la API manualmente (*polling*).
