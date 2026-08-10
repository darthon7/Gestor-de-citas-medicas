# reeedmee — Sistema de Gestión de Citas Médicas (Backend Web)

> Documentación técnica del backend **Laravel** (`sistema-de-gestion-de-citas-medicas`): qué contiene el proyecto y cómo funciona. Este documento cubre únicamente la parte web/API; la app móvil Android (`Movil-citasmedicas`) no se documenta aquí.

---

## 1. Visión general

El sistema gestiona el ciclo completo de una cita médica: registro de pacientes, configuración de doctores y horarios, agendamiento de citas, control del flujo de consulta y reportes.

El backend Laravel expone **dos frentes** sobre una misma base de datos y lógica:

| Frente | Público | Mecanismo | Formato |
|---|---|---|---|
| **Panel Web (SSR)** | Personal clínico (admin, recepcionista, doctor) | Vistas Blade renderizadas en servidor, formularios normales | HTML |
| **API REST** | La app móvil Android y cualquier cliente JSON | Tokens Bearer de **Laravel Sanctum** | JSON `{ mensaje, data }` |

**Stack:**

- Laravel `^13.8`, PHP `^8.3`
- Base de datos **SQLite** (`database/database.sqlite`)
- Sanctum `^4.3` (tokens API)
- `barryvdh/laravel-dompdf` `^3.1` (reportes PDF)
- Frontend: Blade + **Bootstrap 5.3** (CDN) + CSS propio con tokens + iconos **Lucide** + fuente **Inter**

---

## 2. Requisitos e instalación

Requisitos:

- PHP `^8.3`
- Composer 2
- Node.js + npm (solo si se quiere compilar CSS/JS con Vite; el proyecto funciona con CSS estático copiado a `public/css`)

Pasos desde `sistema-de-gestion-de-citas-medicas/`:

```bash
# 1. Dependencias de PHP
composer install

# 2. Configuración
cp .env.example .env          # Windows: Copy-Item .env.example .env
php artisan key:generate

# 3. Base de datos (SQLite vacío desde database/database.sqlite)
php artisan migrate --seed

# 4. Servir
php artisan serve
# → http://127.0.0.1:8000
```

Frontend (opcional si no hay `public/build/manifest.json`): el layout cae a los CSS estáticos de `public/css/*` (ya sincronizados con `resources/css/*`). Para compilar con Vite:

```bash
npm install
npm run build        # build de producción (genera public/build)
```

> Nota: El repositorio mantiene dos copias del CSS de la landing (`resources/css/pages/landing.css` y `public/css/pages/landing.css`); al modificar una hay que sincronizar la otra.

---

## 3. Credenciales de prueba (seeders)

