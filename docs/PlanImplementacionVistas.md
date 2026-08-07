# Plan de Implementación: Rediseño Visual del Frontend (Blade SSR)

---

## 1. Resumen Ejecutivo y Objetivo

El objetivo de este plan de implementación es guiar la **rediseño e integración visual completa** de la interfaz web del **Sistema de Gestión de Citas Médicas**, sustituyendo el diseño frontend actual por las nuevas pantallas responsivas y profesionales proporcionadas en la ruta:

```
C:\Users\zhair\OneDrive\Escritorio\Integradora 4\Gestor-de-citas-medicas-master\Gestor-de-citas-medicas-master\Example screens\Web
```

### Premisas clave de la migración:
1. **Conservación de la Arquitectura Blade SSR:** Se mantendrán intactos los archivos `.blade.php` en `resources/views`, preservando las rutas web (`routes/web.php`), controladores web (`app/Http/Controllers/Web`), repositorios y la lógica de autenticación y autorización por roles.
2. **Sistema de Diseño "Clinical Clarity":** Se adoptará el esquema cromático, la tipografía *Inter*, el sistema de tarjetas elevadas y la iconografía limpia definida en las maquetas (`Example screens/Web/clinical_clarity/DESIGN.md`).
3. **Compatibilidad total con Roles:** La interfaz adaptará dinámicamente sus menús, componentes y acciones según el rol del usuario autenticado (*Administrador*, *Recepcionista*, *Doctor*, *Paciente*).

