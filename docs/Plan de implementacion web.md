# 🖥️ Plan de Implementación — Frontend Web
## Sistema de Gestión de Citas Médicas · Agenda Médica

> **Versión:** 1.0 · **Fecha:** Julio 2026
> **Alcance:** Panel web completo para roles Administrador, Recepcionista y Doctor.
> **Tecnología:** HTML5 + CSS3 (Vanilla) + JavaScript ES Modules (sin frameworks).
> **Backend:** Laravel 11 REST API con autenticación Laravel Sanctum (ya terminado).
> **Ubicación de archivos:** `sistema-de-gestion-de-citas-medicas/public/`

---

## 📋 Índice

1. Visión General y Arquitectura
2. Sistema de Diseño
3. Estructura de Archivos
4. Capa de Servicios API (JS)
5. Pantallas del Sistema
6. Componentes Reutilizables
7. Guards y Control de Acceso por Rol
8. Manejo Global de Errores y Notificaciones
9. Plan de Implementación por Fases
10. Checklist de Verificación Final

---

## 1. Visión General y Arquitectura

### 1.1 Roles y sus secciones

| Rol | Panel de acceso | Menú disponible |
|---|---|---|
| **Administrador** | Dashboard completo | Pacientes, Doctores, Horarios, Especialidades, Recepcionistas, Reportes, Mi Perfil |
| **Recepcionista** | Dashboard restringido | Pacientes, Citas (Calendario), Mi Perfil |
| **Doctor** | Vista de agenda médica | Mi Agenda, Expedientes (solo lectura), Mi Perfil |

### 1.2 Flujo de Autenticación

- Usuario → login.html
- POST /api/auth/login
- Token Sanctum → localStorage
- Lee rol desde localStorage
- Redirige: admin/recepcionista → dashboard.html | doctor → mi-agenda.html

### 1.3 Tecnologías

| Capa | Tecnología |
|---|---|
| Estructura | HTML5 semántico |
| Estilos | CSS3 Vanilla + variables CSS |
| Lógica | JavaScript ES Modules |
| Fuente | Inter (Google Fonts CDN) |
| Iconos | Lucide Icons (CDN) |
| Gráficas | Chart.js (CDN) |
| HTTP | Fetch API nativa |
| Auth | Laravel Sanctum Bearer Token |

---

## 2. Sistema de Diseño

### 2.1 Paleta de Colores (css/variables.css)

```css
:root {
  --color-primary:        #1B6B93;
  --color-primary-dark:   #0F4C6B;
  --color-primary-light:  #A8D5E2;
  --color-secondary:      #2A9D8F;
  --color-secondary-light:#B5E8D5;
  --color-accent:         #E9A319;
  --color-accent-warm:    #F4A261;
  --color-danger:         #E76F51;
  --color-danger-light:   #FADED4;
  --color-bg:             #F7F9FC;
  --color-surface:        #FFFFFF;
  --color-text-primary:   #1A1A2E;
  --color-text-secondary: #4A5568;
  --color-text-muted:     #A0AEC0;
  --color-border:         #E2E8F0;
  --color-overlay:        rgba(26,26,46,0.5);
  --font-family:          'Inter', sans-serif;
  --radius-btn:   8px; --radius-card: 12px; --radius-input: 8px;
  --shadow-card:      0 2px 12px rgba(27,107,147,0.08);
  --shadow-modal:     0 8px 32px rgba(26,26,46,0.15);
  --shadow-card-hover:0 4px 20px rgba(27,107,147,0.14);
  --sidebar-width: 260px; --header-height: 64px;
}
```

### 2.2 Tipografía

| Elemento | Peso | Tamaño |
|---|---|---|
| Título H1 | Bold 700 | 28px |
| Subtítulo H2 | Semibold 600 | 22px |
| H3 sección | Medium 500 | 18px |
| Cuerpo | Regular 400 | 16px |
| Caption | Regular 400 | 13px |
| Botones | Semibold 600 | 15px |


---

## 3. Estructura de Archivos

Todos los archivos viven dentro de `sistema-de-gestion-de-citas-medicas/public/`:

