# 👨‍⚕️ Módulo 2: Gestión de Doctores y Especialidades

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura](#2-diagrama-de-arquitectura)
3. [Modelo de Datos Relacional](#3-modelo-de-datos-relacional)
4. [Capa de Base de Datos — Migraciones](#4-capa-de-base-de-datos--migraciones)
5. [Capa de Modelos (Eloquent ORM)](#5-capa-de-modelos-eloquent-orm)
6. [Capa de Repositorios (Lógica de Negocio)](#6-capa-de-repositorios-lógica-de-negocio)
7. [Capa de Form Requests (Validación)](#7-capa-de-form-requests-validación)
8. [Capa de Controladores](#8-capa-de-controladores)
9. [Rutas (API y Web)](#9-rutas-api-y-web)
10. [Seeders (Datos Iniciales)](#10-seeders-datos-iniciales)
11. [Flujos Completos de Operación](#11-flujos-completos-de-operación)
12. [Relación con Otros Módulos](#12-relación-con-otros-módulos)

---

## 1. Visión General del Módulo

El módulo de **Gestión de Doctores y Especialidades** administra el ciclo de vida completo de los médicos dentro del sistema: desde su registro y validación hasta la asignación de especialidades médicas. Este módulo está estrechamente ligado al de Autenticación (registro de médicos) y al de Horarios/Citas (disponibilidad).

### Responsabilidades principales

| Responsabilidad | Descripción |
|---|---|
| **Registro de doctores** | Crear cuentas de médicos desde el panel de administración (diferente al auto-registro del módulo de Auth) |
| **Validación de doctores** | Flujo de aprobación/rechazo por parte del administrador con trazabilidad |
| **Catálogo de especialidades** | Gestión del catálogo maestro de especialidades médicas |
| **Asignación doctor-especialidad** | Relación Many-to-Many entre doctores y especialidades |
| **Consulta pública de doctores** | Endpoints públicos para que los pacientes busquen médicos disponibles |
| **Gestión de horarios y bloqueos** | El web controller centraliza la gestión de horarios (orquestando repositorios externos) |

### Roles que interactúan con este módulo

| Rol | Permisos |
|---|---|
| **Admin** | CRUD completo de doctores, validación, gestión de especialidades, horarios y bloqueos |
| **Público (sin auth)** | Consultar listado de doctores y detalle individual |
| **Paciente** | Consultar doctores al agendar cita (vía el módulo de Disponibilidad) |

---

## 2. Diagrama de Arquitectura

```
┌──────────────────────────────────────────────────────────────────────┐
│                        PETICIÓN HTTP                                 │
│              API (Móvil/Público)  │  Web (Panel Admin)               │
└────────────────────┬─────────────────────────┬───────────────────────┘
                     │                         │
                     ▼                         ▼
        ┌────────────────────┐    ┌──────────────────────────┐
        │  DoctoresController│    │  DoctoresWebController   │
        │  EspecialidadesCtrl│    │  EspecialidadesWebCtrl   │
        │    (API JSON)      │    │  (Blade + Redirect)      │
        └────────┬───────────┘    └──────────┬───────────────┘
                 │                           │
                 │    ┌──────────────────┐   │
                 └───►│ StoreDoctorReq   │◄──┘
                      │ (Validación)     │
                      └───────┬──────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼                               ▼
┌──────────────────────┐        ┌──────────────────────────┐
│  DoctoresRepository  │        │ EspecialidadesRepository │
│  • obtenerDoctores   │        │ • obtenerEspecialidades  │
│  • registrarDoctor   │        │ • registrarEspecialidad  │
│  • obtenerDoctor     │        │ • obtenerEspecialidad    │
│  • actualizarDoctor  │        └───────────┬──────────────┘
│  • validarDoctor     │                    │
└──────────┬───────────┘                    │
           │                                │
           ▼                                ▼
┌──────────────────────────────────────────────────────┐
│                    MODELOS ELOQUENT                   │
│  ┌──────────┐   ┌───────────────┐   ┌──────────────┐│
│  │ Usuario  │◄──│ PerfilDoctor  │──►│ Especialidad ││
│  │  (base)  │   │ (1:1)        │   │  (M:N pivot) ││
│  └──────────┘   └───────────────┘   └──────────────┘│
│                        │                             │
│           ┌────────────┼────────────┐                │
│           ▼            ▼            ▼                │
│  ┌──────────────┐ ┌─────────┐ ┌──────────┐          │
│  │HorarioDoctor│ │Bloqueo  │ │  Cita    │          │
│  │   (1:N)     │ │Horario  │ │  (1:N)   │          │
│  └──────────────┘ │ (1:N)  │ └──────────┘          │
│                   └─────────┘                        │
└──────────────────────────────────────────────────────┘
           │
           ▼
    ┌──────────────┐
    │   Database   │
    │   (SQLite)   │
    └──────────────┘
```

---

## 3. Modelo de Datos Relacional

Este módulo involucra una relación **Many-to-Many** entre doctores y especialidades, implementada mediante una tabla pivot.

```
┌──────────────┐       ┌──────────────────┐       ┌─────────────────────┐
│   usuarios   │       │ perfiles_doctor  │       │  doctor_especialidad│
│──────────────│       │──────────────────│       │  (tabla pivot)      │
│ id (PK)      │◄──┐   │ id (PK)          │◄──┐   │─────────────────────│
│ nombre       │   │   │ usuario_id (FK)  │───┘   │ perfil_doctor_id(FK)│───►perfiles_doctor.id
│ email        │   │   │ cedula_profesion.│       │ especialidad_id(FK) │───►especialidades.id
│ password     │   │   │ cedula_especial. │       │ PRIMARY KEY(ambas)  │
│ rol='doctor' │   │   │ estado_validac.  │       └─────────────────────┘
│ ...          │   │   │ notas_validac.   │
└──────────────┘   │   │ validado_por(FK) │───►usuarios.id (admin que validó)
                   │   │ validado_en      │
                   │   └──────────────────┘
                   │
                   │   ┌──────────────────┐
                   │   │  especialidades  │
                   │   │──────────────────│
                   │   │ id (PK)          │
                   │   │ nombre (UNIQUE)  │
                   └───│ descripcion      │
                       │ activa           │
                       └──────────────────┘
```

**Relaciones clave:**
- `usuarios` → `perfiles_doctor`: **One-to-One** (un usuario con rol doctor tiene exactamente un perfil)
- `perfiles_doctor` → `especialidades`: **Many-to-Many** (un doctor puede tener varias especialidades y una especialidad puede pertenecer a varios doctores)
- `perfiles_doctor.validado_por` → `usuarios`: **Many-to-One** (trazabilidad de qué admin validó al doctor)

---

## 4. Capa de Base de Datos — Migraciones

### 4.1 Tabla `especialidades`

**Archivo:** `database/migrations/2026_01_01_000002_crear_tabla_especialidades.php`

Es un **catálogo maestro** de especialidades médicas. Se crea en la migración #2 porque es referenciada por la tabla pivot que se crea después.

```php
Schema::create('especialidades', function (Blueprint $table) {
    $table->id();                              // PK autoincremental
    $table->string('nombre')->unique();        // Nombre de la especialidad (UNIQUE)
    $table->text('descripcion')->nullable();   // Descripción libre de la especialidad
    $table->boolean('activa')->default(true);  // Soft-toggle de visibilidad
    $table->timestamps();                      // created_at, updated_at
});
```

**Aspectos técnicos:**

| Campo | Detalle técnico |
|---|---|
| `nombre` con `unique()` | Impide duplicados a nivel de BD. Si intentas insertar "Cardiología" dos veces, la BD lanzará una excepción `QueryException` con código de violación de constraint UNIQUE. |
| `activa` como `boolean` | Implementa un **soft-toggle**: en vez de eliminar especialidades (lo que rompería FKs existentes), se marcan como `activa = false`. El repositorio filtra con `where('activa', true)` para solo mostrar las vigentes. |
| `text('descripcion')` | Se usa `text` en vez de `string` porque la descripción puede ser larga. `string` genera un `VARCHAR(255)` mientras que `text` genera un `TEXT` sin límite práctico. |

---

### 4.2 Tabla `perfiles_doctor`

**Archivo:** `database/migrations/2026_01_01_000003_crear_tabla_perfiles_doctor.php`

Extiende la tabla `usuarios` con datos específicos del rol médico. Es la tabla más rica del módulo.

```php
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
```

**Análisis campo por campo:**

| Campo | Tipo | Constraint | Explicación técnica |
|---|---|---|---|
| `usuario_id` | `foreignId` | `constrained('usuarios')->onDelete('cascade')` | FK hacia la tabla `usuarios`. `constrained()` es un shortcut de Laravel que genera automáticamente `FOREIGN KEY ... REFERENCES usuarios(id)`. La política `cascade` significa: si se elimina el usuario, su perfil de doctor se elimina automáticamente. |
| `cedula_profesional` | `string` | `unique()` | Cada cédula profesional es única en el sistema. Esto previene que dos perfiles usen la misma cédula. |
| `cedula_especialidad` | `string` | `nullable()` | Cédula opcional de especialidad médica (adicional a la profesional). No todos los médicos tienen una especialidad formal certificada. |
| `estado_validacion` | `enum` | `default('pendiente')` | Los tres estados del ciclo de vida de validación: `pendiente` (recién registrado), `validado` (aprobado por admin), `rechazado` (rechazado por admin). |
| `notas_validacion` | `text` | `nullable()` | Comentarios del administrador al validar/rechazar. Útil para auditoría y para informar al médico el motivo de rechazo. |
| `validado_por` | `foreignId` | `nullable()->constrained('usuarios')->onDelete('set null')` | FK al administrador que realizó la validación. Usa `set null` en vez de `cascade` porque si el admin se elimina, queremos mantener el registro del doctor pero sin la referencia al admin. |
| `validado_en` | `timestamp` | `nullable()` | Fecha y hora exacta de la validación. Se registra con `now()` al momento de validar. |

**Diferencia entre `onDelete('cascade')` y `onDelete('set null')`:**

```
onDelete('cascade'):
  Si se elimina el usuario → se elimina el perfil_doctor automáticamente
  Razón: El perfil no tiene sentido sin su usuario base

onDelete('set null'):
  Si se elimina el admin validador → validado_por se pone en NULL
  Razón: El doctor sigue siendo válido aunque el admin ya no exista
```

---

### 4.3 Tabla `doctor_especialidad` (Pivot)

**Archivo:** `database/migrations/2026_01_01_000006_crear_tabla_doctor_especialidad.php`

Tabla pivot que implementa la relación **Many-to-Many** entre `perfiles_doctor` y `especialidades`.

```php
Schema::create('doctor_especialidad', function (Blueprint $table) {
    $table->foreignId('perfil_doctor_id')->constrained('perfiles_doctor')->onDelete('cascade');
    $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
    $table->primary(['perfil_doctor_id', 'especialidad_id']);
});
```

**Anatomía de la tabla pivot:**

| Aspecto | Detalle técnico |
|---|---|
| **No tiene `id` propio** | A diferencia de las tablas regulares, esta tabla pivot no usa `$table->id()`. Esto es una práctica común para tablas pivot simples. |
| **No tiene `timestamps`** | No necesita `created_at` ni `updated_at` porque las relaciones se gestionan con `sync()` que reemplaza todo el conjunto. |
| **Clave primaria compuesta** | `primary(['perfil_doctor_id', 'especialidad_id'])` garantiza que un doctor no pueda tener la misma especialidad dos veces. Es más eficiente que un índice UNIQUE porque la clave primaria ya incluye un índice. |
| **Doble `cascade`** | Si se elimina el doctor O la especialidad, se eliminan los registros correspondientes en la pivot. |

**¿Por qué se llama `doctor_especialidad` y no `perfil_doctor_especialidad`?**

Laravel tiene una convención para nombrar tablas pivot: nombres de los modelos en **singular**, en **orden alfabético**, separados por `_`. Sin embargo, en este proyecto se usa un nombre personalizado que se declara explícitamente en la relación `belongsToMany` del modelo (tercer parámetro).

---

## 5. Capa de Modelos (Eloquent ORM)

### 5.1 Modelo `PerfilDoctor`

**Archivo:** `app/Models/PerfilDoctor.php`

Este es el modelo central del módulo. Representa el perfil profesional de un médico y gestiona múltiples relaciones.

```php
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
```

**`$table = 'perfiles_doctor'`:** Es obligatorio declarar esto porque Laravel inferiría `perfil_doctors` (siguiendo la convención inglesa de pluralización). Como el proyecto usa nombres en español, debemos indicar el nombre real de la tabla.

**`'validado_en' => 'datetime'`:** Convierte el timestamp almacenado en BD a un objeto `Carbon`, permitiendo operaciones como `$doctor->validado_en->format('d/m/Y H:i')` o `$doctor->validado_en->diffForHumans()`.

---

**Relaciones del modelo (6 en total):**

```php
// ═══════════════════════════════════════════
// RELACIÓN 1: Inversa One-to-One con Usuario
// ═══════════════════════════════════════════
public function usuario()
{
    return $this->belongsTo(Usuario::class, 'usuario_id');
}
```

**`belongsTo`** es la inversa de `hasOne`. Mientras que en `Usuario` definimos `hasOne(PerfilDoctor)`, aquí definimos `belongsTo(Usuario)`. El segundo parámetro `'usuario_id'` indica qué columna de `perfiles_doctor` almacena la FK. Aunque Laravel podría inferirlo (buscaría `usuario_id` automáticamente), es buena práctica explicitarlo.

**Uso práctico:**
```php
$doctor = PerfilDoctor::find(1);
$doctor->usuario->nombre;  // "Dr. Juan Carlos López"
$doctor->usuario->email;   // "dr.lopez@email.com"
```

---

```php
// ═══════════════════════════════════════════
// RELACIÓN 2: Many-to-Many con Especialidad
// ═══════════════════════════════════════════
public function especialidades()
{
    return $this->belongsToMany(
        Especialidad::class,       // Modelo relacionado
        'doctor_especialidad',     // Nombre de la tabla pivot
        'perfil_doctor_id',        // FK en la pivot que apunta a ESTE modelo
        'especialidad_id'          // FK en la pivot que apunta al modelo RELACIONADO
    );
}
```

**`belongsToMany` — Parámetros explicados:**

| Parámetro | Valor | Por qué se especifica |
|---|---|---|
| 1° (Modelo) | `Especialidad::class` | El modelo del otro lado de la relación |
| 2° (Tabla pivot) | `'doctor_especialidad'` | Nombre personalizado de la tabla pivot (no sigue la convención de Laravel) |
| 3° (FK local) | `'perfil_doctor_id'` | Columna en la pivot que referencia a `perfiles_doctor.id` |
| 4° (FK relacionada) | `'especialidad_id'` | Columna en la pivot que referencia a `especialidades.id` |

**Operaciones principales sobre esta relación:**

```php
// Consultar especialidades de un doctor
$doctor->especialidades;
// → Collection [Especialidad{nombre: "Cardiología"}, Especialidad{nombre: "Medicina General"}]

// Asignar especialidades (reemplaza todas las existentes)
$doctor->especialidades()->sync([1, 3, 5]);
// Resultado: Elimina relaciones anteriores y crea las nuevas con IDs 1, 3, 5

// Agregar una especialidad sin eliminar las existentes
$doctor->especialidades()->attach(7);

// Quitar una especialidad específica
$doctor->especialidades()->detach(3);
```

**Diferencia entre `sync()`, `attach()` y `detach()`:**

| Método | Comportamiento | Uso típico |
|---|---|---|
| `sync([1,3,5])` | Elimina todas las relaciones que NO están en el array y agrega las que faltan | Actualización completa desde un formulario |
| `attach(7)` | Agrega una nueva relación SIN tocar las existentes | Agregar una especialidad individual |
| `detach(3)` | Elimina una relación específica SIN tocar las demás | Quitar una especialidad individual |
| `detach()` (sin args) | Elimina TODAS las relaciones | Limpiar antes de reasignar |

---

```php
// ═══════════════════════════════════════════
// RELACIÓN 3: One-to-Many con HorarioDoctor
// ═══════════════════════════════════════════
public function horarios()
{
    return $this->hasMany(HorarioDoctor::class, 'perfil_doctor_id');
}
```

Un doctor puede tener **múltiples horarios** (uno por cada día de la semana, o múltiples turnos en un mismo día). Esta relación es utilizada por el módulo de Horarios (Módulo 4).

---

```php
// ═══════════════════════════════════════════
// RELACIÓN 4: One-to-Many con BloqueoHorario
// ═══════════════════════════════════════════
public function bloqueos()
{
    return $this->hasMany(BloqueoHorario::class, 'perfil_doctor_id');
}
```

Un doctor puede tener **múltiples bloqueos** (vacaciones, permisos, días festivos). Los bloqueos anulan la disponibilidad del horario regular.

---

```php
// ═══════════════════════════════════════════
// RELACIÓN 5: One-to-Many con Cita
// ═══════════════════════════════════════════
public function citas()
{
    return $this->hasMany(Cita::class, 'perfil_doctor_id');
}
```

Todas las citas asignadas a este doctor.

---

```php
// ═══════════════════════════════════════════
// RELACIÓN 6: Many-to-One con Usuario (Admin validador)
// ═══════════════════════════════════════════
public function validadoPor()
{
    return $this->belongsTo(Usuario::class, 'validado_por');
}
```

**`'validado_por'`** como segundo parámetro es **crucial** aquí. Sin él, Laravel buscaría una columna llamada `usuario_id` (inferida del nombre del modelo `Usuario`), pero la columna real se llama `validado_por`. Este es un caso donde el mismo modelo (`Usuario`) tiene dos relaciones `belongsTo` desde `PerfilDoctor`:
1. `usuario()` → El médico (vía `usuario_id`)
2. `validadoPor()` → El administrador que validó (vía `validado_por`)

**Uso práctico:**
```php
$doctor = PerfilDoctor::with('validadoPor')->find(1);
$doctor->validadoPor->nombre;  // "Administrador Principal"
$doctor->validado_en->format('d/m/Y');  // "29/07/2026"
```

---

### 5.2 Modelo `Especialidad`

**Archivo:** `app/Models/Especialidad.php`

```php
class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = ['nombre', 'descripcion', 'activa'];

    protected $casts = ['activa' => 'boolean'];

    public function doctores()
    {
        return $this->belongsToMany(
            PerfilDoctor::class,
            'doctor_especialidad',
            'especialidad_id',      // FK que apunta a ESTE modelo
            'perfil_doctor_id'      // FK que apunta al modelo RELACIONADO
        );
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'especialidad_id');
    }
}
```

**Relación `doctores()` — Lado inverso del M:N:**

Esta es la **misma relación** que `PerfilDoctor::especialidades()`, pero vista desde el otro lado. Los parámetros de FK se invierten:

| Relación | FK local | FK relacionada |
|---|---|---|
| `PerfilDoctor::especialidades()` | `perfil_doctor_id` | `especialidad_id` |
| `Especialidad::doctores()` | `especialidad_id` | `perfil_doctor_id` |

**Uso práctico:**
```php
$cardiologia = Especialidad::find(3);
$cardiologia->doctores;
// → Collection de PerfilDoctor que tienen Cardiología como especialidad

$cardiologia->doctores()->count();
// → Número de doctores con esta especialidad
```

**`'activa' => 'boolean'`:** El cast asegura que `$especialidad->activa` retorne `true`/`false` en PHP, en vez de `1`/`0` que es como SQLite almacena booleanos.

---

## 6. Capa de Repositorios (Lógica de Negocio)

### 6.1 `DoctoresRepository`

**Archivo:** `app/Http/Repository/DoctoresRepository.php`

Contiene 5 métodos que cubren el CRUD completo de doctores más la validación administrativa.

---

#### 6.1.1 Método `obtenerDoctores(array $filtros = [])`

**Propósito:** Listar doctores con filtros dinámicos y paginación.

```php
public function obtenerDoctores(array $filtros = [])
{
    try {
        // Inicia el query con eager loading de relaciones
        $query = PerfilDoctor::with(['usuario', 'especialidades']);

        // Filtro 1: Por especialidad
        if (!empty($filtros['especialidad_id'])) {
            $query->whereHas('especialidades', function ($q) use ($filtros) {
                $q->where('especialidades.id', $filtros['especialidad_id']);
            });
        }

        // Filtro 2: Por estado de validación
        if (!empty($filtros['estado_validacion'])) {
            $query->where('estado_validacion', $filtros['estado_validacion']);
        }

        // Filtro 3: Búsqueda por nombre
        if (!empty($filtros['buscar'])) {
            $buscar = $filtros['buscar'];
            $query->whereHas('usuario', function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%$buscar%");
            });
        }

        // Paginación con 15 resultados por defecto
        $doctores = $query->paginate($filtros['por_pagina'] ?? 15);

        return [
            'mensaje' => 'Doctores obtenidos correctamente',
            'data'    => $doctores,
        ];
    } catch (Exception $e) {
        return ['mensaje' => $e->getMessage()];
    }
}
```

**Conceptos técnicos explicados:**

**`with(['usuario', 'especialidades'])` — Eager Loading:**

Sin `with()`, cada vez que accediéramos a `$doctor->usuario` o `$doctor->especialidades` en una iteración, Laravel haría una consulta SQL adicional. Esto se conoce como el problema **N+1**:

```
Sin eager loading (N+1 problem):
  SELECT * FROM perfiles_doctor;         -- 1 consulta
  SELECT * FROM usuarios WHERE id = 1;   -- +1 por cada doctor
  SELECT * FROM usuarios WHERE id = 2;   -- +1 por cada doctor
  ... (N consultas adicionales)

Con eager loading:
  SELECT * FROM perfiles_doctor;                                    -- 1 consulta
  SELECT * FROM usuarios WHERE id IN (1, 2, 3, ...);              -- 1 consulta
  SELECT * FROM especialidades                                     -- 1 consulta
    JOIN doctor_especialidad ON ...
    WHERE perfil_doctor_id IN (1, 2, 3, ...);
```

Con `with()`, sin importar cuántos doctores haya, siempre se ejecutan exactamente **3 consultas SQL**.

---

**`whereHas()` — Filtrado por relación:**

```php
$query->whereHas('especialidades', function ($q) use ($filtros) {
    $q->where('especialidades.id', $filtros['especialidad_id']);
});
```

`whereHas()` genera una subconsulta SQL `EXISTS` que filtra los doctores que **tienen al menos una** especialidad que coincida:

```sql
SELECT * FROM perfiles_doctor
WHERE EXISTS (
    SELECT 1 FROM doctor_especialidad
    INNER JOIN especialidades ON especialidades.id = doctor_especialidad.especialidad_id
    WHERE doctor_especialidad.perfil_doctor_id = perfiles_doctor.id
    AND especialidades.id = ?
)
```

**`use ($filtros)` — Closures y scope de variables:**

En PHP, las funciones anónimas (closures) no heredan automáticamente las variables del scope padre. La cláusula `use ($filtros)` importa explícitamente la variable `$filtros` al scope de la closure. Sin esto, `$filtros` no sería accesible dentro de la función anónima.

---

**`paginate()` — Paginación automática:**

```php
$doctores = $query->paginate($filtros['por_pagina'] ?? 15);
```

`paginate(15)` ejecuta internamente **dos consultas**:
1. `SELECT COUNT(*) FROM perfiles_doctor WHERE ...` — Total de registros
2. `SELECT * FROM perfiles_doctor WHERE ... LIMIT 15 OFFSET 0` — Página actual

Retorna un objeto `LengthAwarePaginator` que incluye:
```json
{
    "data": [...],           // Los 15 doctores de la página actual
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42,
    "next_page_url": "/api/obtenerDoctores?page=2",
    "prev_page_url": null
}
```

---

#### 6.1.2 Método `registrarDoctor(array $data)`

**Propósito:** Crear un doctor desde el panel admin (diferente al auto-registro del AuthRepository).

```php
public function registrarDoctor(array $data)
{
    try {
        // 1. Crear usuario base con rol doctor
        $usuario = Usuario::create([
            'nombre'   => $data['nombre'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password'] ?? 'Doctor1234!'),
            'curp'     => isset($data['curp']) ? strtoupper($data['curp']) : null,
            'telefono' => $data['telefono'] ?? null,
            'rol'      => 'doctor',
            'estado'   => 'activo',
        ]);

        // 2. Crear perfil profesional
        $perfilDoctor = PerfilDoctor::create([
            'usuario_id'          => $usuario->id,
            'cedula_profesional'  => $data['cedula_profesional'],
            'cedula_especialidad' => $data['cedula_especialidad'] ?? null,
            'estado_validacion'   => $data['estado_validacion'] ?? 'pendiente',
        ]);

        // 3. Asignar especialidades (Many-to-Many)
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
```

**Diferencias con `AuthRepository::registrarMedico()`:**

| Aspecto | AuthRepository (auto-registro) | DoctoresRepository (admin) |
|---|---|---|
| **Quién lo invoca** | El propio médico desde la app | El administrador desde el panel |
| **Verificación de cédula** | Sí (contra mock SEP) | No (el admin se responsabiliza) |
| **Contraseña** | Requerida por el médico | Opcional (default: `Doctor1234!`) |
| **Estado validación** | Siempre `pendiente` | Configurable (puede ser `validado` directamente) |
| **Token generado** | No | No |
| **CURP** | Requerida con regex | Opcional |

**`bcrypt()` vs `Hash::make()`:** Ambos producen el mismo resultado (hash bcrypt). `bcrypt()` es un helper global de Laravel que llama internamente a `Hash::make()`. La diferencia es puramente estilística.

---

#### 6.1.3 Método `obtenerDoctor(int $id)`

```php
public function obtenerDoctor(int $id)
{
    try {
        $doctor = PerfilDoctor::with(['usuario', 'especialidades', 'horarios'])
            ->find($id);

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
```

**Nota:** Aquí se incluye la relación `'horarios'` en el eager loading, lo que no se hacía en `obtenerDoctores()`. Esto es porque al ver el detalle de un doctor individual, es útil ver también sus horarios configurados.

**`find($id)`** vs **`findOrFail($id)`:**

| Método | Si no encuentra |
|---|---|
| `find($id)` | Retorna `null` — requiere verificación manual |
| `findOrFail($id)` | Lanza `ModelNotFoundException` (404 automático) |

En este proyecto se usa `find()` con verificación manual para retornar un mensaje personalizado.

---

#### 6.1.4 Método `actualizarDoctor(int $id, array $data)`

```php
public function actualizarDoctor(int $id, array $data)
{
    try {
        $doctor = PerfilDoctor::find($id);
        if (!$doctor) {
            return ['mensaje' => 'Doctor no encontrado'];
        }

        // Actualiza datos en la tabla usuarios (a través de la relación)
        $doctor->usuario->update([
            'nombre'   => $data['nombre']   ?? $doctor->usuario->nombre,
            'email'    => $data['email']     ?? $doctor->usuario->email,
            'telefono' => $data['telefono']  ?? $doctor->usuario->telefono,
        ]);

        // Actualiza datos en la tabla perfiles_doctor
        $doctor->update([
            'cedula_profesional'  => $data['cedula_profesional']  ?? $doctor->cedula_profesional,
            'cedula_especialidad' => $data['cedula_especialidad']  ?? $doctor->cedula_especialidad,
        ]);

        // Re-sincroniza especialidades si se proporcionan
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
```

**Actualización en dos tablas:**

Este método es un ejemplo de cómo un solo endpoint puede actualizar **dos tablas simultáneamente** a través de relaciones Eloquent:

1. `$doctor->usuario->update([...])` — Actualiza la tabla `usuarios` mediante la relación `belongsTo`
2. `$doctor->update([...])` — Actualiza la tabla `perfiles_doctor` directamente

**Patrón de "merge con valores actuales":**

```php
'nombre' => $data['nombre'] ?? $doctor->usuario->nombre,
```

El operador `??` (null coalescing) funciona así: si `$data['nombre']` tiene valor, se usa ese. Si es `null` o no existe, se conserva el valor actual (`$doctor->usuario->nombre`). Esto permite **actualizaciones parciales**: el frontend solo envía los campos que quiere cambiar.

---

#### 6.1.5 Método `validarDoctor(int $id, array $data, int $adminId)`

**Propósito:** Aprobar o rechazar la cuenta de un médico. Es el método más crítico del módulo.

```php
public function validarDoctor(int $id, array $data, int $adminId)
{
    try {
        $doctor = PerfilDoctor::find($id);
        if (!$doctor) {
            return ['mensaje' => 'Doctor no encontrado'];
        }

        // 1. Actualizar estado de validación en perfiles_doctor
        $doctor->update([
            'estado_validacion' => $data['estado_validacion'],  // 'validado' o 'rechazado'
            'notas_validacion'  => $data['notas_validacion'] ?? null,
            'validado_por'      => $adminId,                    // Trazabilidad del admin
            'validado_en'       => now(),                       // Timestamp de la acción
        ]);

        // 2. Efecto cascada: actualizar estado del usuario
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
```

**Lógica de efecto cascada:**

Cuando un doctor es rechazado, no solo se marca como `rechazado` en `perfiles_doctor`, sino que **también se desactiva su cuenta** en la tabla `usuarios`:

```
estado_validacion = 'validado'  →  usuario.estado = 'activo'
                                    (puede loguearse)

estado_validacion = 'rechazado' →  usuario.estado = 'inactivo'
                                    (no puede loguearse)
```

Esto se conecta con el módulo de autenticación: cuando el middleware `CheckAccountStatus` detecta `estado = 'inactivo'`, bloquea el acceso con el mensaje "Tu cuenta está desactivada".

**Trazabilidad completa:**

Después de la validación, el registro queda así:
```php
$doctor->estado_validacion;  // "validado"
$doctor->notas_validacion;   // "Cédula verificada con el sistema SEP"
$doctor->validado_por;       // 1 (ID del admin)
$doctor->validado_en;        // "2026-07-29 17:30:00"
$doctor->validadoPor->nombre; // "Administrador Principal"
```

---

### 6.2 `EspecialidadesRepository`

**Archivo:** `app/Http/Repository/EspecialidadesRepository.php`

Repositorio sencillo para el catálogo de especialidades médicas. 3 métodos CRUD básicos.

---

#### 6.2.1 Método `obtenerEspecialidades()`

```php
public function obtenerEspecialidades()
{
    try {
        $especialidades = Especialidad::where('activa', true)->orderBy('nombre')->get();
        return [
            'mensaje' => 'Especialidades obtenidas correctamente',
            'data'    => $especialidades,
        ];
    } catch (Exception $e) {
        return ['mensaje' => $e->getMessage()];
    }
}
```

**Aspectos técnicos:**

- **`where('activa', true)`:** Filtra solo especialidades activas. Esto implementa el soft-toggle: las especialidades "eliminadas" simplemente se marcan como `activa = false` y dejan de aparecer en los listados.
- **`orderBy('nombre')`:** Ordena alfabéticamente. Esto garantiza un orden consistente para el usuario sin depender del orden de inserción.
- **`get()` vs `paginate()`:** Se usa `get()` en vez de `paginate()` porque el catálogo de especialidades es pequeño (15 registros) y no requiere paginación.

---

#### 6.2.2 Método `registrarEspecialidad(array $data)`

```php
public function registrarEspecialidad(array $data)
{
    try {
        $especialidad = Especialidad::create([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'activa'      => true,  // Siempre se crea como activa
        ]);
        return [
            'mensaje' => 'Especialidad registrada correctamente',
            'data'    => $especialidad,
        ];
    } catch (Exception $e) {
        return ['mensaje' => $e->getMessage()];
    }
}
```

**¿Qué pasa si se intenta crear una especialidad con nombre duplicado?**

Como la migración define `nombre` con `unique()`, la BD lanzará una `QueryException` que será capturada por el `catch`. Sin embargo, en el web controller se valida primero con `'nombre' => 'unique:especialidades,nombre'`, lo que mostraría un error de validación antes de llegar al repositorio.

---

#### 6.2.3 Método `obtenerEspecialidad(int $id)`

```php
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
```

Método de consulta individual. Actualmente se usa internamente; no tiene un endpoint público dedicado.

---

## 7. Capa de Form Requests (Validación)

### 7.1 `StoreDoctorRequest`

**Archivo:** `app/Http/Requests/StoreDoctorRequest.php`

Valida los datos para el registro de un doctor **desde el panel admin**.

```php
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
```

**Comparación con `StoreRegistroMedicoRequest` (auto-registro):**

| Regla | StoreDoctorRequest (Admin) | StoreRegistroMedicoRequest (Auto-registro) |
|---|---|---|
| `password` | `nullable` (default asignado en repo) | `required\|confirmed\|min:8` |
| `curp` | `nullable` (sin regex) | `required\|regex:/^[A-Z]{4}...$/` |
| `estado_validacion` | Acepta cualquier estado | No existe (siempre `pendiente`) |

**¿Por qué el admin tiene reglas más relajadas?**

Porque el administrador es un usuario **de confianza** que opera desde el panel interno. El auto-registro requiere validaciones estrictas porque es un formulario público expuesto a usuarios externos.

**`'estado_validacion' => 'nullable|in:pendiente,validado,rechazado'`:**

La regla `in:` verifica que el valor sea **exactamente** uno de los valores listados. Esto previene inyección de estados inválidos. Si se envía `estado_validacion=superadmin`, la validación fallará con un error 422.

---

### Validación inline en `EspecialidadesWebController`

El controlador web de especialidades usa **validación inline** en vez de Form Request:

```php
public function store(Request $request)
{
    $request->validate([
        'nombre'      => 'required|string|max:100|unique:especialidades,nombre',
        'descripcion' => 'nullable|string|max:255',
    ]);
    // ...
}
```

**¿Cuándo usar validación inline vs Form Request?**

| Criterio | Form Request | Validación inline |
|---|---|---|
| Complejidad | Muchas reglas (>5 campos) | Pocas reglas (2-3 campos) |
| Reutilización | Múltiples controladores usan las mismas reglas | Solo un controlador la usa |
| Mensajes custom | Se necesitan mensajes personalizados extensos | Mensajes default son suficientes |
| Testing | Más fácil de testear aisladamente | Se testea con el controlador |

En este caso, la validación de especialidades es tan simple (2 campos) que un Form Request dedicado sería over-engineering.

---

## 8. Capa de Controladores

### 8.1 `DoctoresController` (API)

**Archivo:** `app/Http/Controllers/DoctoresController.php`

Controlador API delgado con 5 métodos que delegan toda la lógica al repositorio.

```php
class DoctoresController extends Controller
{
    protected $doctoresRepository;

    public function __construct(DoctoresRepository $doctoresRepository)
    {
        $this->doctoresRepository = $doctoresRepository;
    }
```

**Tabla de métodos:**

| Método | HTTP | Autenticación | Rol | Form Request | Repositorio |
|---|---|---|---|---|---|
| `obtenerDoctores()` | GET | Pública | — | `Request` | `obtenerDoctores()` |
| `obtenerDoctor($id)` | GET | Pública | — | — | `obtenerDoctor()` |
| `registrarDoctor()` | POST | `auth:sanctum` | `admin` | `StoreDoctorRequest` | `registrarDoctor()` |
| `actualizarDoctor($id)` | PUT | `auth:sanctum` | `admin` | `Request` | `actualizarDoctor()` |
| `validarDoctor($id)` | PATCH | `auth:sanctum` | `admin` | `Request` | `validarDoctor()` |

**`obtenerDoctores()` y `obtenerDoctor()` son públicos:**

Estos dos endpoints **no requieren autenticación** porque cualquier persona (incluso sin cuenta) debe poder consultar el listado de médicos disponibles. Esto es fundamental para que los pacientes de la app móvil puedan buscar doctores antes de registrarse.

**Método `validarDoctor()` — Extracción del admin ID:**

```php
public function validarDoctor(Request $request, int $id)
{
    try {
        $adminId   = $request->user()->id;  // Obtiene el ID del admin autenticado
        $resultado = $this->doctoresRepository->validarDoctor($id, $request->all(), $adminId);
        return response()->json($resultado, 200);
    } catch (\Exception $e) {
        return response()->json(['mensaje' => $e->getMessage()], 500);
    }
}
```

`$request->user()` retorna la instancia del modelo `Usuario` del usuario autenticado (el admin, ya que la ruta está protegida por `role:admin`). Se pasa el `$adminId` al repositorio para registrar trazabilidad.

---

### 8.2 `EspecialidadesController` (API)

**Archivo:** `app/Http/Controllers/EspecialidadesController.php`

Controlador mínimo con solo 2 métodos:

```php
class EspecialidadesController extends Controller
{
    protected $especialidadesRepository;

    public function __construct(EspecialidadesRepository $especialidadesRepository)
    {
        $this->especialidadesRepository = $especialidadesRepository;
    }

    // GET /api/obtenerEspecialidades (Público)
    public function obtenerEspecialidades() { ... }

    // POST /api/registrarEspecialidad (Solo admin)
    public function registrarEspecialidad(Request $request) { ... }
}
```

---

### 8.3 `DoctoresWebController` (Web)

**Archivo:** `app/Http/Controllers/Web/DoctoresWebController.php`

Este es el controlador **más complejo** del módulo. Orquesta **4 repositorios** diferentes y gestiona doctores, horarios y bloqueos desde una sola interfaz web.

```php
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
```

**Inyección de 4 dependencias:** Este controlador necesita 4 repositorios porque la vista web de doctores integra toda la gestión (perfil, especialidades, horarios, bloqueos) en una sola interfaz. Laravel resuelve las 4 instancias automáticamente desde el Service Container.

**Tabla completa de métodos:**

| Método | HTTP | Acción | Vista/Redirect | Repositorio(s) |
|---|---|---|---|---|
| `index()` | GET | Listar doctores | `doctores.index` | `doctoresRepo` + `especialidadesRepo` |
| `store()` | POST | Crear doctor | Redirect a `index` | `doctoresRepo` |
| `update($id)` | PUT | Actualizar doctor | Redirect a `index` | `doctoresRepo` |
| `validar($id)` | PATCH | Validar/rechazar | Redirect a `index` | `doctoresRepo` |
| `horarios($id)` | GET | Ver horarios y bloqueos | `doctores.horarios` | `doctoresRepo` + `horariosRepo` + `bloqueosRepo` |
| `storeHorario($id)` | POST | Crear horario | Redirect back | `horariosRepo` |
| `updateHorario($id)` | PUT | Actualizar horario | Redirect back | `horariosRepo` |
| `deleteHorario($id)` | DELETE | Eliminar horario | Redirect back | `horariosRepo` |
| `storeBloqueo($id)` | POST | Crear bloqueo | Redirect back | `bloqueosRepo` |
| `deleteBloqueo($id)` | DELETE | Eliminar bloqueo | Redirect back | `bloqueosRepo` |

**Método `index()` — Composición de datos para la vista:**

```php
public function index()
{
    $resDoctores = $this->doctoresRepository->obtenerDoctores();
    $doctores = $resDoctores['data'] ?? [];

    $resEsp = $this->especialidadesRepository->obtenerEspecialidades();
    $especialidades = $resEsp['data'] ?? [];

    return view('doctores.index', compact('doctores', 'especialidades'));
}
```

**`compact('doctores', 'especialidades')`:** Es un shortcut de PHP que crea un array asociativo a partir de nombres de variables:
```php
compact('doctores', 'especialidades')
// Equivale a: ['doctores' => $doctores, 'especialidades' => $especialidades]
```

Las dos variables se pasan a la vista Blade para renderizar la tabla de doctores y el dropdown de especialidades del formulario de registro.

**Método `horarios($doctorId)` — Agregación de datos:**

```php
public function horarios(int $doctorId)
{
    $doctorRes   = $this->doctoresRepository->obtenerDoctor($doctorId);
    $doctor      = $doctorRes['data'] ?? null;

    $horariosRes = $this->horariosRepository->obtenerHorarios($doctorId);
    $horarios    = $horariosRes['data'] ?? [];

    $bloqueosRes = $this->bloqueosRepository->obtenerBloqueos($doctorId);
    $bloqueos    = $bloqueosRes['data'] ?? [];

    return view('doctores.horarios', compact('doctor', 'horarios', 'bloqueos', 'doctorId'));
}
```

Este método demuestra el patrón de **agregación**: combina datos de 3 repositorios diferentes para alimentar una sola vista que muestra el perfil del doctor, sus horarios regulares y sus bloqueos vigentes.

---

### 8.4 `EspecialidadesWebController` (Web)

**Archivo:** `app/Http/Controllers/Web/EspecialidadesWebController.php`

```php
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
        // Validación inline (simple, solo 2 campos)
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

**Diferencia clave:** En el `store()`, la validación se hace **inline** con `$request->validate()` en vez de usar un Form Request dedicado. Cuando la validación falla en un contexto web (no API), Laravel automáticamente redirige `back()` con los errores en la sesión flash.

---

## 9. Rutas (API y Web)

### 9.1 Rutas API

```php
// ═══════════════════════════════════════════
// RUTAS PÚBLICAS (sin autenticación)
// ═══════════════════════════════════════════
Route::get('/obtenerDoctores', [DoctoresController::class, 'obtenerDoctores']);
Route::get('/obtenerDoctor/{id}', [DoctoresController::class, 'obtenerDoctor']);
Route::get('/obtenerEspecialidades', [EspecialidadesController::class, 'obtenerEspecialidades']);

// ═══════════════════════════════════════════
// RUTAS PROTEGIDAS — Solo Admin
// ═══════════════════════════════════════════
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        // Doctores
        Route::post('/registrarDoctor', [DoctoresController::class, 'registrarDoctor']);
        Route::put('/actualizarDoctor/{id}', [DoctoresController::class, 'actualizarDoctor']);
        Route::patch('/validarDoctor/{id}', [DoctoresController::class, 'validarDoctor']);

        // Especialidades
        Route::post('/registrarEspecialidad', [EspecialidadesController::class, 'registrarEspecialidad']);
    });
});
```

**Tabla de endpoints completa:**

| Método HTTP | URL | Auth | Rol | Descripción |
|---|---|---|---|---|
| `GET` | `/api/obtenerDoctores` | Pública | — | Listar doctores (soporta filtros query) |
| `GET` | `/api/obtenerDoctor/{id}` | Pública | — | Detalle de un doctor |
| `GET` | `/api/obtenerEspecialidades` | Pública | — | Catálogo de especialidades |
| `POST` | `/api/registrarDoctor` | Sanctum | Admin | Crear doctor desde admin |
| `PUT` | `/api/actualizarDoctor/{id}` | Sanctum | Admin | Actualizar datos del doctor |
| `PATCH` | `/api/validarDoctor/{id}` | Sanctum | Admin | Aprobar/rechazar doctor |
| `POST` | `/api/registrarEspecialidad` | Sanctum | Admin | Crear nueva especialidad |

**¿Por qué `PUT` para actualizar y `PATCH` para validar?**

| Verbo | Semántica RESTful | Uso en este módulo |
|---|---|---|
| `PUT` | Reemplazo completo del recurso | `actualizarDoctor` → se puede actualizar nombre, email, cédula, especialidades |
| `PATCH` | Modificación parcial del recurso | `validarDoctor` → solo se cambia el `estado_validacion` |

---

### 9.2 Rutas Web

```php
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {

        // Doctores (CRUD + Validación)
        Route::get('/doctores',              [DoctoresWebController::class, 'index'])
             ->name('doctores.index');
        Route::post('/doctores',             [DoctoresWebController::class, 'store'])
             ->name('doctores.store');
        Route::put('/doctores/{id}',         [DoctoresWebController::class, 'update'])
             ->name('doctores.update');
        Route::patch('/doctores/{id}/validar',[DoctoresWebController::class, 'validar'])
             ->name('doctores.validar');

        // Horarios del doctor
        Route::get('/doctores/{id}/horarios', [DoctoresWebController::class, 'horarios'])
             ->name('doctores.horarios');
        Route::post('/doctores/{id}/horarios',[DoctoresWebController::class, 'storeHorario'])
             ->name('horarios.store');
        Route::put('/horarios/{id}',          [DoctoresWebController::class, 'updateHorario'])
             ->name('horarios.update');
        Route::delete('/horarios/{id}',       [DoctoresWebController::class, 'deleteHorario'])
             ->name('horarios.destroy');

        // Bloqueos del doctor
        Route::post('/doctores/{id}/bloqueos',[DoctoresWebController::class, 'storeBloqueo'])
             ->name('bloqueos.store');
        Route::delete('/bloqueos/{id}',       [DoctoresWebController::class, 'deleteBloqueo'])
             ->name('bloqueos.destroy');

        // Especialidades
        Route::get('/especialidades',        [EspecialidadesWebController::class, 'index'])
             ->name('especialidades.index');
        Route::post('/especialidades',        [EspecialidadesWebController::class, 'store'])
             ->name('especialidades.store');
    });
});
```

**Convención de named routes:**

El proyecto sigue la convención `recurso.accion`:

| Named Route | URL | Acción |
|---|---|---|
| `doctores.index` | `/doctores` | Listar |
| `doctores.store` | `/doctores` (POST) | Crear |
| `doctores.update` | `/doctores/{id}` (PUT) | Actualizar |
| `doctores.validar` | `/doctores/{id}/validar` (PATCH) | Validar |
| `doctores.horarios` | `/doctores/{id}/horarios` (GET) | Ver horarios |

**Rutas anidadas (nested resources):**

Las rutas de horarios y bloqueos están **anidadas** bajo doctores:
- `/doctores/{id}/horarios` — Los horarios pertenecen a un doctor específico
- `/doctores/{id}/bloqueos` — Los bloqueos pertenecen a un doctor específico

Pero las operaciones sobre horarios/bloqueos individuales usan su propio ID:
- `/horarios/{id}` — Actualizar/eliminar un horario específico
- `/bloqueos/{id}` — Eliminar un bloqueo específico

---

## 10. Seeders (Datos Iniciales)

### `EspecialidadesSeeder`

**Archivo:** `database/seeders/EspecialidadesSeeder.php`

Precarga 15 especialidades médicas comunes en el catálogo:

```php
$especialidades = [
    ['nombre' => 'Medicina General',    'descripcion' => 'Atención médica primaria y general.'],
    ['nombre' => 'Pediatría',           'descripcion' => 'Atención médica para niños y adolescentes.'],
    ['nombre' => 'Cardiología',         'descripcion' => 'Diagnóstico y tratamiento de enfermedades del corazón.'],
    ['nombre' => 'Dermatología',        'descripcion' => 'Tratamiento de enfermedades de la piel.'],
    ['nombre' => 'Ginecología',         'descripcion' => 'Salud reproductiva femenina.'],
    ['nombre' => 'Oftalmología',        'descripcion' => 'Diagnóstico y tratamiento de enfermedades de los ojos.'],
    ['nombre' => 'Ortopedia',           'descripcion' => 'Tratamiento del sistema músculo-esquelético.'],
    ['nombre' => 'Neurología',          'descripcion' => 'Enfermedades del sistema nervioso.'],
    ['nombre' => 'Psiquiatría',         'descripcion' => 'Salud mental y trastornos psiquiátricos.'],
    ['nombre' => 'Endocrinología',      'descripcion' => 'Enfermedades hormonales y metabólicas.'],
    ['nombre' => 'Gastroenterología',   'descripcion' => 'Enfermedades del aparato digestivo.'],
    ['nombre' => 'Urología',            'descripcion' => 'Enfermedades del aparato urinario.'],
    ['nombre' => 'Otorrinolaringología','descripcion' => 'Oídos, nariz y garganta.'],
    ['nombre' => 'Neumología',          'descripcion' => 'Enfermedades del aparato respiratorio.'],
    ['nombre' => 'Reumatología',        'descripcion' => 'Enfermedades articulares y autoinmunes.'],
];

foreach ($especialidades as $esp) {
    Especialidad::firstOrCreate(['nombre' => $esp['nombre']], $esp);
}
```

**`firstOrCreate(['nombre' => $esp['nombre']], $esp)`:**

| Parámetro | Propósito |
|---|---|
| 1° `['nombre' => ...]` | Criterio de búsqueda: ¿existe una especialidad con este nombre? |
| 2° `$esp` | Datos para crear si no existe |

Esto hace el seeder **idempotente**: si se ejecuta `php artisan db:seed` múltiples veces, no se crearán duplicados.

---

## 11. Flujos Completos de Operación

### 11.1 Flujo de Registro y Validación de Doctor (Admin)

```
                    ADMINISTRADOR
                         │
    ┌────────────────────┼────────────────────────┐
    │                    ▼                        │
    │    GET /doctores (Panel Web)                │
    │    ← Vista con tabla de doctores            │
    │      + formulario de registro               │
    │      + dropdown de especialidades           │
    │                    │                        │
    │                    ▼                        │
    │    POST /doctores                           │
    │    { nombre, email, cedula_profesional,     │
    │      especialidades: [1, 3] }               │
    │                    │                        │
    │         ┌──────────┼──────────┐             │
    │         ▼                     ▼             │
    │  StoreDoctorRequest     DoctoresRepository  │
    │  • email unique         • Usuario::create   │
    │  • cédula unique        • PerfilDoctor::create
    │  • espec. exists        • sync(especialidades)
    │         │                     │             │
    │         └──────────┬──────────┘             │
    │                    ▼                        │
    │    Estado: estado_validacion = 'pendiente'  │
    │    El doctor existe pero NO puede loguearse │
    │    (AuthRepository verifica estado_validacion│
    │     en el método login)                     │
    │                    │                        │
    │                    ▼                        │
    │    PATCH /doctores/{id}/validar             │
    │    { estado_validacion: 'validado',         │
    │      notas_validacion: 'Verificado' }       │
    │                    │                        │
    │         ┌──────────┼──────────┐             │
    │         ▼                     ▼             │
    │  DoctoresRepository    Efecto cascada:      │
    │  • estado_validacion   • usuario.estado     │
    │    = 'validado'          = 'activo'         │
    │  • validado_por = 1    (o 'inactivo' si     │
    │  • validado_en = now()  rechazado)          │
    │         └──────────┬──────────┘             │
    │                    ▼                        │
    │    Estado: Doctor puede loguearse ✅         │
    └─────────────────────────────────────────────┘
```

---

### 11.2 Flujo de Consulta Pública de Doctores (App Móvil)

```
    PACIENTE (Sin autenticación)
         │
         │  GET /api/obtenerDoctores?especialidad_id=3&buscar=López
         │
         ▼
    ┌───────────────────────────────────────────┐
    │ DoctoresController::obtenerDoctores()     │
    │ → DoctoresRepository::obtenerDoctores()   │
    │                                           │
    │ Query construido dinámicamente:            │
    │ PerfilDoctor::with(['usuario','espec.'])  │
    │   ->whereHas('especialidades', id=3)      │
    │   ->whereHas('usuario', nombre LIKE López)│
    │   ->paginate(15)                          │
    └───────────────────┬───────────────────────┘
                        │
                        ▼
    JSON Response (200):
    {
      "mensaje": "Doctores obtenidos correctamente",
      "data": {
        "data": [
          {
            "id": 1,
            "cedula_profesional": "1234567",
            "estado_validacion": "validado",
            "usuario": {
              "id": 2,
              "nombre": "Dr. Juan Carlos López",
              "email": "dr.lopez@email.com"
            },
            "especialidades": [
              { "id": 3, "nombre": "Cardiología" }
            ]
          }
        ],
        "current_page": 1,
        "total": 1,
        "per_page": 15
      }
    }
```

---

### 11.3 Flujo de Gestión de Horarios desde el Panel de Doctores

```
    GET /doctores/{id}/horarios
         │
         ▼
    DoctoresWebController::horarios()
    ├── doctoresRepo->obtenerDoctor(id)    → Datos del doctor
    ├── horariosRepo->obtenerHorarios(id)  → Horarios regulares
    └── bloqueosRepo->obtenerBloqueos(id)  → Bloqueos vigentes
         │
         ▼
    Vista: doctores.horarios
    ┌──────────────────────────────────────────┐
    │  Dr. Juan Carlos López                   │
    │  Cardiología, Medicina General           │
    │                                          │
    │  ┌── Horarios Regulares ──────────────┐  │
    │  │ Lunes    08:00 - 14:00             │  │
    │  │ Miércoles 09:00 - 13:00            │  │
    │  │ [+ Agregar] [Editar] [Eliminar]    │  │
    │  └────────────────────────────────────┘  │
    │                                          │
    │  ┌── Bloqueos ────────────────────────┐  │
    │  │ 01/08/2026 - 15/08/2026            │  │
    │  │ Motivo: Vacaciones                 │  │
    │  │ [+ Agregar] [Eliminar]             │  │
    │  └────────────────────────────────────┘  │
    └──────────────────────────────────────────┘
```

---

## 12. Relación con Otros Módulos

```
                    ┌──────────────────────┐
                    │  Mod 1: Autenticación│
                    │  AuthRepository      │
                    │  registrarMedico()   │──── Auto-registro público
                    └──────────┬───────────┘    con verificación de cédula
                               │
            Comparte: Modelo Usuario, middleware role:admin
                               │
                    ┌──────────▼───────────┐
                    │ Mod 2: DOCTORES      │
                    │ DoctoresRepository   │
                    │ registrarDoctor()    │──── Registro desde admin
                    │ validarDoctor()      │──── Habilita/deshabilita login
                    └──────────┬───────────┘
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
    ┌─────▼──────┐    ┌───────▼────────┐   ┌──────▼───────┐
    │ Mod 4:     │    │ Mod 5: Citas   │   │ Mod 7: Notas │
    │ Horarios   │    │ CitasRepository│   │ Diagnóstico  │
    │ Bloqueos   │    │ Usa doctor_id  │   │ Usa doctor_id│
    │ Disponib.  │    │ + especialidad │   │ por cita     │
    └────────────┘    └────────────────┘   └──────────────┘
```

---

## Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DoctoresController.php              # API: CRUD doctores + validación
│   │   │   ├── EspecialidadesController.php         # API: Catálogo de especialidades
│   │   │   └── Web/
│   │   │       ├── DoctoresWebController.php        # Web: Doctores + horarios + bloqueos
│   │   │       └── EspecialidadesWebController.php  # Web: Catálogo de especialidades
│   │   ├── Repository/
│   │   │   ├── DoctoresRepository.php               # Lógica CRUD + validación de doctores
│   │   │   └── EspecialidadesRepository.php          # Lógica del catálogo de especialidades
│   │   └── Requests/
│   │       └── StoreDoctorRequest.php                # Validación registro doctor (admin)
│   └── Models/
│       ├── PerfilDoctor.php                          # Perfil profesional (6 relaciones)
│       └── Especialidad.php                          # Catálogo de especialidades
├── database/
│   ├── migrations/
│   │   ├── ..._crear_tabla_especialidades.php        # Catálogo maestro
│   │   ├── ..._crear_tabla_perfiles_doctor.php       # Perfil con validación y trazabilidad
│   │   └── ..._crear_tabla_doctor_especialidad.php   # Tabla pivot M:N
│   └── seeders/
│       └── EspecialidadesSeeder.php                   # 15 especialidades precargadas
├── resources/views/
│   ├── doctores/
│   │   ├── index.blade.php                            # Lista + CRUD + validación
│   │   └── horarios.blade.php                         # Gestión horarios + bloqueos
│   └── especialidades/
│       └── index.blade.php                            # Catálogo CRUD
└── routes/
    ├── api.php                                        # Endpoints públicos + admin
    └── web.php                                        # Rutas panel admin
```

---

> **Módulo anterior:** [01 - Autenticación y Seguridad](./01-Autenticacion-y-Seguridad.md)
> **Siguiente módulo:** [03 - Especialidades](./03-Especialidades.md) *(integrado en este documento)*
> **Siguiente módulo funcional:** [04 - Horarios y Bloqueos](./04-Horarios-y-Bloqueos.md)