### Estado general del avance
- El frontend **se rediseñó casi por completo** siguiendo *Clinical Clarity* (19 de 20 vistas funcionales).
- Quedan **3 correcciones / pendientes menores** documentados en la [Sección 7](#7-pendientes-y-correcciones-detectadas): tokens indefinidos en vista del doctor, falta de pestañas en perfil del paciente y PDF de reportes con estilo pre-rediseño.

---

## 2. Mapa de Correspondencia de Pantallas y Vistas Blade

A continuación se detalla el mapeo exacto entre cada carpeta de maquetas (`Example screens/Web/*`) y su correspondiente archivo de vista Blade en la aplicación Laravel. Se incluyen también las vistas existentes que **no tienen maqueta directa** (módulos adicionales/administrativos).

| Pantalla / Maqueta de Ejemplo (`Example screens/Web/`) | Vista Blade Destino (`sistema-de-gestion-de-citas-medicas/resources/views/`) | Descripción y Alcance | Estado |
| :--- | :--- | :--- | :--- |
| **`login_agenda_m_dica`** | `auth/login.blade.php` | Pantalla de inicio de sesión centrada y estilizada. | ✅ Implementada |
| **`recuperar_contrase_a_web`** | `auth/recuperar-password.blade.php` | Paso 1: solicitar recuperación de contraseña por email. | ✅ Implementada |
| **`recuperar_contrase_a_web`** | `auth/verificar-codigo.blade.php` | Paso 2: verificación del código de recuperación. | ✅ Implementada |
| **`recuperar_contrase_a_web`** | `auth/restablecer-password.blade.php` | Paso 3: establecimiento de la nueva contraseña. | ✅ Implementada |
| *(Sin maqueta directa)* | `auth/registro.blade.php` | Registro autónomo de pacientes (nombre, CURP, email, NSS, contraseña). | ✅ Implementada |
| **`dashboard_recepcionista`** | `dashboard/index.blade.php` | Panel principal con métricas clave y tabla de agenda diaria. Filtra según rol (Doctor ve sus consultas; Paciente ve sus citas). | ✅ Implementada |
| **`gesti_n_de_pacientes_web`** | `pacientes/index.blade.php` | Listado, búsqueda (nombre/CURP/expediente) y **modal inline** de alta/edición de pacientes. | ✅ Implementada |
| **`registro_de_paciente_modal_web`** | `pacientes/index.blade.php` (modal `#modal_paciente`) | Modal estandarizado de registro de nuevo paciente (integrado en el listado; el plan original lo ubicaba en un componente separado). | ✅ Implementada |
| **`perfil_y_expediente_del_paciente_web`** | `pacientes/perfil.blade.php` | Vista de detalle de expediente e historial de citas del paciente. ⚠️ El criterio de *pestañas* (Información/Historial/Diagnósticos) **no se implementó**; se usa una columna apilada. | ⚠️ Parcial (ver Sección 7) |
| **`gesti_n_de_citas_web`** | `citas/index.blade.php` | Calendario **semanal interactivo** (grid 7 columnas × 8–18h) con navegación de semanas, filtro por doctor y resumen lateral semanal. | ✅ Implementada |
| **`gesti_n_de_citas_web`** | `citas/agendar.blade.php` | Agendamiento guiado: especialidad → médico → fecha → slots de disponibilidad (vía `/api/obtenerDisponibilidad`). | ✅ Implementada |
| *(Sin maqueta directa)* | `citas/detalle.blade.php` | Detalle de cita con acciones por rol (check-in, cancelación con motivo, inicio de consulta). | ✅ Implementada |
| **`mi_agenda_vista_doctor`** | `doctor/agenda.blade.php` | Línea de tiempo de consultas diarias del médico, con acciones por estado. ⚠️ Tokens `bg-tertiary-fixed` / `bg-surface-container-high` no definidos. | ⚠️ Parcial (Sección 7) |
| **`registro_de_diagn_stico_vista_doctor`** | `doctor/diagnostico.blade.php` | Formulario de consulta médica: signos vitales, diagnóstico y tratamiento (POST a `notas.store`). | ✅ Implementada |
| **`gesti_n_de_doctores_web`** | `doctores/index.blade.php` | Directorio de médicos en **grid de tarjetas** por especialidad, con validación y modal de alta. | ✅ Implementada |
| **`configuraci_n_de_horarios_admin`** | `doctores/horarios.blade.php` | Grid semanal de horarios, pausas y panel/bloqueos de agenda. | ✅ Implementada |
| **`m_dulo_de_reportes_web`** | `reportes/index.blade.php` | Tablero de analíticas con filtros de rango/doctor/especialidad, gráficos Chart.js y exportación a PDF. | ✅ Implementada |
| **`m_dulo_de_reportes_web`** | `reportes/pdf.blade.php` | Plantilla standalone para el PDF exportado. ⚠️ Sin `@extends` y con estilos pre-rediseño (HTML plano para impresión). | ⚠️ No rediseñada (Sección 7) |
| *(Sin maqueta directa)* | `especialidades/index.blade.php` | Catálogo de especialidades (administrador). | ✅ Implementada |
| *(Sin maqueta directa)* | `recepcionistas/index.blade.php` | Gestión de recepcionistas (administrador). | ✅ Implementada |
| *(Sin maqueta directa)* | `perfil/index.blade.php` | Perfil del usuario autenticado (foto, datos, cambio de contraseña). | ✅ Implementada |

**Leyenda del estado:** ✅ Implementada / ⚠️ Parcial / ❌ Pendiente.

---

## 3. Especificación del Sistema de Diseño ("Clinical Clarity")

El rediseño se basará en las siguientes variables CSS y clases utilitarias para mantener coherencia estética en todas las pantallas.

### 3.1 Paleta de Colores Principal
* **Primary (Azul Clínico):** `#005275` (Acciones primarias, botones principales, navegación).
* **Primary Dark / Container:** `#0F4C6B` / `#1B6B93` (Sidebar, banners y encabezados).
* **Secondary (Teal / Verde Sanitario):** `#006A60` / `#8CF5E4` / `#B5E8D5` (Estados completados/confirmados).
* **Tertiary / Warning (Ámbar):** `#885C00` / `#FFBA42` (Citas pendientes / en espera).
* **Danger (Coral / Rojo Sanitario):** `#E76F51` / `#BA1A1A` (Citas canceladas, errores).
* **Background:** `#F7F9FC` (Fondo general antireflejo).
* **Surface / Cards:** `#FFFFFF` con bordes sutiles `#E2E8F0` y sombra leve (`0 2px 12px rgba(27,107,147,0.08)`).

> ⚠️ **Nota de consistencia:** `layouts/auth.blade.php` define un subconjunto reducido de tokens (no incluye `primary-container`, `secondary-light`, `tertiary`, `tertiary-fixed-dim`, `danger-light`). Actualmente las vistas de autenticación no usan esos colores, por lo que no hay rotura visual; sin embargo, se recomienda homogeneiza la configuración de ambos layouts (ver Sección 7).

### 3.2 Tipografía e Iconografía
* **Tipografía:** *Inter* (Google Fonts) en pesos `400` (Regular), `500` (Medium), `600` (SemiBold) y `700` (Bold).
* **Iconos:** *Material Symbols Outlined* (`calendar`, `users`, `stethoscope`, `clock`, `check_circle`, `cancel`, `bar_chart`, `schedule`, `play_circle`, etc.).

---

## 4. Especificación de Componentes Compartidos

| Componente | Archivo (`resources/views/`) | Descripción | Estado |
| :--- | :--- | :--- | :--- |
| **Layout autenticado** | `layouts/app.blade.php` | Dos columnas: sidebar fijo de 260px (`#0F4C6B`) + contenido fluido con fondo `#F7F9FC`. Define toda la paleta de tokens, Inter, Material Symbols y utilidades `.card-shadow`/`.sidebar-item-active`. | ✅ Implementado |
| **Layout público/auth** | `layouts/auth.blade.php` | Fondo con gradiente azul suave y tarjeta centrada. Paleta reducida (ver Sección 7). | ✅ Implementado |
| **Sidebar navegable** | `components/sidebar.blade.php` | Ítems adaptados por rol (`admin | recepcionista | doctor | paciente`), estado activo y logout. | ✅ Implementado |
| **Flash messages** | `components/flash-message.blade.php` | Mensajes de sesión (éxito/error) con estilos semánticos. | ✅ Implementado |

---

## 5. Fases de Implementación Paso a Paso

> **Estado global:** todas las fases están implementadas salvo los pendientes menores listados en la [Sección 7](#7-pendientes-y-correcciones-detectadas).

### Fase 1: Actualización de Layouts y Estilos Base ⤴️ **COMPLETADA**
✅ &nbsp;`layouts/app.blade.php`, `layouts/auth.blade.php`, `components/sidebar.blade.php` migrados a *Clinical Clarity* (sidebar fijo 260px, header con perfil/fecha, sidebar por roles, tokens de color).

### Fase 2: Rediseño de Pantallas de Autenticación ✅ **COMPLETADA**
✅ `auth/login.blade.php` según `login_agenda_m_dica`.
✅ `auth/recuperar-password`, `verificar-codigo`, `restablecer-password` según `recuperar_contrase_a_web`.
✅ `auth/registro.blade.php` (módulo adicional) con el mismo estilo.

### Fase 3: Rediseño del Panel Principal (Dashboard) ✅ **COMPLETADA**
✅ `dashboard/index.blade.php`: 4 tarjetas KPI (`$statTotalDia`, `$statCompletadas`, `$statPendientes`, `$statCanceladas`), tabla **Agenda del Día** con badges de estado y avatar de iniciales, lista **Próximas Citas**, y filtrado por rol.

### Fase 4: Rediseño del Módulo de Pacientes ⚠️ **CASI COMPLETADO**
✅ `pacientes/index.blade.php`: búsqueda, tabla, modal inline de alta/edición, acciones (ver/editar/desactivar) y paginación.
⚠️ `pacientes/perfil.blade.php`: contenido completo pero **sin las pestañas** Información / Historial / Diagnósticos que marca el plan (columna apilada). → *Pendiente en Sección 7*.

### Fase 5: Rediseño del Módulo de Citas y Calendario ✅ **COMPLETADA**
✅ `citas/index.blade.php`: rejilla semanal (`grid` con bloques posicionados por `top: {{ $topPx }}px`), navegador de semanas, filtro por doctor y resumen lateral.
✅ `citas/agendar.blade.php`: agendamiento guiado con especialidad → médico → fecha → slots de disponibilidad.
✅ `citas/detalle.blade.php`: acciones por rol (check-in, cancel con modal, iniciar consulta, vínculo a diagnóstico).

### Fase 6: Rediseño de Vistas del Doctor ⚠️ **COMPLETADO CON NOTAS**
✅ `doctor/agenda.blade.php`: línea de tiempo diaria con dot/iconos por estado y acciones ("Iniciar Consulta", "Registrar Diagnóstico", "Ver Nota").
⚠️ **Tokens `bg-tertiary-fixed` y `bg-surface-container-high` no están definidos** en la configuración de Tailwind de `layouts/app.blade.php` → no pintan fondo. → *Corregir en Sección 7*.
✅ `doctor/diagnostico.blade.php`: formulario POST a `notas.store` (signos vitales, diagnóstico, tratamiento) con `@error`.

### Fase 7: Rediseño de Administración de Doctores y Horarios ✅ **COMPLETADA**
✅ `doctores/index.blade.php`: directorio en **grid de tarjetas** por especialidad, validación (PATCH) y modal de alta.
✅ `doctores/horarios.blade.php`: configurador semanal (`horarios.store`→, `horarios.destroy`) + panel de bloqueos (`bloqueos.store`/`.destroy`).
✅ Módulos administrativos adicionales (`especialidades`, `recepcionistas`) y `perfil` con el mismo sistema.

### Fase 8: Rediseño del Módulo de Reportes ⚠️ **COMPLETADO EN NOTAS**
✅ `reportes/index.blade.php`: filtros (rango/doctor/especialidad), 4 KPIs, gráficos Chart.js (doughnut por estado + barras por especialidad) y exportación.
⚠️ `reportes/pdf.blade.php`: plantilla standalone/para impresión con **CSS antiguo** (`#2a9d8f`, `#ddd`); no usa el sistema de tokens. → *A valor si se requiere homogeneización (Sección 7)*.

---

## 6. Matriz de Verificación y Pruebas de Calidad

| Prueba / Verificación | Criterio de Aceptación | Estado Esperado | Estado Real |
| :--- | :--- | :--- | :--- |
| **Sintaxis Blade & Funcionalidad** | Sin errores de renderizado en directivas `@extends`, `@section`, `@forelse`, `@csrf`. | OK | ✅ Cumple |
| **Integridad de Formularios** | Todos los formularios conservan sus `action`, `method` (POST/PUT/PATCH/DELETE), atributo `name` y mensajes `@error`. | OK | ✅ Cumple (`citas.store`, `notas.store`, `perfil.*`, `pacientes.desactivar`, `horarios.*`, `bloqueos.*`, `doctores.store`, `especialidades.store`, `recepcionistas.store`) |
| **Control de Acceso por Rol** | Los menús y botones visibles corresponden únicamente a los permisos del usuario activo. | OK | ✅ Cumple (sidebar por rol, buttons de acción gateados en citas/detalle, dashboard) |
| **Integración de API** | Los endpoints de disponibilidad se consumen correctamente desde el frontend. | OK | ✅ Cumple (`/api/obtenerDisponibilidad/...` en `citas/agendar`) |
| **Responsividad Visual** | Correcta adaptación en Desktop (1440px+), Tablet (768px–1024px) y Móvil (<768px). | OK | ✅ Cumple (sidebar colapsa, grid responsivo, `md:`/`lg:` breakpoints) |
| **Coherencia de Tokens de Diseño** | Las clases utilizadas corresponden a tokens definidos; sin hexes sueltos ni tokens inexistentes. | OK | ✅/⚠️ `doctor/agenda` y posible `pdf` requieren del fixes (Sección 7) |
| **Consistencia de Paleta entre Layouts** | `layouts/app` y `layouts/auth` definen el mismo conjunto de tokens. | OK | ⚠️ `auth` tiene paleta reducida (Sección 7) |
| **Navegación sin Rupturas** | Todos los enlaces e iconos redireccionan a rutas nombradas de Laravel. | OK | ✅ Cumple |

---

## 7. Pendientes y Correcciones Detectadas (Backlog abierto)

> Resultado de la auditoría de mayo al estado real del código frente al plan. Ordenadas por prioridad.

### P1 — Tokens indefinidos en `doctor/agenda.blade.php` 🐛
Las clases `bg-tertiary-fixed` (líneas ~61, 75) y `bg-surface-container-high` (línea ~62) **no existen** en la paleta de `layouts/app.blade.php`. Tailwind las ignora en silencio → los dots/badges de citas `confirmada`/`agendada` y el estado por defecto quedan **sin fondo**.
**Fixes propuestos (uno de los dos):**
- **Opción A (sin tocar layout):** sustituir por tokens existentes: `bg-tertiary-fixed-dim/30` → para text `text-tertiary`, y `bg-background` para el `default`.
- **Opción B (recomendada):** añadir `tertiary-fixed` y `surface-container-high` al bloque `theme.extend.colors` triangular de `layouts/app.blade.php`, de forma que el esquema siga al `DESIGN.md`.

### 2 — `pacientes/perfil.blade.php` no usa pestañas 🧭
El plan (Fase 4) prevé pestañas *Información Personal* / *Historial de Citas* / *Diagnósticos*. La vista actual muestra toda la información en una sola columna. Todo el contenido ya existe y es funcional; **solo falta agrupar la distribución en pestañas (tab con `@vite`/JS puro)**. Es un refactor visual, no funcional.

### 3 — `reportes/pdf.blade.php` tiene diseño pre-rediseño (por diseño pero inconsistente) ⚠️
Es la plantilla de **exportación a PDF**: no usa `@extends` ni Tailwind (HTML standalone para impresión) ni forklift con la paleta (`#2a9d8f`, `#ddd`, `sans-serif`). Decision a validar: **¿aplicar identidad *Clinical Clarity* (primary/danger/surface) conservando print-safe plain CSS**, o dejar tal cual. La recomendación es aplicar al menos los colores de marca en encabezados/bordes.

### 4 — Discrepancia de tokens en `layouts/auth.blade.php` 🎨
Define un subconjunto menor que `app.blade.php` (pierde `container-dark`, etc.). No afecta a las vistas actuales, pero conviene homogeneizar ambos layouts para mantener un único esquema mutable.

---

*Documento generado para el proyecto Gestor de Citas Médicas. Última auditoría de código reflejada en el documento.*