El `DatabaseSeeder` ejecuta en orden: `AdminUserSeeder`, `EspecialidadesSeeder`, `VerificacionesCedulaSeeder`, `UsuariosPruebaSeeder`.

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@citasmedicas.com` | `Admin1234!` |
| Doctor (validado, Medicina General, Lun-Vie 09:00-17:00) | `dr.alejandro.vega@doctor.com` | `Doctor1234!` |
| Recepcionista | `sofia.morales@recepcion.com` | `Recep1234!` |
| Paciente 1 | `maria.gonzalez@paciente.com` | `Paciente1234!` |
| Paciente 2 | `carlos.ramirez@paciente.com` | `Paciente1234!` |
| Paciente 3 | `luisa.hernandez@paciente.com` | `Paciente1234!` |

Además se siembran: **15 especialidades**, **6 cédulas mock** para validación (`verificaciones_cedula`, 9999999 = inválida) y el doctor de prueba con su horario semanal.

---

## 4. Roles y control de acceso

Cada `Usuario` tiene un `rol` (enum en `usuarios.rol`):

| Rol | Acceso web | Áreas |
|---|---|---|
| `admin` | Sí | Pacientes, Doctores y horarios, Especialidades, Recepcionistas, Citas, Reportes |
| `recepcionista` | Sí | Pacientes, Citas |
| `doctor` | Sí | Su agenda (`/mi-agenda`), diagnóstico/notas de cita |
| `paciente` | No (usa la app móvil/API) | API: agendar, mis citas, historial |

Control en dos capas (alias definidos en `bootstrap/app.php`):

- **`role:admin,recepcionista`** (`RoleMiddleware`): comparar el `rol` con la lista permitida. Respuesta 401 sin sesión / 403 sin permisos.
- **`check.status`** (`CheckAccountStatus`): verifica el `estado` de la cuenta (ver §5).

---

## 5. Autenticación y seguridad de cuenta

| Aspecto | Detalle |
|---|---|
| **Web** | Sesión PHP clásica (`Auth::login`, `session()->regenerate()`). Revisa `AuthWebController`. |
| **API** | Tokens **Sanctum** (`HasApiTokens` en modelo `Usuario`, modo stateful+token). `AuthController` delega en `AuthRepository`. |
| **Modelo autenticable** | `App\Models\Usuario` (configurado en `config/auth.php`), no el `User` por defecto. |
| **Recuperación de contraseña** | Flujo de **código de 6 dígitos**: `solicitarRecuperacion` guarda el código en `password_resets` (caduca a los 30 min) y envía `CodigoRecuperacionMail` (mailer `log`). Rutas: `/recuperar-password`, `/verificar-codigo`, `/restablecer-password`. |
| **Anti fuerza bruta** | 5 intentos fallidos → `estado=bloqueado` + `bloqueado_hasta` (+15 min) + registro en `intentos_login`. El middleware `check.status` desbloquea automáticamente al expirar. |
| **Médicos** | No pueden iniciar sesión si `estado_validacion != validado`. |

---

## 6. Base de datos (resumen)

16 migraciones y las tablas principales:

| Tabla | Propósito |
|---|---|
| `usuarios` | Usuario raíz: nombre, email, curp, rol, estado, bloqueo, foto, intentos |
| `especialidades` | Catálogo de especialidades (activa/inactiva) |
| `perfiles_doctor` | Datos médicos del doctor (cédulas, estado de validación) + pivot `doctor_especialidad` |
| `perfiles_paciente` | Expediente: número de expediente, NSS, contacto de emergencia, etc. |
| `perfiles_recepcionista` | Datos del recepcionista (número de empleado, turno) |
| `horarios_doctor` | Disponibilidad semanal (día, inicio, fin, duración de consulta) |
| `bloqueos_horario` | Ausencias/bloqueos puntuales de un doctor con motivo |
| `citas` | Cita médica: paciente, doctor, especialidad, código `CITA-XXXXXX`, horario, estado, check-in, cancelación |
| `notas_consulta` | Diagnóstico/tratamiento de una cita (una por cita) |
| `registros_auditoria` | Auditoría (creada, ver §11 estado) |
| `verificaciones_cedula` | Mock del "sistema SEP" de validación de cédulas |
| `intentos_login` | Registro de intentos de acceso |
| `personal_access_tokens`, `password_resets`, `sessions` | Soporte Sanctum, recuperación y sesión |

**Estados de cita (enum `citas.estado`):** `agendada` → `confirmada` (check-in) → `en_consulta` → `completada` | `cancelada`.

---

## 7. Arquitectura por capas

Patrón general: **Rutas → Controllers (delgados) → Repositories (lógica) → Eloquent Models**.

```
app/
  Http/
    Controllers/           # API JSON (sin subcarpeta)
    Controllers/Web/       # SSR Blade
    Middleware/            # RoleMiddleware, CheckAccountStatus
    Repository/            # 12 repositorios con la lógica de negocio
    Requests/              # 16 Form Requests (validación; errores → JSON 422)
  Mail/CodigoRecuperacionMail.php
  Models/                  # 15 modelos Eloquent
routes/
  web.php                  # 24 rutas SSR
  api.php                  # 45 rutas JSON
