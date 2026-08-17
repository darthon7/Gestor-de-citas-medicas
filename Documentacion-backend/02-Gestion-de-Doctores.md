# 👨‍⚕️ Módulo 2: Gestión de Doctores y Especialidades

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura](#2-diagrama-de-arquitectura)
3. [Modelo de Datos Relacional](#3-modelo-de-datos-relacional)
4. [Capa de Base de Datos — Migraciones](#4-capa-de-base-de-datos--migraciones)
   - 4.1 [Tabla `especialidades`](#41-tabla-especialidades)
   - 4.2 [Tabla `perfiles_doctor`](#42-tabla-perfiles_doctor)
   - 4.3 [Tabla Pivote `doctor_especialidad`](#43-tabla-pivote-doctor_especialidad)
5. [Capa de Modelos (Eloquent ORM)](#5-capa-de-modelos-eloquent-orm)
   - 5.1 [Modelo `PerfilDoctor`](#51-modelo-perfildoctor)
   - 5.2 [Modelo `Especialidad`](#52-modelo-especialidad)
6. [Capa de Repositorios (Lógica de Negocio)](#6-capa-de-repositorios-lógica-de-negocio)
   - 6.1 [`DoctoresRepository`](#61-doctoresrepository)
   - 6.2 [`EspecialidadesRepository`](#62-especialidadesrepository)
7. [Capa de Validaciones (Form Requests y Validación Inline)](#7-capa-de-validaciones-form-requests-y-validación-inline)
   - 7.1 [`StoreDoctorRequest`](#71-storedoctorrequest)
   - 7.2 [Validación Inline en `EspecialidadesWebController`](#72-validación-inline-en-especialidadeswebcontroller)
8. [Capa de Controladores (API REST vs Blade SSR)](#8-capa-de-controladores-api-rest-vs-blade-ssr)
   - 8.1 [`DoctoresController` (API REST)](#81-doctorescontroller-api-rest)
   - 8.2 [`EspecialidadesController` (API REST)](#82-especialidadescontroller-api-rest)
   - 8.3 [`DoctoresWebController` (Web Blade SSR)](#83-doctoreswebcontroller-web-blade-ssr)
   - 8.4 [`EspecialidadesWebController` (Web Blade SSR)](#84-especialidadeswebcontroller-web-blade-ssr)
9. [Capa de Vistas (Blade SSR UI y Componentes)](#9-capa-de-vistas-blade-ssr-ui-y-componentes)
   - 9.1 [Panel de Gestión de Doctores (`doctores/index.blade.php`)](#91-panel-de-gestión-de-doctores-doctoresindexbladephp)
   - 9.2 [Gestión de Horarios y Bloqueos (`doctores/horarios.blade.php`)](#92-gestión-de-horarios-y-bloqueos-doctoreshorariosbladephp)
   - 9.3 [Catálogo de Especialidades (`especialidades/index.blade.php`)](#93-catálogo-de-especialidades-especialidadesindexbladephp)
10. [Rutas (API y Web)](#10-rutas-api-y-web)
   - 10.1 [Rutas API (`routes/api.php`)](#101-rutas-api-routesapiphp)
   - 10.2 [Rutas Web (`routes/web.php`)](#102-rutas-web-routeswebphp)
11. [Seeders (Datos Iniciales y Carga Idempotente)](#11-seeders-datos-iniciales-y-carga-idempotente)
   - 11.1 [`EspecialidadesSeeder`](#111-especialidadesseeder)
12. [Flujos Completos de Operación](#12-flujos-completos-de-operación)
   - 12.1 [Flujo de Registro y Validación de Doctor (Web Admin)](#121-flujo-de-registro-y-validación-de-doctor-web-admin)
   - 12.2 [Flujo de Consulta Pública de Doctores con Filtros (API REST / Móvil)](#122-flujo-de-consulta-pública-de-doctores-con-filtros-api-rest--móvil)
   - 12.3 [Flujo de Consulta Pública de Especialidades (API REST)](#123-flujo-de-consulta-pública-de-especialidades-api-rest)
   - 12.4 [Flujo de Creación de Nueva Especialidad (Web Admin)](#124-flujo-de-creación-de-nueva-especialidad-web-admin)
   - 12.5 [Flujo de Gestión Integrada de Horarios y Bloqueos (Web Admin)](#125-flujo-de-gestión-integrada-de-horarios-y-bloqueos-web-admin)
13. [Relación con Otros Módulos](#13-relación-con-otros-módulos)
14. [Mapa de Archivos del Módulo](#14-mapa-de-archivos-del-módulo)

---

## 1. Visión General del Módulo

El módulo de **Gestión de Doctores y Especialidades** administra el ciclo de vida completo del personal médico dentro del sistema, así como el catálogo maestro de especialidades de la institución médica: desde el registro y perfilamiento profesional del médico, la aprobación o rechazo con trazabilidad de auditoría, hasta la asignación multidireccional de especialidades médicas (relación Many-to-Many) y la consulta pública de facultativos.

Este módulo se encuentra estrechamente integrado con el de **Autenticación y Seguridad** (control de estado de cuenta y login condicionado), el de **Horarios y Bloqueos** (definición de jornada laboral y excepciones) y el de **Gestión de Citas** (enrutamiento de consultas por especialidad).

### Responsabilidades Principales

| Responsabilidad | Descripción Técnica |
|---|---|
| **Registro de doctores (Admin)** | Alta administrativa de cuentas médicas con generación opcional de contraseña por defecto, asignación directa de cédulas y vinculación múltiple de especialidades. |
| **Validación de facultativos** | Flujo de auditoría formal (`pendiente` → `validado` / `rechazado`) realizado por el Administrador con registro de fecha (`validado_en`), ID del auditor (`validado_por`) y notas explicativas. |
| **Efecto cascada en acceso** | Sincronización automática con la tabla `usuarios`: el rechazo de un médico conmuta su cuenta a `estado = 'inactivo'`, bloqueando el acceso vía middleware `CheckAccountStatus`. |
| **Catálogo maestro de especialidades** | Administración centralizada de las ramas médicas con control de activación lógica (*soft-toggle*) mediante el flag booleano `activa`. |
| **Asignación doctor-especialidad** | Asociación Muchos a Muchos implementada con la tabla pivote `doctor_especialidad` y sincronización atómica mediante `$doctor->especialidades()->sync()`. |
| **Consulta pública sin autenticación** | Endpoints de catálogo abiertos (`GET /api/obtenerDoctores` y `GET /api/obtenerEspecialidades`) para consumo de la aplicación móvil Android y pacientes no registrados. |
| **Centralización de agenda médica** | Interfaz web unificada (`DoctoresWebController@horarios`) que orquesta repositorios de horarios y bloqueos para gestionar turnos semanales y ausencias programadas. |

### Roles que Interactúan con este Módulo

| Rol | Vía de Acceso | Capacidades y Permisos |
|---|---|---|
| **Administrador (`admin`)** | Web SSR / API | CRUD integral de médicos, validación/rechazo de perfiles, alta de especialidades en el catálogo, configuración de horarios semanales y registro de bloqueos. |
| **Médico (`doctor`)** | Web SSR / API | Visualización de perfil profesional propio, consulta de agenda asignada y turnos de consulta. |
| **Recepcionista (`recepcionista`)** | Web SSR | Consulta de médicos en catálogo y disponibilidad para canalizar citas de pacientes presenciales. |
| **Paciente (`paciente`)** | Móvil Android / Web | Búsqueda y filtrado de doctores por especialidad, nombre o disponibilidad de agenda. |
| **Público (Sin Auth)** | API REST Pública | Consulta libre del catálogo maestro de especialidades y listado de facultativos acreditados. |

### Estrategia de Acceso Dual (API REST vs Blade SSR)

1. **API REST (JSON):** Diseñada para consumo por parte de la aplicación móvil Android (Kotlin + Volley) y clientes desacoplados. Retorna respuestas HTTP estructuradas en formato JSON (`{ "mensaje": "...", "data": [...] }`).
2. **Web SSR (Laravel Blade + Tailwind CSS):** Diseñada para el panel de control administrativo de escritorio. Utiliza validación inline y Form Requests, redirecciones con mensajes flash (`with('success', ...)`), y renderizado del lado del servidor con modales interactivos.

---

## 2. Diagrama de Arquitectura

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                   PETICIÓN CLIENTE                                     │
│          API REST (App Móvil / Público)           │       Web SSR (Panel Admin Blade)  │
└───────────────────────────┬───────────────────────┴────────────────────────┬───────────┘
                            │                                                │
                            ▼                                                ▼
               ┌──────────────────────────┐                     ┌──────────────────────────┐
               │    DoctoresController    │                     │   DoctoresWebController  │
               │ EspecialidadesController │                     │EspecialidadesWebControl. │
               │        (API JSON)        │                     │   (Blade SSR + Redirect) │
               └────────────┬─────────────┘                     └────────────┬─────────────┘
                            │                                                │
                            │           ┌────────────────────────┐           │
                            └──────────►│   StoreDoctorRequest   │◄──────────┘
                                        │   Validación Inline    │
                                        └───────────┬────────────┘
                                                    │
                             ┌──────────────────────┴──────────────────────┐
                             ▼                                             ▼
               ┌──────────────────────────┐                  ┌──────────────────────────┐
               │    DoctoresRepository    │                  │ EspecialidadesRepository │
               │  • obtenerDoctores()     │                  │  • obtenerEspecialidades()│
               │  • registrarDoctor()     │                  │  • registrarEspecialidad()│
               │  • obtenerDoctor()       │                  │  • obtenerEspecialidad() │
               │  • actualizarDoctor()    │                  └─────────────┬────────────┘
               │  • validarDoctor()       │                                │
               └────────────┬─────────────┘                                │
                            │                                              │
                            ▼                                              ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                   MODELOS ELOQUENT ORM                                 │
│                                                                                        │
│   ┌──────────────┐          ┌──────────────────┐          ┌────────────────────────┐   │
│   │   Usuario    │◄─────────│   PerfilDoctor   │─────────►│      Especialidad      │   │
│   │    (base)    │  (1:1)   │   (profesional)  │  (M:N)   │       (catálogo)       │   │
│   └──────────────┘          └────────┬─────────┘          └───────────┬────────────┘   │
│                                      │                                │                │
│                         ┌────────────┼────────────┐                   │ (1:N)          │
│                         ▼            ▼            ▼                   ▼                │
│                ┌───────────────┐┌─────────┐┌──────────────┐    ┌──────────────────┐    │
│                │ HorarioDoctor ││ Bloqueo ││     Cita     │    │       Cita       │    │
│                │     (1:N)     ││  (1:N)  ││    (1:N)     │    │  (especialidad)  │    │
│                └───────────────┘└─────────┘└──────────────┘    └──────────────────┘    │
└──────────────────────────────────────────┬─────────────────────────────────────────────┘
                                           │
                                           ▼
                                ┌─────────────────────┐
                                │   Base de Datos     │
                                │      (MySQL)        │
                                └─────────────────────┘
```

---

## 3. Modelo de Datos Relacional

El módulo se estructura alrededor de tres tablas principales (`usuarios`, `perfiles_doctor`, `especialidades`) y la tabla de unión (`doctor_especialidad`), articulando relaciones One-to-One, Many-to-Many, One-to-Many y Many-to-One:

```
┌─────────────────────┐       ┌────────────────────────┐       ┌─────────────────────────┐
│      usuarios       │       │    perfiles_doctor     │       │   doctor_especialidad   │
│─────────────────────│       │────────────────────────│       │   (Tabla Pivote M:N)    │
│ id (PK)             │◄──┐   │ id (PK)                │◄──┐   │─────────────────────────│
│ nombre              │   │   │ usuario_id (FK)        │───┘   │ perfil_doctor_id (FK)   │───►perfiles_doctor.id
│ email (UNIQUE)      │   │   │ cedula_profesional(UNQ)│       │ especialidad_id (FK)    │───►especialidades.id
│ password            │   │   │ cedula_especialidad    │       │ PRIMARY KEY (ambas FKs) │
│ curp                │   │   │ estado_validacion (ENM)│       └─────────────────────────┘
│ telefono            │   │   │ notas_validacion       │
│ rol ('doctor')      │   │   │ validado_por (FK) ──┐  │       ┌─────────────────────────┐
│ estado ('activo')   │   │   │ validado_en            │  │       │     especialidades      │
└─────────────────────┘   │   └────────────────────────┘  │       │─────────────────────────│
                          │                               │       │ id (PK)                 │
                          └───────────────────────────────┼───────│ nombre (VARCHAR UNIQUE) │
                                                          │       │ descripcion (TEXT NULL) │
                                                          │       │ activa (BOOLEAN DEFAULT)│
                                                          │       │ timestamps              │
                                                          │       └────────────┬────────────┘
                                                          ▼                    │
                                                usuarios.id (Admin validador)  │ (1:N)
                                                                               ▼
                                                                  ┌─────────────────────────┐
                                                                  │          citas          │
                                                                  │─────────────────────────│
                                                                  │ id (PK)                 │
                                                                  │ perfil_doctor_id (FK)   │
                                                                  │ especialidad_id (FK) ───┘
                                                                  │ fecha_cita / hora_cita  │
                                                                  │ ...                     │
                                                                  └─────────────────────────┘
```

### Detalle de Relaciones Relacionales

| Entidad Origen | Entidad Destino | Cardinalidad | Clave Foránea | Comportamiento en Cascada |
|---|---|---|---|---|
| `usuarios` | `perfiles_doctor` | 1 : 1 | `perfiles_doctor.usuario_id` | `ON DELETE CASCADE` (Al borrar usuario, se elimina el perfil). |
| `perfiles_doctor` | `especialidades` | M : N | `doctor_especialidad.perfil_doctor_id` + `especialidad_id` | `ON DELETE CASCADE` en ambos lados de la tabla pivote. |
| `usuarios` (Admin) | `perfiles_doctor` | 1 : N | `perfiles_doctor.validado_por` | `ON DELETE SET NULL` (Si el admin se borra, el registro del doctor conserva su validez). |
| `perfiles_doctor` | `horarios_doctor` | 1 : N | `horarios_doctor.perfil_doctor_id` | `ON DELETE CASCADE` |
| `perfiles_doctor` | `bloqueos_horario`| 1 : N | `bloqueos_horario.perfil_doctor_id`| `ON DELETE CASCADE` |
| `perfiles_doctor` | `citas` | 1 : N | `citas.perfil_doctor_id` | `ON DELETE RESTRICT` (Protección de historial clínico). |
| `especialidades` | `citas` | 1 : N | `citas.especialidad_id` | `ON DELETE RESTRICT` (Protección de citas agendadas). |

---

## 4. Capa de Base de Datos — Migraciones

### 4.1 Tabla `especialidades`

**Archivo:** `database/migrations/2026_01_01_000002_crear_tabla_especialidades.php`

Catálogo maestro de especialidades médicas. Se ejecuta como migración #2 para permitir que las tablas posteriores referencien su clave primaria.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id();                              // PK autoincremental BIGINT UNSIGNED
            $table->string('nombre')->unique();        // Nombre único de la rama médica
            $table->text('descripcion')->nullable();   // Descripción clínica detallada
            $table->boolean('activa')->default(true);  // Soft-toggle de disponibilidad
            $table->timestamps();                      // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidades');
    }
};
```

**Análisis técnico de columnas:**

| Campo | Tipo SQL | Restricción | Explicación Técnica |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `PRIMARY KEY AUTO_INCREMENT` | Identificador único numérico para la especialidad. |
| `nombre` | `VARCHAR(255)` | `UNIQUE INDEX` | Impide duplicados en BD (ej. evita dos registros para "Cardiología"). Genera un índice B-Tree que acelera las búsquedas al agendar citas. |
| `descripcion` | `TEXT` | `NULLABLE` | Almacena textos explicativos largos sin el límite de 255 caracteres de `VARCHAR`. |
| `activa` | `TINYINT(1)` / `BOOLEAN` | `DEFAULT 1 (true)` | Implementa un **soft-toggle**: en vez de ejecutar `DELETE` (lo que violaría restricciones de clave foránea en citas históricas), se marca `activa = false` para ocultarla en nuevos agendamientos. |
| `timestamps` | `TIMESTAMP` | `NULLABLE` | Auditoría de creación y última actualización automática vía Eloquent. |

---

### 4.2 Tabla `perfiles_doctor`

**Archivo:** `database/migrations/2026_01_01_000003_crear_tabla_perfiles_doctor.php`

Extiende la tabla `usuarios` con información profesional, cédulas de ejercicio y trazabilidad del proceso de validación.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles_doctor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('cedula_profesional')->unique();
            $table->string('cedula_especialidad')->nullable();
            $table->enum('estado_validacion', ['pendiente', 'validado', 'rechazado'])->default('pendiente');
            $table->text('notas_validacion')->nullable();
            $table->foreignId('validado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamp('validado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles_doctor');
    }
};
```

**Análisis técnico de columnas y políticas de eliminación:**

| Campo | Tipo | Restricción / FK | Explicación Técnica |
|---|---|---|---|
| `usuario_id` | `BIGINT UNSIGNED` | `constrained('usuarios')->onDelete('cascade')` | FK hacia `usuarios`. Política `cascade`: al eliminar la cuenta de usuario, su perfil médico se elimina en cascada. |
| `cedula_profesional` | `VARCHAR(255)` | `UNIQUE` | Garantiza que ninguna cédula médica pueda duplicarse en el sistema. |
| `cedula_especialidad` | `VARCHAR(255)` | `NULLABLE` | Cédula opcional de grado o subespecialidad médica. |
| `estado_validacion` | `ENUM` | `default('pendiente')` | Estados del ciclo de vida: `pendiente` (recién registrado), `validado` (aprobado por admin), `rechazado` (rechazado). |
| `notas_validacion` | `TEXT` | `NULLABLE` | Motivo de aprobación o rechazo registrado por el administrador. |
| `validado_por` | `BIGINT UNSIGNED` | `nullable()->constrained('usuarios')->onDelete('set null')` | FK hacia el administrador que dictaminó la validación. Usa `set null` para preservar la validez del doctor aunque el usuario administrador sea eliminado. |
| `validado_en` | `TIMESTAMP` | `NULLABLE` | Fecha y hora exacta de la aprobación/rechazo (`now()`). |

---

### 4.3 Tabla Pivote `doctor_especialidad`

**Archivo:** `database/migrations/2026_01_01_000006_crear_tabla_doctor_especialidad.php`

Implementa la relación de unión Muchos a Muchos entre `perfiles_doctor` y `especialidades`.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_especialidad', function (Blueprint $table) {
            $table->foreignId('perfil_doctor_id')->constrained('perfiles_doctor')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->primary(['perfil_doctor_id', 'especialidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_especialidad');
    }
};
```

**Propiedades de diseño de la tabla pivote:**
1. **Sin clave autoincremental (`id`) ni `timestamps`:** Al ser una tabla de unión pura, se optimiza almacenamiento y velocidad en consultas `JOIN`.
2. **Clave Primaria Compuesta:** `primary(['perfil_doctor_id', 'especialidad_id'])` previene a nivel de motor de base de datos la inserción duplicada de una misma especialidad en un perfil médico.
3. **Doble `onDelete('cascade')`:** Al eliminar un doctor o una especialidad, los registros asociados en la tabla de unión se limpian automáticamente.

---

## 5. Capa de Modelos (Eloquent ORM)

### 5.1 Modelo `PerfilDoctor`

**Archivo:** `app/Models/PerfilDoctor.php`

Representa el perfil profesional del médico y articula 6 relaciones de negocio:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilDoctor extends Model
{
    use HasFactory;

    protected $table = 'perfiles_doctor';

    protected $fillable = [
        'usuario_id',
        'cedula_profesional',
        'cedula_especialidad',
        'estado_validacion',
        'notas_validacion',
        'validado_por',
        'validado_en',
    ];

    protected $casts = [
        'validado_en' => 'datetime',
    ];

    /**
     * Relación 1: Inversa One-to-One con Usuario (cuenta base)
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación 2: Many-to-Many con Especialidad vía tabla pivote
     */
    public function especialidades()
    {
        return $this->belongsToMany(
            Especialidad::class,
            'doctor_especialidad',
            'perfil_doctor_id',
            'especialidad_id'
        );
    }

    /**
     * Relación 3: One-to-Many con HorarioDoctor (jornadas semanales)
     */
    public function horarios()
    {
        return $this->hasMany(HorarioDoctor::class, 'perfil_doctor_id');
    }

    /**
     * Relación 4: One-to-Many con BloqueoHorario (excepciones y vacaciones)
     */
    public function bloqueos()
    {
        return $this->hasMany(BloqueoHorario::class, 'perfil_doctor_id');
    }

    /**
     * Relación 5: One-to-Many con Cita (consultas agendadas)
     */
    public function citas()
    {
        return $this->hasMany(Cita::class, 'perfil_doctor_id');
    }

    /**
     * Relación 6: Many-to-One con Usuario (Administrador que dictaminó la validación)
     */
    public function validadoPor()
    {
        return $this->belongsTo(Usuario::class, 'validado_por');
    }
}
```

**Operaciones sobre la relación Many-to-Many `especialidades()`:**

```php
// 1. Obtener colección de especialidades vinculadas
$especialidades = $doctor->especialidades;

// 2. Sincronización atómica (reemplaza las actuales por el array especificado)
$doctor->especialidades()->sync([1, 3, 5]);

// 3. Vincular una especialidad adicional sin alterar las previas
$doctor->especialidades()->attach(7);

// 4. Desvincular una especialidad puntual
$doctor->especialidades()->detach(3);
```

| Método | Comportamiento | Escenario de Uso |
|---|---|---|
| `sync([1, 3, 5])` | Borra las relaciones ausentes en el array y agrega las nuevas | Formularios web de edición y registro |
| `attach($id)` | Inserta una relación individual preservando las existentes | Asignación incremental |
| `detach($id)` | Remueve una relación específica | Revocación puntual de especialidad |

---

### 5.2 Modelo `Especialidad`

**Archivo:** `app/Models/Especialidad.php`

Representa cada registro del catálogo maestro y gestiona sus relaciones inversas:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /**
     * Relación Muchos a Muchos inversa con PerfilDoctor
     */
    public function doctores()
    {
        return $this->belongsToMany(
            PerfilDoctor::class,
            'doctor_especialidad',
            'especialidad_id',
            'perfil_doctor_id'
        );
    }

    /**
     * Relación Uno a Muchos con Cita
     */
    public function citas()
    {
        return $this->hasMany(Cita::class, 'especialidad_id');
    }
}
```

**Puntos técnicos del modelo:**
- `'activa' => 'boolean'`: Casteo nativo que garantiza que `$especialidad->activa` sea siempre evaluado como `true`/`false` en PHP.
- `doctores()`: Inversa de `PerfilDoctor::especialidades()`, invirtiendo los parámetros de clave foránea en la tabla pivote (`especialidad_id` como local, `perfil_doctor_id` como relacionada).

---

## 6. Capa de Repositorios (Lógica de Negocio)

### 6.1 `DoctoresRepository`

**Archivo:** `app/Http/Repository/DoctoresRepository.php`

Encapsula el CRUD de facultativos, búsquedas dinámicas con Eager Loading y el flujo crítico de validación administrativa:

```php
<?php

namespace App\Http\Repository;

use App\Models\PerfilDoctor;
use App\Models\Usuario;
use Exception;

class DoctoresRepository
{
    /**
     * Obtiene el listado de doctores con filtros dinámicos y paginación.
     */
    public function obtenerDoctores(array $filtros = [])
    {
        try {
            $query = PerfilDoctor::with(['usuario', 'especialidades']);

            if (!empty($filtros['especialidad_id'])) {
                $query->whereHas('especialidades', function ($q) use ($filtros) {
                    $q->where('especialidades.id', $filtros['especialidad_id']);
                });
            }

            if (!empty($filtros['estado_validacion'])) {
                $query->where('estado_validacion', $filtros['estado_validacion']);
            }

            if (!empty($filtros['buscar'])) {
                $buscar = $filtros['buscar'];
                $query->whereHas('usuario', function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%$buscar%");
                });
            }

            $doctores = $query->paginate($filtros['por_pagina'] ?? 15);

            return [
                'mensaje' => 'Doctores obtenidos correctamente',
                'data'    => $doctores,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Registra un nuevo médico desde el panel administrativo.
     */
    public function registrarDoctor(array $data)
    {
        try {
            $usuario = Usuario::create([
                'nombre'   => $data['nombre'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password'] ?? 'Doctor1234!'),
                'curp'     => isset($data['curp']) ? strtoupper($data['curp']) : null,
                'telefono' => $data['telefono'] ?? null,
                'rol'      => 'doctor',
                'estado'   => 'activo',
            ]);

            $perfilDoctor = PerfilDoctor::create([
                'usuario_id'          => $usuario->id,
                'cedula_profesional'  => $data['cedula_profesional'],
                'cedula_especialidad' => $data['cedula_especialidad'] ?? null,
                'estado_validacion'   => $data['estado_validacion'] ?? 'pendiente',
            ]);

            if (!empty($data['especialidades'])) {
                $perfilDoctor->especialidades()->sync($data['especialidades']);
            }

            return [
                'mensaje' => 'Doctor registrado correctamente',
                'data'    => $perfilDoctor->load(['usuario', 'especialidades']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Consulta el detalle individual de un médico.
     */
    public function obtenerDoctor(int $id)
    {
        try {
            $doctor = PerfilDoctor::with(['usuario', 'especialidades', 'horarios'])->find($id);

            if (!$doctor) {
                return ['mensaje' => 'Doctor no encontrado'];
            }

            return [
                'mensaje' => 'Doctor obtenido correctamente',
                'data'    => $doctor,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Actualiza los datos del usuario y perfil médico.
     */
    public function actualizarDoctor(int $id, array $data)
    {
        try {
            $doctor = PerfilDoctor::find($id);
            if (!$doctor) {
                return ['mensaje' => 'Doctor no encontrado'];
            }

            $doctor->usuario->update([
                'nombre'   => $data['nombre']   ?? $doctor->usuario->nombre,
                'email'    => $data['email']     ?? $doctor->usuario->email,
                'telefono' => $data['telefono']  ?? $doctor->usuario->telefono,
            ]);

            $doctor->update([
                'cedula_profesional'  => $data['cedula_profesional']  ?? $doctor->cedula_profesional,
                'cedula_especialidad' => $data['cedula_especialidad'] ?? $doctor->cedula_especialidad,
            ]);

            if (!empty($data['especialidades'])) {
                $doctor->especialidades()->sync($data['especialidades']);
            }

            return [
                'mensaje' => 'Doctor actualizado correctamente',
                'data'    => $doctor->load(['usuario', 'especialidades']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Dictamina la validación o rechazo del médico con efecto cascada en su cuenta.
     */
    public function validarDoctor(int $id, array $data, int $adminId)
    {
        try {
            $doctor = PerfilDoctor::find($id);
            if (!$doctor) {
                return ['mensaje' => 'Doctor no encontrado'];
            }

            $doctor->update([
                'estado_validacion' => $data['estado_validacion'],
                'notas_validacion'  => $data['notas_validacion'] ?? null,
                'validado_por'      => $adminId,
                'validado_en'       => now(),
            ]);

            // Efecto cascada sobre el estado de la cuenta base
            $estadoUsuario = $data['estado_validacion'] === 'rechazado' ? 'inactivo' : 'activo';
            $doctor->usuario->update(['estado' => $estadoUsuario]);

            return [
                'mensaje' => 'Estado de validación actualizado',
                'data'    => $doctor->load('usuario'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
```

**Análisis de Optimización y Reglas de Negocio:**
1. **Prevención del problema N+1:** El uso de `with(['usuario', 'especialidades'])` consolida la consulta en 3 queries fijas, evitando ejecutar 1 query SQL por cada doctor iterado en la vista o respuesta JSON.
2. **Filtrado por subconsulta `whereHas()`:** Permite filtrar facultativos según el nombre de la especialidad o texto de búsqueda en la tabla `usuarios` sin necesidad de realizar `JOIN` manuales.
3. **Efecto Cascada de Seguridad:** En `validarDoctor()`, si el dictamen es `'rechazado'`, se conmuta automáticamente `usuarios.estado = 'inactivo'`. El middleware global `CheckAccountStatus` intercepta subsecuentes peticiones de login rechazando al usuario.

---

### 6.2 `EspecialidadesRepository`

**Archivo:** `app/Http/Repository/EspecialidadesRepository.php`

Encapsula la persistencia y lectura del catálogo maestro:

```php
<?php

namespace App\Http\Repository;

use App\Models\Especialidad;
use Exception;

class EspecialidadesRepository
{
    /**
     * Retorna todas las especialidades activas ordenadas alfabéticamente.
     */
    public function obtenerEspecialidades()
    {
        try {
            $especialidades = Especialidad::where('activa', true)
                ->orderBy('nombre')
                ->get();

            return [
                'mensaje' => 'Especialidades obtenidas correctamente',
                'data'    => $especialidades,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Registra una nueva especialidad médica en el catálogo maestro.
     */
    public function registrarEspecialidad(array $data)
    {
        try {
            $especialidad = Especialidad::create([
                'nombre'      => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'activa'      => true,
            ]);

            return [
                'mensaje' => 'Especialidad registrada correctamente',
                'data'    => $especialidad,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Consulta una especialidad individual por su ID.
     */
    public function obtenerEspecialidad(int $id)
    {
        try {
            $especialidad = Especialidad::find($id);

            if (!$especialidad) {
                return ['mensaje' => 'Especialidad no encontrada'];
            }

            return [
                'mensaje' => 'Especialidad obtenida correctamente',
                'data'    => $especialidad,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
```

---

## 7. Capa de Validaciones (Form Requests y Validación Inline)

### 7.1 `StoreDoctorRequest`

**Archivo:** `app/Http/Requests/StoreDoctorRequest.php`

Valida las peticiones de alta de médicos tanto en el controlador API como en el controlador Web:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'              => 'required|string|max:255',
            'email'               => 'required|email|unique:usuarios,email',
            'password'            => 'nullable|string|min:8',
            'curp'                => 'nullable|string|size:18',
            'telefono'            => 'nullable|string|max:20',
            'cedula_profesional'  => 'required|string|unique:perfiles_doctor,cedula_profesional',
            'cedula_especialidad' => 'nullable|string',
            'especialidades'      => 'nullable|array',
            'especialidades.*'    => 'exists:especialidades,id',
            'estado_validacion'   => 'nullable|in:pendiente,validado,rechazado',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'             => 'El nombre es requerido.',
            'email.required'              => 'El correo electrónico es requerido.',
            'email.unique'                => 'El correo electrónico ya está registrado.',
            'cedula_profesional.required' => 'La cédula profesional es requerida.',
            'cedula_profesional.unique'   => 'La cédula profesional ya está registrada.',
            'especialidades.*.exists'     => 'Una o más especialidades seleccionadas no son válidas.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'msj'    => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
```

**Diferencias arquitectónicas: Registro Admin vs Auto-Registro:**

| Aspecto | `StoreDoctorRequest` (Admin) | `StoreRegistroMedicoRequest` (Auto-Registro Público) |
|---|---|---|
| **Contexto de Seguridad** | Operado por el Administrador autenticado | Formulario público de registro |
| **Validación de Cédula** | Se valida unicidad en BD | Se valida existencia contra mock SEP |
| **Contraseña** | `nullable` (Asigna `'Doctor1234!'` si no se envía) | `required|confirmed|min:8` |
| **Estado Inicial** | Configurable (`pendiente`, `validado`, `rechazado`) | Forzado a `pendiente` |

---

### 7.2 Validación Inline en `EspecialidadesWebController`

Dado que el catálogo de especialidades consta de 2 campos simples, se utiliza **validación inline** en `EspecialidadesWebController@store` para evitar sobrecarga de clases:

```php
$request->validate([
    'nombre'      => 'required|string|max:100|unique:especialidades,nombre',
    'descripcion' => 'nullable|string|max:255',
], [
    'nombre.required' => 'El nombre de la especialidad es obligatorio.',
    'nombre.unique'   => 'Esta especialidad médica ya se encuentra registrada.',
    'nombre.max'      => 'El nombre no debe exceder los 100 caracteres.',
]);
```

---

## 8. Capa de Controladores (API REST vs Blade SSR)

### 8.1 `DoctoresController` (API REST)

**Archivo:** `app/Http/Controllers/DoctoresController.php`

Controlador REST ligero que retorna respuestas en formato JSON para el cliente móvil y servicios externos:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Repository\DoctoresRepository;
use App\Http\Requests\StoreDoctorRequest;
use Illuminate\Http\Request;

class DoctoresController extends Controller
{
    protected $doctoresRepository;

    public function __construct(DoctoresRepository $doctoresRepository)
    {
        $this->doctoresRepository = $doctoresRepository;
    }

    public function obtenerDoctores(Request $request)
    {
        try {
            $resultado = $this->doctoresRepository->obtenerDoctores($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarDoctor(StoreDoctorRequest $request)
    {
        try {
            $resultado = $this->doctoresRepository->registrarDoctor($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function obtenerDoctor(int $id)
    {
        try {
            $resultado = $this->doctoresRepository->obtenerDoctor($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function actualizarDoctor(Request $request, int $id)
    {
        try {
            $resultado = $this->doctoresRepository->actualizarDoctor($id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function validarDoctor(Request $request, int $id)
    {
        try {
            $adminId   = $request->user()->id;
            $resultado = $this->doctoresRepository->validarDoctor($id, $request->all(), $adminId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
```

---

### 8.2 `EspecialidadesController` (API REST)

**Archivo:** `app/Http/Controllers/EspecialidadesController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Repository\EspecialidadesRepository;
use Illuminate\Http\Request;

class EspecialidadesController extends Controller
{
    protected $especialidadesRepository;

    public function __construct(EspecialidadesRepository $especialidadesRepository)
    {
        $this->especialidadesRepository = $especialidadesRepository;
    }

    public function obtenerEspecialidades()
    {
        try {
            $resultado = $this->especialidadesRepository->obtenerEspecialidades();
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarEspecialidad(Request $request)
    {
        try {
            $resultado = $this->especialidadesRepository->registrarEspecialidad($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
```

---

### 8.3 `DoctoresWebController` (Web Blade SSR)

**Archivo:** `app/Http/Controllers/Web/DoctoresWebController.php`

Orquesta 4 repositorios para alimentar las vistas del panel de administración web:

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\BloqueosRepository;
use App\Http\Repository\DoctoresRepository;
use App\Http\Repository\EspecialidadesRepository;
use App\Http\Repository\HorariosRepository;
use App\Http\Requests\StoreBloqueoRequest;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\StoreHorarioRequest;
use Illuminate\Http\Request;

class DoctoresWebController extends Controller
{
    protected $doctoresRepository;
    protected $especialidadesRepository;
    protected $horariosRepository;
    protected $bloqueosRepository;

    public function __construct(
        DoctoresRepository $doctoresRepository,
        EspecialidadesRepository $especialidadesRepository,
        HorariosRepository $horariosRepository,
        BloqueosRepository $bloqueosRepository
    ) {
        $this->doctoresRepository = $doctoresRepository;
        $this->especialidadesRepository = $especialidadesRepository;
        $this->horariosRepository = $horariosRepository;
        $this->bloqueosRepository = $bloqueosRepository;
    }

    public function index(Request $request)
    {
        $resDoctores = $this->doctoresRepository->obtenerDoctores([
            'buscar'            => $request->query('buscar'),
            'especialidad_id'   => $request->query('especialidad_id'),
            'estado_validacion' => $request->query('estado_validacion') ?: null,
        ]);
        $doctores = isset($resDoctores['data']) ? collect($resDoctores['data']->items()) : collect();

        $resEsp = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $resEsp['data'] ?? [];

        return view('doctores.index', compact('doctores', 'especialidades'));
    }

    public function store(StoreDoctorRequest $request)
    {
        try {
            $this->doctoresRepository->registrarDoctor($request->all());
            return redirect()->route('doctores.index')->with('success', 'Médico registrado con éxito. Pendiente de validación.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->doctoresRepository->actualizarDoctor($id, $request->all());
            return redirect()->route('doctores.index')->with('success', 'Médico actualizado con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function validar(Request $request, $id)
    {
        try {
            $this->doctoresRepository->validarDoctor($id, $request->all(), $request->user()->id);
            return redirect()->route('doctores.index')->with('success', 'Estado de validación del médico actualizado.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function horarios($doctorId)
    {
        try {
            $doctorRes   = $this->doctoresRepository->obtenerDoctor($doctorId);
            $doctor      = $doctorRes['data'] ?? null;

            $horariosRes = $this->horariosRepository->obtenerHorarios($doctorId);
            $horarios    = $horariosRes['data'] ?? [];

            $bloqueosRes = $this->bloqueosRepository->obtenerBloqueos($doctorId);
            $bloqueos    = $bloqueosRes['data'] ?? [];

            return view('doctores.horarios', compact('doctor', 'horarios', 'bloqueos', 'doctorId'));
        } catch (\Exception $e) {
            return redirect()->route('doctores.index')->with('error', $e->getMessage());
        }
    }

    public function storeHorario(StoreHorarioRequest $request, $doctorId)
    {
        try {
            $this->horariosRepository->registrarHorario($doctorId, $request->all());
            return back()->with('success', 'Horario registrado correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateHorario(Request $request, $id)
    {
        try {
            $this->horariosRepository->actualizarHorario($id, $request->all());
            return back()->with('success', 'Horario actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteHorario($id)
    {
        try {
            $this->horariosRepository->eliminarHorario($id);
            return back()->with('success', 'Horario eliminado.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeBloqueo(StoreBloqueoRequest $request, $doctorId)
    {
        try {
            $this->bloqueosRepository->registrarBloqueo($doctorId, $request->all(), $request->user()->id);
            return back()->with('success', 'Bloqueo de horario registrado.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteBloqueo($id)
    {
        try {
            $this->bloqueosRepository->eliminarBloqueo($id);
            return back()->with('success', 'Bloqueo eliminado.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

---

### 8.4 `EspecialidadesWebController` (Web Blade SSR)

**Archivo:** `app/Http/Controllers/Web/EspecialidadesWebController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\EspecialidadesRepository;
use Illuminate\Http\Request;

class EspecialidadesWebController extends Controller
{
    protected $especialidadesRepository;

    public function __construct(EspecialidadesRepository $especialidadesRepository)
    {
        $this->especialidadesRepository = $especialidadesRepository;
    }

    public function index()
    {
        $res = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $res['data'] ?? [];

        return view('especialidades.index', compact('especialidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100|unique:especialidades,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        try {
            $this->especialidadesRepository->registrarEspecialidad($request->all());
            return redirect()->route('especialidades.index')
                ->with('success', 'Especialidad creada con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
```

---

## 9. Capa de Vistas (Blade SSR UI y Componentes)

Las vistas del módulo están construidas con **Blade SSR + Tailwind CSS (Clinical Clarity)** y **Material Symbols**:

### 9.1 Panel de Gestión de Doctores (`doctores/index.blade.php`)

**Archivo:** `resources/views/doctores/index.blade.php`

- **Listado y Filtros:** Barra de búsqueda por nombre y dropdown dinámico de especialidades.
- **Badges de Estado de Validación:**
  - `validado`: Verde esmeralda (`bg-emerald-50 text-emerald-700`).
  - `pendiente`: Amarillo ámbar (`bg-amber-50 text-amber-700`).
  - `rechazado`: Rojo carmesí (`bg-red-50 text-red-700`).
- **Modales Integrados:** Modal de alta de médico con checklist de especialidades múltiples, modal de edición de datos, y modal de dictamen de validación rápida.
- **Acceso Directo a Horarios:** Botón para navegar a la configuración de turnos del doctor (`route('doctores.horarios', $doc->id)`).

---

### 9.2 Gestión de Horarios y Bloqueos (`doctores/horarios.blade.php`)

**Archivo:** `resources/views/doctores/horarios.blade.php`

- **Encabezado Clínico:** Tarjeta resumen con avatar del médico, cédula profesional y badges de especialidades asignadas.
- **Grilla de Horarios Semanales:** Configuración de días (`lunes` a `domingo`), `hora_inicio`, `hora_fin` y duración por consulta (`duracion_consulta_minutos`).
- **Listado de Bloqueos:** Tabla de ausencias programadas y modal para registrar nuevos bloqueos por fecha o rango de horas con motivo clínico.

---

### 9.3 Catálogo de Especialidades (`especialidades/index.blade.php`)

**Archivo:** `resources/views/especialidades/index.blade.php`

```html
@extends('layouts.app')
@section('titulo', 'Catálogo de Especialidades')

@section('content')
<!-- Header Controls -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Catálogo de Especialidades</h1>
        <p class="text-xs text-text-secondary mt-0.5">Especialidades configuradas para los servicios del centro médico</p>
    </div>
    <button type="button" onclick="abrirModalEspecialidad()" class="px-5 py-2.5 bg-primary text-white rounded-xl font-semibold text-xs flex items-center justify-center gap-2 shadow-md hover:bg-primary-dark active:scale-[0.99] transition-all">
        <span class="material-symbols-outlined text-lg">add</span>
        <span>Nueva Especialidad</span>
    </button>
</div>

<!-- Especialidades Table Card -->
<div class="bg-surface rounded-2xl card-shadow border border-border overflow-hidden max-w-4xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background/60 border-b border-border text-xs font-semibold text-text-secondary uppercase tracking-wider">
                    <th class="px-6 py-4"># ID</th>
                    <th class="px-6 py-4">Nombre de la Especialidad</th>
                    <th class="px-6 py-4">Descripción</th>
                    <th class="px-6 py-4 text-right">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-sm">
                @forelse($especialidades as $esp)
                    <tr class="hover:bg-background/40 transition-colors">
                        <td class="px-6 py-4 font-bold text-primary text-xs whitespace-nowrap">#{{ $esp['id'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-primary-light/40 text-primary-dark font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($esp['nombre'] ?? 'E', 0, 1)) }}
                                </div>
                                <span class="font-semibold text-text-primary text-xs">{{ $esp['nombre'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-text-secondary">{{ $esp['descripcion'] ?? 'Sin descripción' }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                                Activo
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-xs text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-1 block text-text-muted">stethoscope</span>
                            No hay especialidades registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nueva Especialidad -->
<div id="modal_especialidad" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
    <div class="bg-surface rounded-2xl shadow-2xl border border-border w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-background border-b border-border flex items-center justify-between">
            <h3 class="font-bold text-primary-dark text-base">Nueva Especialidad Médica</h3>
            <button type="button" onclick="cerrarModalEspecialidad()" class="text-text-muted hover:text-text-primary transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('especialidades.store') }}" class="p-6 space-y-4">
            @csrf
            <div class="space-y-1">
                <label for="txt_nombre_esp" class="text-xs font-semibold text-text-secondary block">Nombre de la Especialidad *</label>
                <input type="text" id="txt_nombre_esp" name="nombre" required placeholder="Ej: Cardiología, Pediatría" class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
            <div class="space-y-1">
                <label for="txt_desc_esp" class="text-xs font-semibold text-text-secondary block">Descripción</label>
                <input type="text" id="txt_desc_esp" name="descripcion" placeholder="Descripción opcional..." class="w-full px-4 py-2.5 bg-white border border-border rounded-xl text-sm text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
            </div>
            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalEspecialidad()" class="px-4 py-2.5 rounded-xl border border-border text-text-secondary text-xs font-semibold hover:bg-background transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-semibold shadow-md transition-all">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function abrirModalEspecialidad() { document.getElementById('modal_especialidad').classList.remove('hidden'); }
    function cerrarModalEspecialidad() { document.getElementById('modal_especialidad').classList.add('hidden'); }
</script>
@endsection
```

---

## 10. Rutas (API y Web)

### 10.1 Rutas API (`routes/api.php`)

```php
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\EspecialidadesController;
use Illuminate\Support\Facades\Route;

// ═════════════════════════════════════════════════════════════════════
// RUTAS PÚBLICAS (Consumo de Móvil Android / Pacientes sin Auth)
// ═════════════════════════════════════════════════════════════════════
Route::get('/obtenerDoctores',        [DoctoresController::class, 'obtenerDoctores']);
Route::get('/obtenerDoctor/{id}',     [DoctoresController::class, 'obtenerDoctor']);
Route::get('/obtenerEspecialidades',  [EspecialidadesController::class, 'obtenerEspecialidades']);

// ═════════════════════════════════════════════════════════════════════
// RUTAS PROTEGIDAS — Solo Administrador (Sanctum Token + Rol Admin)
// ═════════════════════════════════════════════════════════════════════
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        // Gestión de Doctores
        Route::post('/registrarDoctor',       [DoctoresController::class, 'registrarDoctor']);
        Route::put('/actualizarDoctor/{id}',  [DoctoresController::class, 'actualizarDoctor']);
        Route::patch('/validarDoctor/{id}',   [DoctoresController::class, 'validarDoctor']);

        // Gestión de Especialidades
        Route::post('/registrarEspecialidad', [EspecialidadesController::class, 'registrarEspecialidad']);
    });
});
```

### Tabla de Endpoints API REST

| Verbo HTTP | Endpoint URI | Auth / Middleware | Rol | Descripción Funcional |
|---|---|---|---|---|
| `GET` | `/api/obtenerDoctores` | Ninguna (Pública) | — | Listar médicos con filtros query (`buscar`, `especialidad_id`, `estado_validacion`) y paginación. |
| `GET` | `/api/obtenerDoctor/{id}` | Ninguna (Pública) | — | Obtener perfil completo del médico con especialidades y horarios. |
| `GET` | `/api/obtenerEspecialidades`| Ninguna (Pública) | — | Catálogo completo de especialidades médicas activas ordenadas de A a Z. |
| `POST` | `/api/registrarDoctor` | `auth:sanctum`, `check.status` | `admin` | Registrar un médico con usuario, perfil y especialidades vinculadas. |
| `PUT` | `/api/actualizarDoctor/{id}`| `auth:sanctum`, `check.status` | `admin` | Actualizar nombre, contacto, cédulas y especialidades del facultativo. |
| `PATCH`| `/api/validarDoctor/{id}` | `auth:sanctum`, `check.status` | `admin` | Dictaminar aprobación o rechazo del médico registrando trazabilidad y conmutando el estado de la cuenta. |
| `POST` | `/api/registrarEspecialidad`| `auth:sanctum`, `check.status` | `admin` | Crear una nueva rama médica en el catálogo maestro. |

---

### 10.2 Rutas Web (`routes/web.php`)

```php
use App\Http\Controllers\Web\DoctoresWebController;
use App\Http\Controllers\Web\EspecialidadesWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        // Gestión Web de Doctores
        Route::get('/doctores',                [DoctoresWebController::class, 'index'])->name('doctores.index');
        Route::post('/doctores',               [DoctoresWebController::class, 'store'])->name('doctores.store');
        Route::put('/doctores/{id}',           [DoctoresWebController::class, 'update'])->name('doctores.update');
        Route::patch('/doctores/{id}/validar', [DoctoresWebController::class, 'validar'])->name('doctores.validar');

        // Gestión Web de Horarios y Bloqueos (Anidadas al Doctor)
        Route::get('/doctores/{id}/horarios',  [DoctoresWebController::class, 'horarios'])->name('doctores.horarios');
        Route::post('/doctores/{id}/horarios', [DoctoresWebController::class, 'storeHorario'])->name('horarios.store');
        Route::put('/horarios/{id}',           [DoctoresWebController::class, 'updateHorario'])->name('horarios.update');
        Route::delete('/horarios/{id}',        [DoctoresWebController::class, 'deleteHorario'])->name('horarios.destroy');
        Route::post('/doctores/{id}/bloqueos', [DoctoresWebController::class, 'storeBloqueo'])->name('bloqueos.store');
        Route::delete('/bloqueos/{id}',        [DoctoresWebController::class, 'deleteBloqueo'])->name('bloqueos.destroy');

        // Catálogo Web de Especialidades
        Route::get('/especialidades',          [EspecialidadesWebController::class, 'index'])->name('especialidades.index');
        Route::post('/especialidades',         [EspecialidadesWebController::class, 'store'])->name('especialidades.store');
    });
});
```

---

## 11. Seeders (Datos Iniciales y Carga Idempotente)

### 11.1 `EspecialidadesSeeder`

**Archivo:** `database/seeders/EspecialidadesSeeder.php`

Precarga 15 especialidades médicas estándar durante la inicialización del sistema (`php artisan db:seed`). La ejecución es estrictamente **idempotente** mediante `firstOrCreate()`:

```php
<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use Illuminate\Database\Seeder;

class EspecialidadesSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = [
            ['nombre' => 'Medicina General',       'descripcion' => 'Atención médica primaria y general.'],
            ['nombre' => 'Pediatría',              'descripcion' => 'Atención médica para niños y adolescentes.'],
            ['nombre' => 'Cardiología',            'descripcion' => 'Diagnóstico y tratamiento de enfermedades del corazón.'],
            ['nombre' => 'Dermatología',           'descripcion' => 'Tratamiento de enfermedades de la piel.'],
            ['nombre' => 'Ginecología',            'descripcion' => 'Salud reproductiva femenina.'],
            ['nombre' => 'Oftalmología',           'descripcion' => 'Diagnóstico y tratamiento de enfermedades de los ojos.'],
            ['nombre' => 'Ortopedia',              'descripcion' => 'Tratamiento del sistema músculo-esquelético.'],
            ['nombre' => 'Neurología',             'descripcion' => 'Enfermedades del sistema nervioso.'],
            ['nombre' => 'Psiquiatría',            'descripcion' => 'Salud mental y trastornos psiquiátricos.'],
            ['nombre' => 'Endocrinología',         'descripcion' => 'Enfermedades hormonales y metabólicas.'],
            ['nombre' => 'Gastroenterología',      'descripcion' => 'Enfermedades del aparato digestivo.'],
            ['nombre' => 'Urología',               'descripcion' => 'Enfermedades del aparato urinario.'],
            ['nombre' => 'Otorrinolaringología',   'descripcion' => 'Oídos, nariz y garganta.'],
            ['nombre' => 'Neumología',             'descripcion' => 'Enfermedades del aparato respiratorio.'],
            ['nombre' => 'Reumatología',           'descripcion' => 'Enfermedades articulares y autoinmunes.'],
        ];

        foreach ($especialidades as $esp) {
            Especialidad::firstOrCreate(['nombre' => $esp['nombre']], $esp);
        }
    }
}
```

---

## 12. Flujos Completos de Operación

### 12.1 Flujo de Registro y Validación de Doctor (Web Admin)

```
ADMINISTRADOR (Browser)               DoctoresWebController                  DoctoresRepository                 Base de Datos (MySQL)
         │                                      │                                      │                                 │
         │ 1. POST /doctores                    │                                      │                                 │
         │    {nombre, email, cedula,           │                                      │                                 │
         │     especialidades: [1, 3]}          │                                      │                                 │
         ├─────────────────────────────────────►│                                      │                                 │
         │                                      │ StoreDoctorRequest::validate()       │                                 │
         │                                      │ ──► doctoresRepo->registrarDoctor()  │                                 │
         │                                      ├─────────────────────────────────────►│                                 │
         │                                      │                                      │ 1. Usuario::create()            │
         │                                      │                                      ├────────────────────────────────►│
         │                                      │                                      │ 2. PerfilDoctor::create()       │
         │                                      │                                      ├────────────────────────────────►│
         │                                      │                                      │ 3. sync([1, 3]) (Pivote)        │
         │                                      │                                      ├────────────────────────────────►│
         │                                      │                                      │◄────────────────────────────────┤
         │                                      │◄─────────────────────────────────────┤                                 │
         │ 302 Redirect (/doctores)             │                                      │                                 │
         │ [Estado: 'pendiente']                │                                      │                                 │
         │◄─────────────────────────────────────┤                                      │                                 │
         │                                      │                                      │                                 │
         │ 2. PATCH /doctores/{id}/validar      │                                      │                                 │
         │    {estado_validacion: 'validado'}   │                                      │                                 │
         ├─────────────────────────────────────►│                                      │                                 │
         │                                      │ ──► doctoresRepo->validarDoctor()    │                                 │
         │                                      ├─────────────────────────────────────►│                                 │
         │                                      │                                      │ UPDATE perfiles_doctor SET      │
         │                                      │                                      │  estado_validacion='validado',  │
         │                                      │                                      │  validado_por=1, validado_en=NOW│
         │                                      │                                      ├────────────────────────────────►│
         │                                      │                                      │ UPDATE usuarios SET             │
         │                                      │                                      │  estado='activo' (Cascada)      │
         │                                      │                                      ├────────────────────────────────►│
         │                                      │                                      │◄────────────────────────────────┤
         │                                      │◄─────────────────────────────────────┤                                 │
         │ 302 Redirect (/doctores)             │                                      │                                 │
         │ [Doctor Habilitado para Login ✅]    │                                      │                                 │
         │◄─────────────────────────────────────┤                                      │                                 │
```

---

### 12.2 Flujo de Consulta Pública de Doctores con Filtros (API REST / Móvil)

```
PACIENTE / APP ANDROID                         DoctoresController                    DoctoresRepository                 Base de Datos (MySQL)
         │                                              │                                      │                                 │
         │ GET /api/obtenerDoctores?especialidad_id=3   │                                      │                                 │
         │                         &buscar=López        │                                      │                                 │
         ├─────────────────────────────────────────────►│                                      │                                 │
         │                                              │ obtenerDoctores($filtros)            │                                 │
         │                                              ├─────────────────────────────────────►│                                 │
         │                                              │                                      │ SELECT * FROM perfiles_doctor   │
         │                                              │                                      │  WHERE EXISTS (pivot esp_id=3)  │
         │                                              │                                      │  AND EXISTS (user LIKE 'López') │
         │                                              │                                      │  LIMIT 15 OFFSET 0              │
         │                                              │                                      ├────────────────────────────────►│
         │                                              │                                      │◄────────────────────────────────┤
         │                                              │◄─────────────────────────────────────┤                                 │
         │ JSON 200 OK                                  │                                      │                                 │
         │◄─────────────────────────────────────────────┤                                      │                                 │
```

**Ejemplo de Payload JSON Retornado (200 OK):**

```json
{
  "mensaje": "Doctores obtenidos correctamente",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "usuario_id": 2,
        "cedula_profesional": "12345678",
        "cedula_especialidad": "ESP-9988",
        "estado_validacion": "validado",
        "usuario": {
          "id": 2,
          "nombre": "Dr. Juan Carlos López",
          "email": "dr.lopez@citasmedicas.com",
          "telefono": "5551234567"
        },
        "especialidades": [
          {
            "id": 3,
            "nombre": "Cardiología",
            "activa": true
          }
        ]
      }
    ],
    "per_page": 15,
    "total": 1
  }
}
```

---

### 12.3 Flujo de Consulta Pública de Especialidades (API REST)

```
APP MÓVIL (Kotlin / Volley)               EspecialidadesController              EspecialidadesRepository            Base de Datos (MySQL)
         │                                              │                                      │                                 │
         │ GET /api/obtenerEspecialidades               │                                      │                                 │
         ├─────────────────────────────────────────────►│                                      │                                 │
         │                                              │ obtenerEspecialidades()              │                                 │
         │                                              ├─────────────────────────────────────►│                                 │
         │                                              │                                      │ SELECT * FROM especialidades    │
         │                                              │                                      │  WHERE activa = 1               │
         │                                              │                                      │  ORDER BY nombre ASC            │
         │                                              │                                      ├────────────────────────────────►│
         │                                              │                                      │◄────────────────────────────────┤
         │                                              │◄─────────────────────────────────────┤                                 │
         │ JSON 200 OK                                  │                                      │                                 │
         │◄─────────────────────────────────────────────┤                                      │                                 │
```

---

### 12.4 Flujo de Creación de Nueva Especialidad (Web Admin)

```
ADMINISTRADOR (Browser)               EspecialidadesWebController            EspecialidadesRepository            Base de Datos (MySQL)
         │                                          │                                      │                                 │
         │ 1. Abre modal #modal_especialidad        │                                      │                                 │
         │ 2. POST /especialidades                  │                                      │                                 │
         │    {nombre: "Neurología"}                │                                      │                                 │
         ├─────────────────────────────────────────►│                                      │                                 │
         │                                          │ $request->validate()                 │                                 │
         │                                          │ ──► registrarEspecialidad($data)     │                                 │
         │                                          ├─────────────────────────────────────►│                                 │
         │                                          │                                      │ INSERT INTO especialidades      │
         │                                          │                                      │  (nombre, activa, created_at)   │
         │                                          │                                      │  VALUES ('Neurología', 1, NOW)  │
         │                                          │                                      ├────────────────────────────────►│
         │                                          │                                      │◄────────────────────────────────┤
         │                                          │◄─────────────────────────────────────┤                                 │
         │ 302 Redirect (/especialidades)           │                                      │                                 │
         │ + Flash Session: "Especialidad creada..."│                                      │                                 │
         │◄─────────────────────────────────────────┤                                      │                                 │
```

---

### 12.5 Flujo de Gestión Integrada de Horarios y Bloqueos (Web Admin)

```
ADMINISTRADOR (Browser)                    DoctoresWebController                     Repositorios Inyectados
         │                                            │                                         │
         │ GET /doctores/{id}/horarios                │                                         │
         ├───────────────────────────────────────────►│                                         │
         │                                            │ 1. doctoresRepo->obtenerDoctor(id)      │
         │                                            ├────────────────────────────────────────►│ ──► PerfilDoctor + User
         │                                            │ 2. horariosRepo->obtenerHorarios(id)    │
         │                                            ├────────────────────────────────────────►│ ──► Horarios semanales
         │                                            │ 3. bloqueosRepo->obtenerBloqueos(id)    │
         │                                            ├────────────────────────────────────────►│ ──► Bloqueos vigentes
         │                                            │                                         │
         │ Renderiza Vista Blade                      │                                         │
         │ `doctores.horarios`                        │                                         │
         │◄───────────────────────────────────────────┤                                         │
```

---

## 13. Relación con Otros Módulos

```
                               ┌─────────────────────────────────────────┐
                               │       Módulo 1: Autenticación           │
                               │  • Auto-registro de médicos (mock SEP)  │
                               │  • Middleware check.status y role:admin │
                               └────────────────────┬────────────────────┘
                                                    │
                                                    ▼
                               ┌─────────────────────────────────────────┐
                               │   Módulo 2: DOCTORES Y ESPECIALIDADES   │
                               │  • Gestión de perfiles y validación     │
                               │  • Catálogo maestro de especialidades   │
                               │  • Tabla pivote doctor_especialidad     │
                               └──────┬──────────────────────┬───────────┘
                                      │                      │
                   ┌──────────────────┘                      └──────────────────┐
                   ▼                                                            ▼
┌────────────────────────────────────────┐                   ┌────────────────────────────────────────┐
│  Módulo 4: Horarios y Bloqueos         │                   │  Módulo 5: Gestión de Citas            │
│  • Define turnos y horas de consulta   │                   │  • Agendamiento por especialidad y doc │
│  • Bloqueos por vacaciones/permisos    │                   │  • Validación de slots disponibles     │
│  • Motor de slots libres para citas    │                   │  • Historial clínico del paciente      │
└──────────────────┬─────────────────────┘                   └──────────────────┬─────────────────────┘
                   │                                                            │
                   └────────────────────────────┬───────────────────────────────┘
                                                │
                                                ▼
                               ┌─────────────────────────────────────────┐
                               │  Módulo 7: Notas de Consulta            │
                               │  Módulo 9: Reportes y Estadísticas      │
                               │  • Diagnóstico y recetas por médico     │
                               │  • Métricas de citas por especialidad   │
                               └─────────────────────────────────────────┘
```

1. **Módulo 1 (Autenticación y Seguridad):** Comparte el modelo `Usuario` y el middleware de control de estado. El estado `validado`/`rechazado` del doctor conmuta directamente `usuarios.estado = 'activo'|'inactivo'`.
2. **Módulo 4 (Horarios y Disponibilidad):** Utiliza `perfil_doctor_id` como clave foránea para estructurar la jornada laboral semanal (`horarios_doctor`) y las excepciones de agenda (`bloqueos_horario`).
3. **Módulo 5 (Gestión de Citas):** Las citas almacenan concurrentemente `perfil_doctor_id` y `especialidad_id`, asegurando trazabilidad exacta de la disciplina en que se brindó la atención.
4. **Módulo 7 (Notas de Consulta):** Cada nota médica registra el diagnóstico y tratamiento emitido por el `perfil_doctor_id` asociado a la cita completada.
5. **Módulo 9 (Reportes y Estadísticas):** Agrupa estadísticas de demanda y volumen de consultas atendidas por especialidad médica y por facultativo.

---

## 14. Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DoctoresController.php              # API REST: Endpoints JSON de doctores
│   │   │   ├── EspecialidadesController.php         # API REST: Endpoints JSON de especialidades
│   │   │   └── Web/
│   │   │       ├── DoctoresWebController.php        # Web SSR: Orquestación Doctores + Horarios
│   │   │       └── EspecialidadesWebController.php  # Web SSR: Catálogo de especialidades
│   │   ├── Repository/
│   │   │   ├── DoctoresRepository.php               # Lógica de datos, Eager loading y validación
│   │   │   └── EspecialidadesRepository.php          # Lógica de datos del catálogo maestro
│   │   └── Requests/
│   │       └── StoreDoctorRequest.php                # Validación de entrada para médicos
│   └── Models/
│       ├── PerfilDoctor.php                          # Modelo Eloquent con 6 relaciones
│       └── Especialidad.php                          # Modelo Eloquent del catálogo maestro
├── database/
│   ├── migrations/
│   │   ├── 2026_01_01_000002_crear_tabla_especialidades.php   # Migración catálogo especialidades
│   │   ├── 2026_01_01_000003_crear_tabla_perfiles_doctor.php  # Migración perfil profesional
│   │   └── 2026_01_01_000006_crear_tabla_doctor_especialidad.php # Migración tabla pivote M:N
│   └── seeders/
│       └── EspecialidadesSeeder.php                   # Seeder idempotente (15 especialidades)
├── resources/views/
│   ├── doctores/
│   │   ├── index.blade.php                            # Vista Web: Tabla de doctores y modales
│   │   └── horarios.blade.php                         # Vista Web: Gestión de horarios y bloqueos
│   └── especialidades/
│       └── index.blade.php                            # Vista Web: Catálogo de especialidades
└── routes/
    ├── api.php                                        # Definición de rutas REST públicas y admin
    └── web.php                                        # Definición de rutas Web SSR con sesión
```

---

> **Módulo anterior:** [01 - Autenticación y Seguridad](./01-Autenticacion-y-Seguridad.md)  
> **Siguiente módulo:** [04 - Horarios y Bloqueos](./04-Horarios-y-Bloqueos.md)