```
public/
├── css/
│   ├── variables.css
│   ├── base.css
│   ├── layout.css
│   ├── components.css
│   ├── animations.css
│   └── pages/
│       ├── login.css
│       ├── dashboard.css
│       ├── citas.css
│       ├── doctores.css
│       ├── reportes.css
│       └── agenda-doctor.css
│
├── js/
│   ├── api/
│   │   ├── config.js
│   │   ├── auth-service.js
│   │   ├── pacientes-service.js
│   │   ├── doctores-service.js
│   │   ├── horarios-service.js
│   │   ├── citas-service.js
│   │   ├── notas-service.js
│   │   └── reportes-service.js
│   ├── ui/
│   │   ├── login-view.js
│   │   ├── recuperar-view.js
│   │   ├── dashboard-view.js
│   │   ├── pacientes-view.js
│   │   ├── paciente-perfil-view.js
│   │   ├── doctores-view.js
│   │   ├── horarios-view.js
│   │   ├── especialidades-view.js
│   │   ├── recepcionistas-view.js
│   │   ├── citas-view.js
│   │   ├── agendar-view.js
│   │   ├── cita-detalle-view.js
│   │   ├── reportes-view.js
│   │   ├── mi-agenda-view.js
│   │   ├── diagnostico-view.js
│   │   └── perfil-view.js
│   └── utils/
│       ├── router.js
│       ├── notifications.js
│       ├── modal.js
│       ├── formatters.js
│       └── validators.js
│
├── assets/
│   └── logo-am.svg
│
├── login.html
├── recuperar-password.html
├── verificar-codigo.html
├── restablecer-password.html
├── dashboard.html
├── pacientes.html
├── paciente-perfil.html
├── doctores.html
├── horarios.html
├── especialidades.html
├── recepcionistas.html
├── citas.html
├── agendar-cita.html
├── cita-detalle.html
├── reportes.html
├── mi-agenda.html
├── diagnostico.html
└── mi-perfil.html
```

---

## 4. Capa de Servicios API (JS)

### 4.1 config.js — Helper Central

Maneja automáticamente:
- Cabeceras `Content-Type: application/json` y `Accept: application/json`
- Inyección del Bearer Token desde `localStorage`
- Redirección a `login.html` en respuesta `401`
- Toast de error en respuesta `403`

### 4.2 Mapa de Endpoints por Servicio

| Servicio JS | Método JS | Endpoint Backend | Rol |
|---|---|---|---|
| **auth-service** | `login()` | `POST /auth/login` | Público |
| | `solicitarRecuperacion()` | `POST /auth/solicitarRecuperacion` | Público |
| | `verificarCodigo()` | `POST /auth/verificarCodigo` | Público |
| | `restablecerPassword()` | `POST /auth/restablecerPassword` | Público |
| | `logout()` | `POST /auth/cerrarSesion` | Autenticado |
| | `obtenerPerfil()` | `GET /miPerfil` | Autenticado |
| | `actualizarPerfil()` | `PUT /actualizarMiPerfil` | Autenticado |
| | `cambiarPassword()` | `POST /cambiarPassword` | Autenticado |
| **pacientes-service** | `obtenerPacientes(q)` | `GET /obtenerPacientes` | Admin/Recep |
| | `registrarPaciente(data)` | `POST /registrarPaciente` | Admin/Recep |
| | `obtenerPaciente(id)` | `GET /obtenerPaciente/{id}` | Admin/Recep |
| | `actualizarPaciente(id,d)` | `PUT /actualizarPaciente/{id}` | Admin/Recep |
| | `desactivarPaciente(id)` | `PATCH /desactivarPaciente/{id}` | Admin/Recep |
| **doctores-service** | `obtenerDoctores(esp?)` | `GET /obtenerDoctores` | Público |
| | `obtenerDoctor(id)` | `GET /obtenerDoctor/{id}` | Público |
| | `obtenerDisponibilidad(id,f)` | `GET /obtenerDisponibilidad/{id}?fecha=` | Público |
| | `registrarDoctor(data)` | `POST /registrarDoctor` | Admin |
| | `actualizarDoctor(id,d)` | `PUT /actualizarDoctor/{id}` | Admin |
| | `validarDoctor(id)` | `PATCH /validarDoctor/{id}` | Admin |
| **horarios-service** | `obtenerHorarios(dId)` | `GET /obtenerHorarios/{doctorId}` | Admin |
| | `registrarHorario(dId,d)` | `POST /registrarHorario/{doctorId}` | Admin |
| | `actualizarHorario(id,d)` | `PUT /actualizarHorario/{id}` | Admin |
| | `eliminarHorario(id)` | `DELETE /eliminarHorario/{id}` | Admin |
| | `obtenerBloqueos(dId)` | `GET /obtenerBloqueos/{doctorId}` | Admin |
| | `registrarBloqueo(dId,d)` | `POST /registrarBloqueo/{doctorId}` | Admin |
| | `eliminarBloqueo(id)` | `DELETE /eliminarBloqueo/{id}` | Admin |
| **citas-service** | `obtenerCitas(f)` | `GET /obtenerCitas` | Admin/Recep |
| | `registrarCita(data)` | `POST /registrarCita` | Admin/Recep |
| | `obtenerCita(id)` | `GET /obtenerCita/{id}` | Admin/Recep |
| | `reprogramarCita(id,d)` | `PUT /reprogramarCita/{id}` | Admin/Recep |
| | `cancelarCita(id,motivo)` | `PATCH /cancelarCita/{id}` | Admin/Recep |
| | `checkIn(id)` | `PATCH /checkInCita/{id}` | Admin/Recep |
| **notas-service** | `registrarNota(cId,d)` | `POST /registrarNota/{citaId}` | Doctor |
| | `obtenerNotas(cId)` | `GET /obtenerNotas/{citaId}` | Doctor |
| | `iniciarConsulta(id)` | `PATCH /iniciarConsulta/{id}` | Doctor |
| | `completarCita(id)` | `PATCH /completarCita/{id}` | Doctor |
| **reportes-service** | `reporteCitas(f)` | `GET /reporteCitas` | Admin |
| | `reporteDoctores(f)` | `GET /reporteDoctores` | Admin |
| | `reporteEspecialidades()` | `GET /reporteEspecialidades` | Admin |
| | `reportePacientes()` | `GET /reportePacientes` | Admin |
| | `resumenDiario()` | `GET /resumenDiario` | Admin |
| | `exportarReporte(tipo)` | `GET /exportarReporte/{tipo}` | Admin |


