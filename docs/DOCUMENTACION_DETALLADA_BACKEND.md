# Documentación Técnica Detallada del Backend
## Sistema de Gestión de Citas Médicas (Laravel 11 REST API)

---

## 📋 Índice
1. [Visión General del Sistema](#1-visión-general-del-sistema)
2. [Arquitectura y Patrón de Diseño (Proyectu3 Style)](#2-arquitectura-y-patrón-de-diseño-proyectu3-style)
3. [Estructura del Proyecto](#3-estructura-del-proyecto)
4. [Base de Datos y Diagrama de Entidades](#4-base-de-datos-y-diagrama-de-entidades)
   - [Detalle de Tablas y Campos](#detalle-de-tablas-y-campos)
5. [Modelos Eloquent (`App\Models`)](#5-modelos-eloquent-appmodels)
6. [Capa de Repositorios (`App\Http\Repository`)](#6-capa-de-repositorios-apphttprepository)
7. [Capa de Form Requests / Validaciones (`App\Http\Requests`)](#7-capa-de-form-requests--validaciones-apphttprequests)
8. [Capa de Controladores (`App\Http\Controllers`)](#8-capa-de-controladores-apphttpcontrollers)
9. [Middlewares y Seguridad](#9-middlewares-y-seguridad)
10. [Catálogo Completo de Endpoints API (`routes/api.php`)](#10-catálogo-completo-de-endpoints-api-routesapiphp)
11. [Reglas de Negocio Implementadas](#11-reglas-de-negocio-implementadas)
12. [Ejecución, Seeders y Pruebas Automatizadas](#12-ejecución-seeders-y-pruebas-automatizadas)

---

## 1. Visión General del Sistema

El backend del **Sistema de Gestión de Citas Médicas** está desarrollado en **Laravel 11** utilizando **MySQL** como motor de base de datos relacional. 

Su propósito central es servir como la API REST unificada que atiende tanto al **panel web administrativo** (destinado a Administradores, Recepcionistas y Médicos) como a la **aplicación móvil Android** (destinada a los Pacientes).

### Roles Soportados:
1. **Administrador (`admin`)**: Acceso global al sistema, gestión de usuarios, recepción de médicos, validación de cédulas, configuración de horarios y bloqueos, generación de reportes y exportaciones.
2. **Recepcionista (`recepcionista`)**: Registro y administración de pacientes, agendamiento de citas, check-in el día de la consulta, reprogramación y cancelación.
3. **Médico (`doctor`)**: Consulta de agenda propia, inicio de consultas clínicas, registro de diagnósticos y tratamientos (notas de consulta).
4. **Paciente (`paciente`)**: Consulta de especialidades y doctores disponibles, consulta de slots en tiempo real, agendamiento de citas propias, cancelación (mínimo 2h de anticipación), consulta de historial clínico.

---

## 2. Arquitectura y Patrón de Diseño (Proyectu3 Style)

El proyecto sigue estrictamente el patrón desacoplado **Repository-Controller-Request** normado en la skill `proyectu3-architecture`:

```
[ Petición HTTP / Cliente REST (Web / App Móvil) ]
                       │
                       ▼
[ Rutas API (routes/api.php) + Middlewares (Sanctum, Role, CheckAccountStatus) ]
                       │
                       ▼
[ Form Request (Validación de datos de entrada) ]
       │                                   │
   (Falla)                              (Pasa)
       ▼                                   ▼
[ Respuesta JSON 422 ]               [ Controller (Inyección de Dependencia) ]
                                           │
                                           ▼
                                    [ Repository (Lógica de DB + Eloquent) ]
                                           │
                                           ▼
                                    [ Model Eloquent <──> MySQL DB ]
```

### Reglas Inviolables Cumplidas:
- **Cero Eloquent en Controladores**: Ningún controlador invoca directamente métodos de Eloquent (`::all()`, `::create()`, `::find()`). Toda la interacción con la base de datos se realiza a través de su Repositorio correspondiente.
- **Nombres en Español**: Los métodos de los repositorios utilizan semántica expresada en español (`obtener...`, `registrar...`, `actualizar...`, `eliminar...`, `cancelar...`, `validar...`).
- **Manejo de Excepciones Integrado**: Todos los métodos de los controladores y repositorios están envueltos en bloques `try { ... } catch (\Exception $e) { ... }`.
- **Estructura Estándar de Respuestas JSON**:
  - Respuestas exitosas: HTTP 200 con JSON conteniendo campo `"mensaje"` y opcionalmente `"data"` / `"token"`.
  - Errores de validación: HTTP 422 con formato `{"msj": "Error de validacion", "errors": { ... }}`.
  - Excepciones no controladas: HTTP 500 con formato `{"mensaje": $e->getMessage()}`.

---

## 3. Estructura del Proyecto

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── BloqueosController.php
│   │   │   ├── CitasController.php
│   │   │   ├── DisponibilidadController.php
│   │   │   ├── DoctoresController.php
│   │   │   ├── EspecialidadesController.php
│   │   │   ├── HorariosController.php
│   │   │   ├── NotasConsultaController.php
│   │   │   ├── PacientesController.php
│   │   │   ├── PerfilController.php
│   │   │   └── ReportesController.php
│   │   ├── Middleware/
│   │   │   ├── CheckAccountStatus.php
│   │   │   └── RoleMiddleware.php
│   │   ├── Repository/
│   │   │   ├── AuthRepository.php
│   │   │   ├── BloqueosRepository.php
│   │   │   ├── CitasRepository.php
│   │   │   ├── DisponibilidadRepository.php
│   │   │   ├── DoctoresRepository.php
│   │   │   ├── EspecialidadesRepository.php
│   │   │   ├── HorariosRepository.php
│   │   │   ├── NotasConsultaRepository.php
│   │   │   ├── PacientesRepository.php
│   │   │   ├── ReportesRepository.php
│   │   │   ├── UsuariosRepository.php
│   │   │   └── VerificacionCedulaRepository.php
│   │   └── Requests/
│   │       ├── CancelacionCitaRequest.php
│   │       ├── StoreBloqueoRequest.php
│   │       ├── StoreCitaRequest.php
│   │       ├── StoreDoctorRequest.php
│   │       ├── StoreHorarioRequest.php
│   │       ├── StoreLoginRequest.php
│   │       ├── StoreNotaConsultaRequest.php
│   │       ├── StorePacienteRequest.php
│   │       ├── StoreRegistroMedicoRequest.php
│   │       ├── StoreRegistroPacienteRequest.php
│   │       ├── StoreRegistroRecepcionistaRequest.php
│   │       ├── UpdateCitaRequest.php
│   │       └── UpdatePacienteRequest.php
│   └── Models/
│       ├── BloqueoHorario.php
│       ├── Cita.php
│       ├── Especialidad.php
│       ├── HorarioDoctor.php
│       ├── IntentoLogin.php
│       ├── NotaConsulta.php
│       ├── PerfilDoctor.php
│       ├── PerfilPaciente.php
│       ├── PerfilRecepcionista.php
│       ├── RegistroAuditoria.php
│       ├── Usuario.php
│       └── VerificacionCedula.php
├── bootstrap/
│   └── app.php
├── database/
│   ├── migrations/
│   │   ├── 2026_01_01_000001_crear_tabla_usuarios.php
│   │   ├── 2026_01_01_000002_crear_tabla_especialidades.php
│   │   ├── 2026_01_01_000003_crear_tabla_perfiles_doctor.php
│   │   ├── 2026_01_01_000004_crear_tabla_perfiles_paciente.php
│   │   ├── 2026_01_01_000005_crear_tabla_perfiles_recepcionista.php
│   │   ├── 2026_01_01_000006_crear_tabla_doctor_especialidad.php
│   │   ├── 2026_01_01_000007_crear_tabla_horarios_doctor.php
│   │   ├── 2026_01_01_000008_crear_tabla_bloqueos_horario.php
│   │   ├── 2026_01_01_000009_crear_tabla_citas.php
│   │   ├── 2026_01_01_000010_crear_tabla_notas_consulta.php
│   │   ├── 2026_01_01_000011_crear_tabla_registros_auditoria.php
│   │   ├── 2026_01_01_000012_crear_tabla_verificaciones_cedula.php
│   │   └── 2026_01_01_000013_crear_tabla_intentos_login.php
│   └── seeders/
│       ├── AdminUserSeeder.php
│       ├── DatabaseSeeder.php
│       ├── EspecialidadesSeeder.php
│       └── VerificacionesCedulaSeeder.php
├── docs/
│   └── DOCUMENTACION_DETALLADA_BACKEND.md
├── routes/
│   └── api.php
└── tests/
    └── Feature/
        ├── AuthTest.php
        └── CitasTest.php
```

---

## 4. Base de Datos y Diagrama de Entidades

La base de datos relacional se denomina `citas_medicas` y contiene 13 tablas custom más la tabla de Sanctum `personal_access_tokens`.

```
                    ┌────────────────────────┐
                    │        usuarios        │
                    └───────────┬────────────┘
                                │ 1:1
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
┌────────────────┐     ┌────────────────┐     ┌─────────────────────┐
│perfiles_doctor │     │perfiles_paciente│    │perfiles_recepcionista│
└───────┬────────┘     └───────┬────────┘     └─────────────────────┘
        │ 1:N                  │ 1:N
        ├──────────────────────┼──────────────────────┐
        ▼                      ▼                      ▼
┌────────────────┐     ┌────────────────┐     ┌─────────────────────┐
│horarios_doctor │     │     citas      │     │  bloqueos_horario   │
└────────────────┘     └───────┬────────┘     └─────────────────────┘
                               │ 1:1
                               ▼
                       ┌────────────────┐
                       │ notas_consulta │
                       └────────────────┘
```

---

### Detalle de Tablas y Campos

#### 1. Tabla `usuarios`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador único del usuario |
| `nombre` | VARCHAR(255) | No | Nombre completo |
| `email` | VARCHAR(255) (UNIQUE) | No | Correo electrónico de acceso |
| `password` | VARCHAR(255) | No | Contraseña hasheada (BCrypt) |
| `curp` | VARCHAR(18) (UNIQUE) | Sí | Clave Única de Registro de Población |
| `telefono` | VARCHAR(20) | Sí | Teléfono de contacto |
| `rol` | ENUM('admin', 'doctor', 'recepcionista', 'paciente') | No | Rol del usuario en el sistema |
| `estado` | ENUM('activo', 'inactivo', 'bloqueado') | No | Estado de la cuenta (Default: 'activo') |
| `foto_perfil` | VARCHAR(255) | Sí | Ruta de la foto de perfil en storage |
| `intentos_fallidos` | INT | No | Contador de intentos de login erróneos (Default: 0) |
| `bloqueado_hasta` | TIMESTAMP | Sí | Marca de tiempo hasta la cual la cuenta está bloqueada |
| `timestamps` | TIMESTAMP | No | `created_at` y `updated_at` |

#### 2. Tabla `especialidades`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador único |
| `nombre` | VARCHAR(255) (UNIQUE) | No | Nombre de la especialidad médica |
| `descripcion` | TEXT | Sí | Descripción o detalles |
| `activa` | BOOLEAN | No | Estado activo/inactivo (Default: true) |
| `timestamps` | TIMESTAMP | No | `created_at` y `updated_at` |

#### 3. Tabla `perfiles_doctor`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador único del perfil médico |
| `usuario_id` | BIGINT UNSIGNED (FK) | No | Referencia a `usuarios.id` (ON DELETE CASCADE) |
| `cedula_profesional` | VARCHAR(255) (UNIQUE) | No | Cédula profesional emitida por la SEP |
| `cedula_especialidad` | VARCHAR(255) | Sí | Cédula adicional de especialidad |
| `estado_validacion` | ENUM('pendiente', 'validado', 'rechazado') | No | Estado de validación por admin |
| `notas_validacion` | TEXT | Sí | Observaciones del administrador |
| `validado_por` | BIGINT UNSIGNED (FK) | Sí | Admin que validó la cuenta |
| `validado_en` | TIMESTAMP | Sí | Fecha y hora de la validación |
| `timestamps` | TIMESTAMP | No | `created_at` y `updated_at` |

#### 4. Tabla `perfiles_paciente`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador único del perfil |
| `usuario_id` | BIGINT UNSIGNED (FK) | No | Referencia a `usuarios.id` (ON DELETE CASCADE) |
| `numero_expediente` | VARCHAR(255) (UNIQUE) | No | Expediente clínico único (`EXP-YYYYMMDD-XXXX`) |
| `fecha_nacimiento` | DATE | Sí | Fecha de nacimiento |
| `sexo` | ENUM('M', 'F') | Sí | Sexo biológico |
| `direccion` | TEXT | Sí | Domicilio |
| `contacto_emergencia_nombre` | VARCHAR(255) | Sí | Nombre de contacto de emergencia |
| `contacto_emergencia_telefono` | VARCHAR(20) | Sí | Teléfono de emergencia |
| `nss` | VARCHAR(20) | Sí | Número de Seguridad Social |
| `timestamps` | TIMESTAMP | No | `created_at` y `updated_at` |

#### 5. Tabla `perfiles_recepcionista`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador único |
| `usuario_id` | BIGINT UNSIGNED (FK) | No | Referencia a `usuarios.id` |
| `numero_empleado` | VARCHAR(255) | Sí | Código de empleado interno |
| `unidad_asignada` | VARCHAR(255) | Sí | Clínica o sede asignada |
| `turno` | VARCHAR(50) | Sí | Turno de trabajo (Matutino/Vespertino) |
| `creado_por_admin_id` | BIGINT UNSIGNED (FK) | Sí | Admin que dió de alta la cuenta |

#### 6. Tabla `doctor_especialidad` (Pivote)
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `perfil_doctor_id` | BIGINT UNSIGNED (FK) | No | Referencia a `perfiles_doctor.id` |
| `especialidad_id` | BIGINT UNSIGNED (FK) | No | Referencia a `especialidades.id` |
| *(PK Compuesta)* | `(perfil_doctor_id, especialidad_id)` | — | Garantiza no duplicar especialidad |

#### 7. Tabla `horarios_doctor`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador del bloque de horario |
| `perfil_doctor_id` | BIGINT UNSIGNED (FK) | No | Referencia al doctor |
| `dia_semana` | ENUM('lunes','martes','miercoles','jueves','viernes','sabado','domingo') | No | Día laboral |
| `hora_inicio` | TIME | No | Hora de inicio de consulta |
| `hora_fin` | TIME | No | Hora de término de consulta |
| `duracion_consulta_minutos` | INT | No | Duración por cita en minutos (Default: 30) |
| `activo` | BOOLEAN | No | Si el horario está vigente (Default: true) |

#### 8. Tabla `bloqueos_horario`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador del bloqueo |
| `perfil_doctor_id` | BIGINT UNSIGNED (FK) | No | Referencia al doctor |
| `fecha_bloqueo` | DATE | No | Fecha bloqueada |
| `hora_inicio_bloqueo` | TIME | Sí | Hora inicio (si es parcial) |
| `hora_fin_bloqueo` | TIME | Sí | Hora fin (si es parcial) |
| `motivo` | VARCHAR(255) | Sí | Motivo (Vacaciones, Conferencia, Ausencia) |
| `creado_por` | BIGINT UNSIGNED (FK) | Sí | Usuario que registró el bloqueo |

#### 9. Tabla `citas`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador único de la cita |
| `perfil_paciente_id` | BIGINT UNSIGNED (FK) | No | Paciente agendado |
| `perfil_doctor_id` | BIGINT UNSIGNED (FK) | No | Médico asignado |
| `especialidad_id` | BIGINT UNSIGNED (FK) | No | Especialidad médica |
| `codigo_referencia` | VARCHAR(255) (UNIQUE) | No | Código único de cita (`CITA-XXXXXX`) |
| `fecha_cita` | DATE | No | Fecha agendada |
| `hora_cita` | TIME | No | Hora agendada |
| `duracion_minutos` | INT | No | Duración estimada |
| `estado` | ENUM('agendada','confirmada','en_consulta','completada','cancelada') | No | Estado actual de la cita |
| `motivo_cancelacion` | TEXT | Sí | Motivo si la cita fue cancelada |
| `cancelado_por` | BIGINT UNSIGNED (FK) | Sí | Usuario que canceló |
| `cancelado_en` | TIMESTAMP | Sí | Momento de la cancelación |
| `checkin_en` | TIMESTAMP | Sí | Marca de tiempo de recepción (Check-in) |
| `checkin_por` | BIGINT UNSIGNED (FK) | Sí | Recepcionista/Admin que registró el check-in |

#### 10. Tabla `notas_consulta`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador |
| `cita_id` | BIGINT UNSIGNED (FK) | No | Cita a la que pertenece la nota |
| `diagnostico` | TEXT | No | Diagnóstico clínico formulado por el doctor |
| `tratamiento` | TEXT | No | Prescripción / Indicaciones médicas |
| `notas_adicionales` | TEXT | Sí | Observaciones adicionales |
| `creado_por` | BIGINT UNSIGNED (FK) | Sí | Usuario médico autor |

#### 11. Tabla `verificaciones_cedula` (Mock SEP)
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador |
| `numero_cedula` | VARCHAR(255) (UNIQUE) | No | Número de cédula profesional |
| `nombre_titular` | VARCHAR(255) | No | Nombre del profesional registrado |
| `profesion` | VARCHAR(255) | No | Carrera profesional registrada |
| `institucion` | VARCHAR(255) | Sí | Universidad o institución emisora |
| `es_valida` | BOOLEAN | No | Si la cédula es válida (Default: true) |

#### 12. Tabla `intentos_login`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador |
| `email` | VARCHAR(255) | No | Correo intentado |
| `direccion_ip` | VARCHAR(45) | Sí | IP del cliente |
| `exitoso` | BOOLEAN | No | Resultado del intento |

#### 13. Tabla `registros_auditoria`
| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED (PK) | No | Identificador |
| `usuario_id` | BIGINT UNSIGNED (FK) | Sí | Usuario autor de la acción |
| `accion` | VARCHAR(255) | No | Acción realizada |
| `tipo_entidad` | VARCHAR(255) | Sí | Nombre de la entidad |
| `entidad_id` | BIGINT UNSIGNED | Sí | ID del registro afectado |
| `valores_anteriores` | JSON | Sí | Estado previo |
| `valores_nuevos` | JSON | Sí | Estado posterior |

---

## 5. Modelos Eloquent (`App\Models`)

Todos los modelos extienden de `Illuminate\Database\Eloquent\Model` (a excepción de `Usuario` que extiende de `Illuminate\Foundation\Auth\User`) y definen explícitamente:
- `$table` (Nombre en español)
- `$fillable` (Asignación masiva segura)
- `$casts` (Casteo de tipos como contraseñas, fechas y JSON)
- Métodos de relación Eloquent

### Resumen de Modelos:

1. **`Usuario.php`**:
   - Uses: `HasApiTokens`, `HasFactory`.
   - Relaciones: `perfilDoctor()`, `perfilPaciente()`, `perfilRecepcionista()`, `registrosAuditoria()`.
   - Hidden: `password`, `remember_token`.
2. **`PerfilDoctor.php`**:
   - Relaciones: `usuario()`, `especialidades()`, `horarios()`, `bloqueos()`, `citas()`, `validadoPor()`.
3. **`PerfilPaciente.php`**:
   - Relaciones: `usuario()`, `citas()`.
4. **`PerfilRecepcionista.php`**:
   - Relaciones: `usuario()`, `creadoPor()`.
5. **`Especialidad.php`**:
   - Relaciones: `doctores()`, `citas()`.
6. **`HorarioDoctor.php`**:
   - Relaciones: `doctor()`.
7. **`BloqueoHorario.php`**:
   - Relaciones: `doctor()`, `creadoPor()`.
8. **`Cita.php`**:
   - Relaciones: `perfilPaciente()`, `perfilDoctor()`, `especialidad()`, `notaConsulta()`, `canceladoPor()`, `checkinPor()`.
9. **`NotaConsulta.php`**:
   - Relaciones: `cita()`, `creadoPor()`.
10. **`VerificacionCedula.php`**, **`IntentoLogin.php`**, **`RegistroAuditoria.php`**.

---

## 6. Capa de Repositorios (`App\Http\Repository`)

Los repositorios contienen el 100% de la lógica de persistencia y consultas Eloquent.

### 1. `AuthRepository`
- `login(array $credenciales, string $ip)`: Valida credenciales con `Hash::check()`, verifica intentos fallidos y bloquea a los 5 fallos por 15 min. Si el usuario es médico, verifica que `estado_validacion === 'validado'`. Emite token de Sanctum `createToken('auth')->plainTextToken`.
- `registrarPaciente(array $data)`: Crea registro en `usuarios` + `perfiles_paciente` generando número de expediente `EXP-YYYYMMDD-XXXX`. Retorna usuario y token.
- `registrarMedico(array $data)`: Verifica la cédula contra `verificaciones_cedula` (SEP Mock). Si es válida, crea `usuarios` y `perfiles_doctor` en estado `pendiente`. Asigna especialidades en la tabla pivote.
- `registrarRecepcionista(array $data, int $adminId)`: Crea cuenta de recepcionista asociada al ID del administrador.
- `cerrarSesion(Usuario $usuario)`: Revoca el token actual (`$usuario->currentAccessToken()->delete()`).

### 2. `PacientesRepository`
- `obtenerPacientes(array $filtros)`: Búsqueda paginada por nombre, CURP o expediente.
- `registrarPaciente(array $data)`: Registro administrativo de paciente.
- `obtenerPaciente(int $id)`: Detalle completo de paciente con su historial de citas y notas.
- `actualizarPaciente(int $id, array $data)`: Edita datos personales y de expediente.
- `desactivarPaciente(int $id)`: Verifica que no existan citas activas (`agendada`, `confirmada`, `en_consulta`). Si existen, rechaza la desactivación; de lo contrario cambia `estado = 'inactivo'`.

### 3. `DoctoresRepository`
- `obtenerDoctores(array $filtros)`: Filtra doctores por especialidad, estado de validación o búsqueda por nombre.
- `registrarDoctor(array $data)`: Alta de médico desde el panel administrativo.
- `obtenerDoctor(int $id)`: Obtiene datos del médico, especialidades y horarios.
- `actualizarDoctor(int $id, array $data)`: Actualiza cédulas y especialidades sincronizadas.
- `validarDoctor(int $id, array $data, int $adminId)`: Aprueba (`validado`) o rechaza (`rechazado`) a un médico. Si es rechazado, desactiva la cuenta de usuario.

### 4. `DisponibilidadRepository`
- `obtenerSlotsDisponibles(int $doctorId, string $fecha)`:
  1. Identifica el día de la semana y obtiene el `HorarioDoctor` activo.
  2. Obtiene bloqueos registrados en `bloqueos_horario` para esa fecha.
  3. Obtiene citas agendadas en esa fecha.
  4. Genera los slots en intervalos definidos (`duracion_consulta_minutos`, ej: 30 min) y los marca como `disponible: true/false`.
- `verificarDisponibilidad(int $doctorId, string $fecha, string $hora)`: Devuelve `true/false` para validar antes de agendar.

### 5. `CitasRepository`
- `registrarCita(array $data)`: Valida disponibilidad con `DisponibilidadRepository`. Genera código de referencia único (`CITA-XXXXXX`).
- `reprogramarCita(int $id, array $data)`: Verifica disponibilidad en la nueva fecha/hora y actualiza.
- `cancelarCita(int $id, array $data, int $usuarioId)`: Cambia estado a `cancelada` registrando el motivo y el autor.
- `checkInCita(int $id, int $usuarioId)`: Cambia estado a `confirmada` y guarda `checkin_en`.
- `iniciarConsulta(int $id)`: Transiciona la cita a `en_consulta`.
- `completarCita(int $id)`: Transiciona a `completada`.
- `registrarCitaPaciente()`: Para la app móvil. Garantiza que el paciente no tenga ya una cita activa el mismo día con el mismo médico.
- `cancelarCitaPaciente()`: Para la app móvil. Valida que falten al menos 2 horas para la cita antes de permitir la cancelación.

### 6. `NotasConsultaRepository`
- `registrarNota(int $citaId, array $data, int $doctorUsuarioId)`: Registra diagnóstico y tratamiento. Transiciona automáticamente la cita a estado `completada`.

### 7. `HorariosRepository`
- `registrarHorario()` / `actualizarHorario()`: Valida mediante `verificarSolapamiento()` que los rangos de horas no se crucen para el mismo doctor el mismo día.

### 8. `BloqueosRepository`
- `registrarBloqueo()`: Verifica si existen citas ya agendadas en el rango a bloquear. Si existen, registra el bloqueo y emite un mensaje de alerta informativo indicando cuántas citas resultaron afectadas.

### 9. `ReportesRepository`
- Genera consultas consolidadas para los 5 reportes: `reporteCitas`, `reporteDoctores`, `reporteEspecialidades`, `reportePacientes` y `resumenDiario`.

---

## 7. Capa de Form Requests / Validaciones (`App\Http\Requests`)

Todas las clases extienden de `FormRequest`. Sobrescriben el método `failedValidation` para retornar respuestas estandarizadas HTTP 422:

```php
protected function failedValidation(Validator $validator)
{
    throw new HttpResponseException(response()->json([
        "msj" => "Error de validación",
        "errors" => $validator->errors()
    ], 422));
}
```

### Principales Form Requests:
- `StoreLoginRequest`: email requerido con formato válido, password requerido.
- `StoreRegistroPacienteRequest`: CURP con expresión regular mexicana `^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$`, email único, contraseña mínima de 8 caracteres con confirmación.
- `StoreRegistroMedicoRequest`: Cédula profesional única, CURP, especialidades válidas.
- `StoreCitaRequest`: Fecha no pasada (`after_or_equal:today`), hora `HH:MM:SS`, existencia de paciente, doctor y especialidad.
- `StoreHorarioRequest`: Hora fin posterior a hora inicio (`after:hora_inicio`), días válidos de la semana.
- `StoreNotaConsultaRequest`: Diagnóstico y tratamiento requeridos.

---

## 8. Capa de Controladores (`App\Http\Controllers`)

Los controladores reciben el Repositorio mediante inyección de dependencias en el constructor. No contienen lógica de base de datos.

Ejemplo ilustrativo del patrón utilizado en los 11 controladores:

```php
namespace App\Http\Controllers;

use App\Http\Repository\CitasRepository;
use App\Http\Requests\StoreCitaRequest;

class CitasController extends Controller
{
    protected $citasRepository;

    public function __construct(CitasRepository $citasRepository)
    {
        $this->citasRepository = $citasRepository;
    }

    public function registrarCita(StoreCitaRequest $request)
    {
        try {
            $resultado = $this->citasRepository->registrarCita($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
```

---

## 9. Middlewares y Seguridad

### 1. Autenticación con Laravel Sanctum
Protege endpoints privados exigiendo el header `Authorization: Bearer <token>`.

### 2. `RoleMiddleware` (`role:admin,recepcionista,doctor,paciente`)
Verifica la propiedad `$usuario->rol` contra el listado de roles permitidos en la ruta. Si el rol no coincide, retorna HTTP 403:
`{"mensaje": "No tienes permisos para realizar esta acción. Rol requerido: admin."}`

### 3. `CheckAccountStatus` (`check.status`)
Inspecciona el estado de la cuenta en cada petición autenticada:
- Si `estado === 'inactivo'`: Retorna HTTP 403 ("Tu cuenta está desactivada").
- Si `estado === 'bloqueado'`: Verifica `bloqueado_hasta`. Si la fecha es futura, rechaza con HTTP 403. Si la fecha ya transcurrió, desbloquea la cuenta automáticamente y permite el paso.

---

## 10. Catálogo Completo de Endpoints API (`routes/api.php`)

Total: **51 Endpoints Registrados**

### 🔓 Rutas Públicas (Sin Autenticación)
| Método | Endpoint | Descripción |
|---|---|---|
| `POST` | `/api/auth/login` | Iniciar sesión y obtener token Sanctum |
| `POST` | `/api/auth/registrarPaciente` | Registro público de pacientes |
| `POST` | `/api/auth/registrarMedico` | Registro público de médicos (con validación de cédula SEP) |
| `GET` | `/api/obtenerEspecialidades` | Lista de especialidades activas |
| `GET` | `/api/obtenerDoctores` | Lista de doctores filtrable |
| `GET` | `/api/obtenerDoctor/{id}` | Detalle de un doctor |
| `GET` | `/api/obtenerDisponibilidad/{doctorId}` | Slots disponibles por fecha (`?fecha=YYYY-MM-DD`) |

### 🔒 Rutas Generales Autenticadas (`auth:sanctum`, `check.status`)
| Método | Endpoint | Descripción |
|---|---|---|
| `POST` | `/api/auth/cerrarSesion` | Invalida el token actual |
| `GET` | `/api/miPerfil` | Obtener datos del perfil autenticado |
| `PUT` | `/api/actualizarMiPerfil` | Actualizar nombre o teléfono |
| `POST` | `/api/cambiarPassword` | Cambiar contraseña ingresando la actual |
| `POST` | `/api/actualizarFoto` | Subir foto de perfil |

### 📱 Rutas para Pacientes - App Móvil (`role:paciente`)
| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/api/misCitas` | Citas agendadas e historial propio |
| `POST` | `/api/agendarCita` | Agendar cita desde la app |
| `GET` | `/api/miCita/{id}` | Detalle de cita con código de referencia |
| `PATCH` | `/api/cancelarMiCita/{id}` | Cancelar cita propia (mínimo 2h de anticipación) |
| `GET` | `/api/miHistorial` | Consultas completadas con diagnóstico |
| `GET` | `/api/miConsulta/{id}` | Detalle de diagnóstico y tratamiento |

### 🩺 Rutas para Médicos (`role:doctor`)
| Método | Endpoint | Descripción |
|---|---|---|
| `PATCH` | `/api/iniciarConsulta/{id}` | Cambiar estado de cita a `en_consulta` |
| `POST` | `/api/registrarNota/{citaId}` | Registrar diagnóstico/tratamiento y completar cita |
| `GET` | `/api/obtenerNotas/{citaId}` | Consultar nota de consulta de una cita |
| `PATCH` | `/api/completarCita/{id}` | Marcar cita como completada |

### 📋 Rutas para Recepcionistas y Admins (`role:admin,recepcionista`)
| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/api/obtenerPacientes` | Búsqueda paginada de pacientes |
| `POST` | `/api/registrarPaciente` | Alta administrativa de paciente |
| `GET` | `/api/obtenerPaciente/{id}` | Perfil completo del paciente |
| `PUT` | `/api/actualizarPaciente/{id}` | Edición de paciente |
| `PATCH` | `/api/desactivarPaciente/{id}` | Desactivar paciente (sin citas pendientes) |
| `GET` | `/api/obtenerCitas` | Agenda de citas filtrable |
| `POST` | `/api/registrarCita` | Agendar cita desde recepción |
| `GET` | `/api/obtenerCita/{id}` | Detalle de cita |
| `PUT` | `/api/reprogramarCita/{id}` | Reagendar fecha u hora |
| `PATCH` | `/api/cancelarCita/{id}` | Cancelar cita registrando motivo |
| `PATCH` | `/api/checkInCita/{id}` | Registrar llegada del paciente |

### 👑 Rutas Exclusivas de Administrador (`role:admin`)
| Método | Endpoint | Descripción |
|---|---|---|
| `POST` | `/api/auth/registrarRecepcionista` | Registrar cuentas de recepcionistas |
| `POST` | `/api/registrarDoctor` | Alta de médico |
| `PUT` | `/api/actualizarDoctor/{id}` | Edición de médico |
| `PATCH` | `/api/validarDoctor/{id}` | Aprobar o rechazar solicitud de médico |
| `POST` | `/api/registrarEspecialidad` | Registrar nueva especialidad |
| `GET` | `/api/obtenerHorarios/{doctorId}` | Consultar horarios del médico |
| `POST` | `/api/registrarHorario/{doctorId}` | Configurar horario semanal |
| `PUT` | `/api/actualizarHorario/{id}` | Modificar horario |
| `DELETE` | `/api/eliminarHorario/{id}` | Eliminar horario |
| `GET` | `/api/obtenerBloqueos/{doctorId}` | Consultar bloqueos de fechas |
| `POST` | `/api/registrarBloqueo/{doctorId}` | Registrar vacaciones/bloqueo |
| `DELETE` | `/api/eliminarBloqueo/{id}` | Eliminar bloqueo |
| `GET` | `/api/reporteCitas` | Reporte general de citas |
| `GET` | `/api/reporteDoctores` | Doctores con más consultas |
| `GET` | `/api/reporteEspecialidades` | Especialidades más solicitadas |
| `GET` | `/api/reportePacientes` | Pacientes más frecuentes |
| `GET` | `/api/resumenDiario` | Resumen del flujo del día |
| `GET` | `/api/exportarReporte/{tipo}` | Exportación a PDF o Excel (`?formato=pdf`) |

---

## 11. Reglas de Negocio Implementadas

1. **Bloqueo por Intentos Fallidos**: Al acumular 5 intentos fallidos consecutivos de login en el mismo correo, la cuenta cambia a estado `bloqueado` durante 15 minutos exactos.
2. **Validación de Cédula Profesional SEP (Mock)**: Durante el autoregistro de un médico, el sistema consulta la tabla `verificaciones_cedula`. Si la cédula no existe o no es válida, la cuenta no se crea. Si es válida, se crea en estado `pendiente` a la espera de aprobación por un administrador.
3. **Generación de Folios Únicos**:
   - Expedientes de pacientes: `EXP-YYYYMMDD-XXXX`
   - Citas médicas: `CITA-XXXXXX`
4. **Validación Anti-Solapamiento de Horarios**: No se permite configurar dos bloques de horario para el mismo doctor en el mismo día que se crucen en horas.
5. **Cálculo Dinámico de Disponibilidad**: Los slots disponibles se generan dinámicamente a partir del rango laboral del médico, excluyendo citas ya agendadas y bloqueos por vacaciones.
6. **Protección de Desactivación de Paciente**: Un paciente no puede ser desactivado si tiene citas pendientes en estado `agendada`, `confirmada` o `en_consulta`.
7. **Regla de 2 Horas para Cancelación Móvil**: Los pacientes desde la app móvil solo pueden cancelar citas si faltan 2 horas o más para la hora agendada.
8. **Restricción de Citas Múltiples**: Un paciente no puede agendar más de una cita activa con el mismo médico el mismo día desde la app móvil.
9. **Transición Automática por Diagnóstico**: Al guardar la nota de consulta (diagnóstico y tratamiento), la cita cambia automáticamente su estado a `completada`.

---

## 12. Ejecución, Seeders y Pruebas Automatizadas

### Requisitos del Sistema:
- PHP 8.2+
- MySQL 8.0+
- Composer

### Configuración del Entorno (`.env`):
```env
APP_NAME="Sistema de Gestion de Citas Medicas"
APP_ENV=local
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=citas_medicas
DB_USERNAME=root
DB_PASSWORD=1111

MAIL_MAILER=log
```

### Ejecutar Migraciones y Seeders:
```bash
php artisan migrate:fresh --seed
```

#### Credenciales Administrador Creadas por Seeder:
- **Email**: `admin@citasmedicas.com`
- **Password**: `Admin1234!`

### Ejecutar Pruebas Automatizadas:
```bash
php artisan test
```
*Resultado actual: 7 tests pasados exitosamente con 37 aserciones.*