database/migrations + seeders
resources/views            # Blade (layouts, auth, módulos)
resources/css              # variables.css, components.css, pages/*
```

### Controladores Web (`app/Http/Controllers/Web/`)

| Controlador | Responsabilidad |
|---|---|
| `LandingWebController` | Landing pública + raíz `/` (landing para visitantes, dashboard para autenticados) |
| `AuthWebController` | Login, registro, recuperación por código, logout (sesión) |
| `DashboardWebController` | Resumen del día: totales por estado + próximas 5 citas |
| `PacientesWebController` | CRUD pacientes, búsqueda, desactivar |
| `CitasWebController` | Calendario semanal, agendar/reprogramar/cancelar/check-in, detalle |
| `DoctoresWebController` | CRUD doctores + horarios semanales + bloqueos |
| `DoctorWebController` | Agenda del doctor, iniciar/completar consulta, registrar nota |
| `RecepcionistasWebController` | Lista y alta de recepcionistas |
| `EspecialidadesWebController` | Catálogo de especialidades |
| `PerfilWebController` | Ver/editar perfil, cambiar contraseña, foto |
| `ReportesWebController` | Reportes e indicadores + exportación PDF (web) |

### Repositorios (`app/Http/Repository/`)

Devolución constante en `['mensaje' => ..., 'data' => ...]`:

| Repositorio | Función |
|---|---|
| `AuthRepository` | login (con bloqueo), recuperación por código, registro paciente/medico/recepcionista, logout |
| `CitasRepository` | CRUD citas, flujo (check-in, consulta, completar), cancelación con motivo |
| `DisponibilidadRepository` | Cálculo de slots libres según horario − citas − bloqueos |
| `DoctoresRepository` | CRUD + validación de cédula del doctor |
| `PacientesRepository` | CRUD + histórico clínico + búsqueda (nombre/CURP/expediente) |
| `EspecialidadesRepository` | Catálogo |
| `HorariosRepository` | CRUD + detección de solapamientos |
| `BloqueosRepository` | CRUD + alerta si hay citas afectadas |
| `NotasConsultaRepository` | Registrar/leer notas |
| `ReportesRepository` | Reportes de citas, doctores, especialidades, pacientes, resumen diario |
| `UsuariosRepository` | Perfil, cambiar contraseña, foto |
| `VerificacionCedulaRepository` | Validación mock de cédula |

### Form Requests (validación)

`StoreLoginRequest`, `StoreCitaRequest`, `StorePacienteRequest`, `StoreDoctorRequest`, `StoreHorarioRequest`, `StoreBloqueoRequest`, `StoreNotaConsultaRequest`, `StoreRecuperacionRequest`, etc. Todos reescribir `failedValidation` para responder JSON 422 (aunque también se usan en web). Regla CURP con regex (18, mayúsculas).

---

## 8. Flujo de una cita (cómo funciona)

1. **Configuración**: el admin registra al doctor (valida cédula vs `verificaciones_cedula`) y le define `horarios_doctor` (días + horas + duración, típicamente 30 min).
2. **Disponibilidad**: `DisponibilidadController` / `DisponibilidadRepository` genera los slots candidatos a partir del horario del doctor, descartando citas ocupadas y `bloqueos_horario` (parciales o totales).
3. **Agendamiento**: se valida que el slot esté libre y se crea `citas` con `codigo_referencia = CITA-XXXXXX`. En la web lo hace la recepción/administrador; en la app, el propio paciente (`agendarCita` evita duplicado por doctor+mismo día).
4. **Check-in**: al llegar el paciente al centro → `estado = confirmada` (registra `checkin_por`/`checkin_en`).
5. **Consulta**: el doctor inicia la consulta (`en_consulta`) y registra la `nota_consulta` (diagnóstico, tratamiento) → `completada`.
6. **Cancelación/reprogramación**: con motivo y usuario; el paciente solo puede cancelar **≥ 2 h antes**; el staff sin restricción extra.
7. **Reportes**: agregados por estado, doctor, especialidad y paciente; exportación **PDF** via DomPDF.

---

## 9. Rutas

### Web SSR (`routes/web.php`)

- **guest**: `GET/POST /login`, `GET/POST /registro`, todos los pasos de recuperación (`/recuperar-password`, `/verificar-codigo`, `/restablecer-password`).
- **auth + check.status**: `POST /logout`, `GET/PUT /mi-perfil`, `POST /cambiar-password`, `POST /actualizar-foto`.
- **auth + check.status + role(admin,recepcionista)**: CRUD `/pacientes`, `/citas`, `/especialidades`, `GET/POST /recepcionistas`, `/reportes`, `GET /reportes/exportar/{tipo}`.
- **auth + role(admin)**: `/doctores` (+ `/horarios`, `/bloqueos`).
- **auth + role(doctor)**: `GET /mi-agenda`, `GET /diagnostico/{citaId}`, `PATCH /citas/{id}/iniciar|completar`, `POST /citas/{citaId}/nota`.

**API (`routes/api.php`, JSON, guard Sanctum)**, agrupada por rol:

- **Público**: login, registro (paciente/medico), recuperación, `obtenerEspecialidades`, `obtenerDoctores`, `obtenerDisponibilidad/{doctorId}`.
- **auth**: `miPerfil`, `cambiarPassword`, `actualizarFoto`, `cerrarSesion`.
- **+ role:paciente**: `misCitas`, `agendarCita`, `miCita`, `cancelarMiCita`, `miHistorial`, `miConsulta`.
- **+ role:doctor**: `registrarNota`, `obtenerNotas`, `iniciarConsulta`, `completarCita`.
- **+ role:admin,recepcionista**: CRUD de pacientes y citas, check-in, reportes y resumen diario.
- **+ role:admin**: `registrarRecepcionista`, CRUD doctores/especialidades, horarios, bloqueos, `exportarReporte`.

---

## 10. Frontend web (SSR)

- **Motor**: Blade; layouts `layouts/app.blade.php` (sidebar + flash-messages) y `layouts/auth.blade.php`.
- **Estilos**: CSS propio en `resources/css/`: `variables.css` (tokens: `--color-primary: #1B6B93`, radios, sombras), `components.css`, `bootstrap-theme.css`, `pages/{citas,doctores,landing}.css`. Bootstrap 5.3 y Lucide vía CDN.
- **Vistas**: `landing`, `dashboard/index`, `auth/*`, `citas/*` (calendario semanal, agendar, detalle), `doctores/*`, `pacientes/*`, `especialidades`, `recepcionistas`, `perfil`, `doctor/agenda` y `doctor/diagnostico`, `reportes/*`.
- **Assets**: se sirve `public/css/*` (estático) o el bundle de Vite si existe `public/build/manifest.json`.