---

## 5. Pantallas del Sistema

---

### AUTH-01 — Login
**Archivos:** `login.html` + `js/ui/login-view.js` + `css/pages/login.css`

**Diseño:** Card centrada (440px), fondo gradiente #F7F9FC→#A8D5E2, logo medico + titulo

| ID | Tipo | Descripcion |
|---|---|---|
| `form_login` | form | Formulario principal |
| `txt_email` | input email | Correo electronico |
| `txt_password` | input password | Contrasena con toggle ojo |
| `btn_toggle_pass` | button | Toggle mostrar/ocultar |
| `lnk_recuperar` | a | Enlace recuperar contrasena |
| `btn_ingresar` | button submit | Boton principal #1B6B93 |
| `div_alerta_error` | div | Alerta de error (oculta) |

**Logica:** POST /auth/login → guardar token/rol en localStorage → redirigir (admin/recep→dashboard, doctor→mi-agenda)

---

### AUTH-02 — Recuperacion de Contrasena (3 pasos)

**Paso 1 - recuperar-password.html:** campo email + POST /auth/solicitarRecuperacion
**Paso 2 - verificar-codigo.html:** campo codigo 6 digitos + POST /auth/verificarCodigo
**Paso 3 - restablecer-password.html:** nueva contrasena + indicador fortaleza + POST /auth/restablecerPassword

---

### ADMIN-01 — Dashboard Principal
**Archivos:** `dashboard.html` + `js/ui/dashboard-view.js`
**Acceso:** Administrador y Recepcionista

**Stat Cards:**

| ID | Icono | Color | Fuente |
|---|---|---|---|
| `stat_total_dia` | calendar | #1B6B93 | GET /resumenDiario |
| `stat_completadas` | check-circle | #2A9D8F | GET /resumenDiario |
| `stat_pendientes` | clock | #E9A319 | GET /resumenDiario |
| `stat_canceladas` | x-circle | #E76F51 | GET /resumenDiario |

**Tabla Agenda del Dia:** Hora | Paciente | Doctor | Especialidad | Estado — API: GET /obtenerCitas?fecha={hoy}
**Lista Proximas Citas:** 4 tarjetas compactas, limite 4 resultados futuros

**Sidebar Admin:** Inicio | Pacientes | Citas | Doctores | Reportes | Mi Perfil
**Sidebar Recep:** Inicio | Pacientes | Citas | Mi Perfil

---

### ADMIN-02 — Gestion de Pacientes
**Archivos:** `pacientes.html` + `js/ui/pacientes-view.js`
**Acceso:** Administrador y Recepcionista

| ID | Funcion |
|---|---|
| `inp_buscar_paciente` | Busqueda tiempo real (debounce 500ms) |
| `btn_nuevo_paciente` | Abre modal registro |
| `tabla_pacientes_body` | Renderizado dinamico |
| `paginacion_container` | Controles paginacion 10/pag |
| `modal_nuevo_paciente` | Modal registro/edicion |

**Columnas tabla:** # Expediente | Nombre | CURP | Telefono | Correo | Estado | Acciones
**Acciones:** Ver perfil | Editar | Desactivar

**Modal campos:**

| Campo | ID | Validacion |
|---|---|---|
| Nombre Completo | `txt_nombre_pac` | Requerido min 5 |
| Fecha Nacimiento | `inp_fecha_nac` | No futura |
| Sexo | `sel_sexo` | Requerido |
| CURP | `txt_curp` | 18 chars regex |
| Telefono | `txt_telefono_pac` | 10 digitos |
| Correo | `txt_email_pac` | Email valido |
| Direccion | `txt_direccion` | Opcional |

Registro: POST /registrarPaciente | Edicion: PUT /actualizarPaciente/{id}

---

### ADMIN-03 — Perfil / Expediente de Paciente
**Archivos:** `paciente-perfil.html` + `js/ui/paciente-perfil-view.js`
**Acceso:** Administrador y Recepcionista

**Cabecera:** Avatar 80px iniciales + nombre + CURP + expediente + badge estado + boton editar

**Tabs:**

| Tab ID | Titulo | Contenido |
|---|---|---|
| `tab_info` | Informacion Personal | Datos solo lectura |
| `tab_historial` | Historial de Citas | Timeline con estado, doctor, fecha |
| `tab_diagnosticos` | Diagnosticos | Notas clinicas de doctores |

API: GET /obtenerPaciente/{id}

---

### ADMIN-04 — Gestion de Doctores
**Archivos:** `doctores.html` + `js/ui/doctores-view.js`
**Acceso:** solo Administrador

**Layout:** Grid 3 columnas de cards
**Busqueda:** por nombre o especialidad
**API:** GET /obtenerDoctores?especialidad_id={id}

**Contenido cada card:**
- Avatar 64px con iniciales en #A8D5E2
- Nombre Inter Semibold 16px
- Badge especialidad: pill #B5E8D5/texto #2A9D8F
- Cedula profesional #A0AEC0 12px
- Iconos contacto (tel + email)
- Enlace "Horarios" → horarios.html?doctor={id}
- Enlace "Editar" → modal
- Estado Inactivo: borde rojo, opacidad 0.6

**Modal campos:** Nombre | Especialidad | Cedula | Telefono | Correo
POST /registrarDoctor | PUT /actualizarDoctor/{id} | PATCH /validarDoctor/{id}

---

### ADMIN-05 — Configuracion de Horarios del Doctor
**Archivos:** `horarios.html` + `js/ui/horarios-view.js`
**Acceso:** solo Administrador

**Layout:**
- Breadcrumb: Doctores > Dr. Nombre > Horarios
- Mini-card del doctor
- Grid semanal 7 columnas + Panel Bloqueos (280px)

**Colores de bloques:**
- Consulta: fondo #A8D5E2, texto #1B6B93
- Descanso: fondo #F7F9FC, borde punteado
- Bloqueado: fondo #FADED4, texto #E76F51

**Formulario Agregar Horario:** dia semana | hora inicio | hora fin | duracion (15/20/30/45/60 min)
- POST /registrarHorario/{doctorId} | DELETE /eliminarHorario/{id}

**Panel Bloqueos:** Desde/Hasta date pickers + Motivo dropdown + alerta citas afectadas
- POST /registrarBloqueo/{doctorId} → { fecha_inicio, fecha_fin, motivo }

---

### ADMIN-06 — Gestion de Especialidades
**Archivos:** `especialidades.html` + `js/ui/especialidades-view.js`
**Acceso:** solo Administrador

Tabla: # | Nombre | Doctores Activos | Acciones
Modal: campo txt_nombre_esp → POST /registrarEspecialidad → { nombre }