---

## 11. Tests

Suites `Feature` (`AuthTest`, `CitasTest`, `SistemaIntegralTest`, `SegundoPacienteTest`, `UsuariosPruebaTest`) + `Unit`, cubren flujos API: login/bloqueo, recuperación completa, registro con validación de cédula y escenarios integrales (crear especialidad → doctor → horario → cita → check-in → consulta).

Ejecución:

```bash
php artisan test
```

---

## 12. Estado conocido / limitaciones

1. **Export web de citas** — `ReportesWebController@exportar` (ruta `reportes.exportar`) pasa a la vista `reportes.pdf` un arreglo con el resumen del reporte (total, estados y `citas`), pero la vista itera `$cita['fecha_hora']`/`['paciente']` por ítem de cita. La estructura no coincide → riesgo de error al exportar.
2. **Export API de reportes** — `ReportesController@exportarReporte` carga vistas `reportes.{citas|doctores|especialidades|pacientes}`, pero solo existen `reportes.pdf` e `reportes.index` → vistas PDF inexistentes.
3. **Auditoría sin uso** — existe la tabla `registros_auditoria` pero no hay código que la escriba.
4. **Modelo `User` huérfano** — el modelo `User` (tabla `users`) y su `UserFactory` no tienen migración ni uso real (heredados del skeleton); el autenticable real es `Usuario`.
5. **Exportación Excel removida** — la dependencia `maatwebsite/excel` fue eliminada; la exportación queda solo en PDF.

---

## 13. Estructura de carpetas (árbol resumido)

```
sistema-de-gestion-de-citas-medicas/
├─ app/
│  ├─ Http/
│  │  ├─ Controllers/           # API (JSON): Auth, Citas, Doctores, ...
│  │  ├─ Controllers/Web/       # SSR Blade
│  │  ├─ Middleware/            # RoleMiddleware, CheckAccountStatus
│  │  ├─ Repository/            # Lógica de negocio (12)
│  │  └─ Requests/              # FormRequest (validación)
│  ├─ Mail/CodigoRecuperacionMail.php
│  └─ Models/
├─ database/
│  ├─ migrations/               # 16 tablas
│  └─ seeders/                  # 5 seeders
├─ routes/ (web.php, api.php, console.php)
├─ resources/views (+ css/ + js/)
├─ public/css + public/assets  # CSS estático servido
├─ config/ (auth.php, sanctum.php, ...)
└─ bootstrap/app.php           # middleware alias, statefulApi, JSON api/*
```

---

*Documento generado desde el repositorio `Gestor-de-citas-medicas` — revisa que sea consistente con el código.*