---

### ADMIN-07 — Gestion de Recepcionistas
**Archivos:** `recepcionistas.html` + `js/ui/recepcionistas-view.js`
**Acceso:** solo Administrador
**Nota:** Las recepcionistas NO se autoregistran, las crea el Admin.

Tabla: Nombre | Correo | Estado | Fecha Alta | Acciones

**Modal campos:**

| Campo | ID | Validacion |
|---|---|---|
| Nombre Completo | `txt_nombre_recep` | Requerido |
| Correo | `txt_email_recep` | Email valido unico |
| Contrasena Inicial | `txt_pass_recep` | Min 8 chars |
| Confirmar Contrasena | `txt_pass_conf_recep` | Debe coincidir |

API: POST /auth/registrarRecepcionista

---

### ADMIN-08 — Modulo de Reportes
**Archivos:** `reportes.html` + `js/ui/reportes-view.js`
**Acceso:** solo Administrador

**Barra de Filtros:**

| ID | Funcion | API |
|---|---|---|
| `inp_desde` + `inp_hasta` | Rango de fechas | — |
| `sel_doctor_reporte` | Filtro por doctor | — |
| `sel_esp_reporte` | Filtro especialidad | — |
| `btn_generar_reporte` | Lanza peticiones | Multiple endpoints |
| `btn_exportar_pdf` | Exportar | GET /exportarReporte/pdf |
| `btn_exportar_excel` | Exportar | GET /exportarReporte/excel |

**Stat Cards:** Total Agendadas (#1B6B93) | Completadas (#2A9D8F) | Canceladas (#E76F51) | Tasa % (#E9A319)

**Graficas Chart.js:**

| Grafica | Tipo | Canvas ID | Endpoint |
|---|---|---|---|
| Citas por Periodo | Bar agrupado | `chart_citas_periodo` | GET /reporteCitas |
| Especialidades | Doughnut | `chart_especialidades` | GET /reporteEspecialidades |

**Tabla Doctores:** Posicion | Doctor | Especialidad | Consultas | Tasa — barra CSS horizontal — GET /reporteDoctores

---

### RECEP-01 — Calendario de Citas
**Archivos:** `citas.html` + `js/ui/citas-view.js` + `css/pages/citas.css`
**Acceso:** Administrador y Recepcionista

**Controles:**

| ID | Funcion |
|---|---|
| `btn_vista_dia/semana/mes` | Cambia vista activa |
| `btn_anterior/siguiente` | Navegar periodos |
| `lbl_rango_fecha` | Texto del rango |
| `sel_filtro_doctor` | Filtrar por doctor |
| `btn_nueva_cita` | Navega a agendar-cita.html |

**Vista Semana (defecto):** Grid 7 col (Lun-Dom), eje tiempo 8:00-18:00, bloques posicionados absolutamente
Colores: Confirmada #1B6B93 | Completada #2A9D8F | Pendiente #E9A319 | Cancelada #E76F51
Linea roja = hora actual | Clic en bloque → panel lateral de resumen

API: GET /obtenerCitas?fecha_inicio={}&fecha_fin={}&doctor_id={}

---

### RECEP-02 — Agendar Nueva Cita (Wizard 3 pasos)
**Archivos:** `agendar-cita.html` + `js/ui/agendar-view.js`
**Acceso:** Administrador y Recepcionista

**Paso 1 — Seleccionar Paciente:**
- `inp_buscar_pac_cita`: busqueda en vivo debounce
- `ul_resultados_pac`: lista clickeable
- `card_pac_seleccionado`: resumen elegido
- `btn_paso2`: deshabilitado hasta seleccionar
- API: GET /obtenerPacientes?q={query}

**Paso 2 — Doctor y Horario:**
- `sel_especialidad_cita` → GET /obtenerEspecialidades
- `sel_doctor_cita` → se puebla al elegir especialidad
- `inp_fecha_cita` → date picker
- `div_slots_hora` → GET /obtenerDisponibilidad/{id}?fecha= (pills interactivos)
- `txt_motivo_cita` → requerido min 10 chars

**Paso 3 — Confirmacion:** tarjeta resumen + POST /registrarCita → { paciente_id, doctor_id, especialidad_id, fecha_hora, motivo_consulta }

---

### RECEP-03 — Detalle de Cita
**Archivos:** `cita-detalle.html` + `js/ui/cita-detalle-view.js`
**Acceso:** Admin, Recepcionista, Doctor

Info: Fecha/hora | Estado badge | Codigo referencia | Paciente | Doctor | Motivo | Notas clinicas

**Acciones por estado:**

| Estado | Recep/Admin | Doctor |
|---|---|---|
| agendada | Reprogramar, Cancelar, Check-in | — |
| confirmada | Cancelar | Iniciar Consulta |
| en_consulta | — | Ir a diagnostico.html |
| completada | Ver notas | Ver notas |
| cancelada | — | — |

---

### DOCTOR-01 — Mi Agenda del Dia
**Archivos:** `mi-agenda.html` + `js/ui/mi-agenda-view.js`
**Acceso:** solo Doctor

**Sidebar Doctor:** Mi Agenda (activo) | Expedientes | Mi Perfil | Cerrar Sesion

**Bienvenida:** "Buenos dias/tardes, Dr. {nombre}" + "Hoy tienes {n} consultas"

**Timeline:**

| Estado | Borde-left | Boton accion |
|---|---|---|
| confirmada | #2A9D8F | "Iniciar Consulta" → PATCH /iniciarConsulta/{id} |
| en_consulta | #1B6B93 + punto pulsante | "Registrar Diagnostico" → diagnostico.html |
| pendiente | #E9A319 | "Confirmar Llegada" |
| completada | #A0AEC0 | "Ver Expediente" |

Slots vacios: tarjeta punteada "HH:MM AM — Disponible"
API: GET /obtenerCitas?doctor_id={mi_id}&fecha={hoy}

---

### DOCTOR-02 — Formulario de Diagnostico
**Archivos:** `diagnostico.html` + `js/ui/diagnostico-view.js`
**Acceso:** solo Doctor (cita en estado en_consulta)

**Seccion Signos Vitales** (icono corazon #E76F51):
- `inp_presion` (mmHg) | `inp_frecuencia` (bpm) | `inp_temperatura` (C) | `inp_peso` (kg)

**Seccion Diagnostico** (icono #1B6B93): `textarea_diagnostico` 5 filas
**Seccion Tratamiento** (icono #2A9D8F): `textarea_tratamiento` 4 filas
**Seccion Notas Adicionales** (colapsable): `textarea_notas_adicionales`

**Acciones:**
- "Guardar borrador" → POST /registrarNota/{citaId}
- "Completar Consulta" → POST /registrarNota/{citaId} + PATCH /completarCita/{id} → mi-agenda.html

---

### SHARED-01 — Mi Perfil (todos los roles)
**Archivos:** `mi-perfil.html` + `js/ui/perfil-view.js`
**Acceso:** todos los roles

**Cabecera:** Avatar 96px iniciales fondo #2A9D8F | nombre + badge rol

**Datos Personales:**

| Campo | Editable | Endpoint |
|---|---|---|
| Nombre | No (lock) | — |
| Correo | No (lock) | — |
| Telefono | Si | PUT /actualizarMiPerfil |
| Foto | Si | POST /actualizarFoto |

**Cambiar Contrasena:** actual + nueva + confirmar + indicador fortaleza (4 segmentos)
API: POST /cambiarPassword


---

## 6. Componentes Reutilizables

### 6.1 Sidebar
- Fondo #0F4C6B, ancho 260px
- Item activo: borde izquierdo 3px #2A9D8F + fondo rgba(255,255,255,0.08)
- Icono Lucide + label blanco por cada enlace

### 6.2 Header
- Fondo blanco, altura 64px, borde-bottom #E2E8F0
- Titulo H1 de pagina | icono notificacion | avatar | fecha actual

### 6.3 Stat Card
- Blanco, border-radius 12px, sombra --shadow-card
- Hover: translateY(-2px) + sombra --shadow-card-hover

### 6.4 Badges de Estado
```css
.badge--success { background: #B5E8D5; color: #2A9D8F; }  /* Confirmada  */
.badge--warning { background: #FEF3C7; color: #E9A319; }  /* Pendiente   */
.badge--danger  { background: #FADED4; color: #E76F51; }  /* Cancelada   */
.badge--info    { background: #A8D5E2; color: #1B6B93; }  /* En Consulta */
```

### 6.5 Modal Generico (js/utils/modal.js)
```javascript
modal.open('modal_id');   // Muestra modal con overlay
modal.close('modal_id');  // Cierre con Escape y clic en overlay tambien
```

### 6.6 Toast / Notificaciones (js/utils/notifications.js)
```javascript
notify.success('Cita agendada correctamente');
notify.error('No se pudo conectar con el servidor');
notify.warning('3 citas se veran afectadas');
notify.info('Sesion iniciada como Recepcionista');
```
- Posicion: esquina superior derecha
- Auto-dismiss: 4 segundos
- Animacion: transform + opacity

### 6.7 Skeleton Loader
- Clase `.skeleton` en contenedores mientras cargan datos de la API
- Efecto shimmer con linear-gradient animado 1.5s

---

## 7. Guards y Control de Acceso por Rol

**Archivo:** `js/utils/router.js`

```javascript
import { checkAuth, checkRole } from '../utils/router.js';

checkAuth();                           // Redirige a login.html si no hay token
checkRole(['admin']);                   // Redirige a dashboard.html si rol no coincide
checkRole(['admin', 'recepcionista']);
checkRole(['doctor']);
```

| Pagina HTML | Roles permitidos |
|---|---|
| login.html | Publico (redirige si autenticado) |
| recuperar-*.html | Publico |
| dashboard.html | admin, recepcionista |
| pacientes.html | admin, recepcionista |
| paciente-perfil.html | admin, recepcionista |
| doctores.html | **solo admin** |
| horarios.html | **solo admin** |
| especialidades.html | **solo admin** |
| recepcionistas.html | **solo admin** |
| reportes.html | **solo admin** |
| citas.html | admin, recepcionista |
| agendar-cita.html | admin, recepcionista |
| cita-detalle.html | admin, recepcionista, doctor |
| mi-agenda.html | **solo doctor** |
| diagnostico.html | **solo doctor** |
| mi-perfil.html | admin, recepcionista, doctor |

---

## 8. Manejo Global de Errores y Notificaciones

### 8.1 Codigos HTTP y Respuesta en UI

| Codigo | Significado | Accion en Frontend |
|---|---|---|
| 200 / 201 | Exito | Toast verde + actualizar DOM |
| 401 | Sesion expirada | localStorage.clear() + redirect login.html |
| 403 | Sin permisos de rol | Toast rojo + redirect dashboard.html |
| 422 | Validacion fallida | Errores inline debajo de cada campo |
| 409 | Conflicto (horario solapado) | Toast amarillo con mensaje especifico |
| 500 | Error del servidor | Toast rojo "Error del servidor. Intenta de nuevo." |
| Sin red | Sin conexion | Toast rojo "Sin conexion con el servidor" |

### 8.2 Validaciones en Cliente (js/utils/validators.js)

| Funcion | Regla |
|---|---|
| `validarCURP(curp)` | Regex oficial CURP Mexico 18 chars |
| `validarEmail(email)` | Formato estandar |
| `validarTelefono(tel)` | 10 digitos numericos |
| `validarPassword(pass)` | Min 8 chars, 1 mayuscula, 1 numero |
| `validarFecha(fecha)` | No fecha futura para nacimiento |

Errores: clase `.field-error` + borde #E76F51 en el input.

---

## 9. Plan de Implementacion por Fases

### Fase 1 — Cimientos del Sistema (2-3 dias)
- [ ] Crear estructura de carpetas y archivos vacios
- [ ] Implementar css/variables.css, css/base.css, css/layout.css
- [ ] Implementar css/components.css (botones, inputs, badges, cards, modales, tablas)
- [ ] Implementar css/animations.css (skeleton, hover effects, toasts)
- [ ] Implementar js/api/config.js con manejo 401/403
- [ ] Implementar js/utils/router.js, notifications.js, modal.js, formatters.js, validators.js

### Fase 2 — Autenticacion (1 dia)
- [ ] login.html + js/ui/login-view.js con redireccion por rol
- [ ] recuperar-password.html + verificar-codigo.html + restablecer-password.html
- [ ] js/api/auth-service.js completo (todos los metodos)

### Fase 3 — Panel Admin: Pacientes y Doctores (2-3 dias)
- [ ] js/api/pacientes-service.js + js/api/doctores-service.js
- [ ] dashboard.html + stat cards + tabla del dia
- [ ] pacientes.html + modal + busqueda en tiempo real
- [ ] paciente-perfil.html + tabs historial/diagnosticos
- [ ] doctores.html + modal registro/edicion/validar

### Fase 4 — Horarios, Especialidades, Recepcionistas (2 dias)
- [ ] js/api/horarios-service.js
- [ ] horarios.html + grid semanal + panel de bloqueos
- [ ] especialidades.html
- [ ] recepcionistas.html + modal

### Fase 5 — Modulo de Citas (2-3 dias)
- [ ] js/api/citas-service.js completo
- [ ] citas.html + calendario semana con bloques posicionados
- [ ] Vistas Dia y Mes
- [ ] agendar-cita.html + wizard 3 pasos con slots dinamicos
- [ ] cita-detalle.html + acciones condicionales por estado y rol

### Fase 6 — Vista del Doctor (1-2 dias)
- [ ] js/api/notas-service.js
- [ ] mi-agenda.html + timeline + sidebar del doctor
- [ ] diagnostico.html + signos vitales + notas clinicas

### Fase 7 — Reportes y Mi Perfil (1-2 dias)
- [ ] js/api/reportes-service.js
- [ ] reportes.html + Chart.js (barra + dona) + tabla de doctores
- [ ] mi-perfil.html + edicion telefono + contrasena + foto

### Fase 8 — Pulido Final y Pruebas (1-2 dias)
- [ ] Flujo completo con backend activo (php artisan serve)
- [ ] Guards de rol en todas las paginas
- [ ] Responsive en 1280px+
- [ ] Manejo de errores (401, 403, 422, 500, sin red)
- [ ] Animaciones y micro-interacciones

---

## 10. Checklist de Verificacion Final

### Autenticacion
- [ ] Login guarda token y rol en localStorage
- [ ] Redireccion correcta segun rol
- [ ] Guard bloquea paginas sin token
- [ ] Guard bloquea paginas con rol incorrecto
- [ ] Logout limpia localStorage y redirige a login
- [ ] Flujo recuperacion de contrasena funciona en 3 pasos

### Pacientes
- [ ] Busqueda en tiempo real con debounce
- [ ] Modal registra y edita con validacion CURP/telefono/email
- [ ] Perfil muestra tabs de historial y diagnosticos
- [ ] Desactivar requiere confirmacion

### Doctores y Horarios
- [ ] Cards muestran activos e inactivos diferenciados
- [ ] Modal registra, edita y valida correctamente
- [ ] Grid semanal muestra bloques coloreados por tipo
- [ ] Bloqueo de horario muestra alerta de citas afectadas

### Citas
- [ ] Calendario semana con bloques posicionados correctamente
- [ ] Filtro por doctor funciona sin recargar
- [ ] Wizard llena slots dinamicos segun doctor y fecha
- [ ] Confirmacion y redireccion al calendario
- [ ] Reprogramar y cancelar desde detalle

### Doctor
- [ ] Timeline muestra solo citas del doctor logueado en el dia actual
- [ ] "Iniciar Consulta" actualiza el estado
- [ ] Diagnostico guarda signos vitales y notas
- [ ] "Completar Consulta" cambia estado y redirige

### Reportes
- [ ] Grafica de barras renderiza datos reales
- [ ] Grafica de dona muestra especialidades
- [ ] Filtros actualizan graficas correctamente
- [ ] Exportacion PDF/Excel funciona

### UX / Diseno
- [ ] Sidebar activo resaltado en cada pagina
- [ ] Toasts aparecen y desaparecen (4s)
- [ ] Skeleton loaders mientras cargan datos
- [ ] Hover effects en cards y botones
- [ ] Modales cierran con Escape y overlay
- [ ] IDs unicos y descriptivos en todos los elementos
- [ ] Fuente Inter + Lucide Icons cargados correctamente

---

## Referencias del Proyecto

| Documento | Descripcion |
|---|---|
| Instrucciones_Diseno_Visual_Frontend.md | Diseno visual detallado por pantalla (prompts Stitch) |
| Guia_Conexion_Servicio_Web.md | Codigo de referencia para la capa de servicios JS |
| Sistema_Gestion_Citas_Medicas.md | Especificacion funcional del sistema |
| api.php (routes/) | Endpoints del backend Laravel 11 |

---

*Plan de Implementacion Web — Agenda Medica · Sistema de Gestion de Citas Medicas · v1.0 · Julio 2026*
