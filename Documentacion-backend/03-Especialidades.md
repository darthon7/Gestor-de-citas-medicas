# 🏥 Módulo 3: Catálogo de Especialidades

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura del Módulo](#2-diagrama-de-arquitectura-del-módulo)
3. [Modelo de Datos Relacional](#3-modelo-de-datos-relacional)
4. [Capa de Base de Datos — Migraciones](#4-capa-de-base-de-datos--migraciones)
5. [Capa de Modelos (Eloquent ORM)](#5-capa-de-modelos-eloquent-orm)
6. [Capa de Repositorios (Lógica de Negocio)](#6-capa-de-repositorios-lógica-de-negocio)
7. [Capa de Validaciones (Form Requests y Validación Inline)](#7-capa-de-validaciones-form-requests-y-validación-inline)
8. [Capa de Controladores (API REST vs Blade SSR)](#8-capa-de-controladores-api-rest-vs-blade-ssr)
9. [Capa de Vistas (Blade SSR UI y Componentes)](#9-capa-de-vistas-blade-ssr-ui-y-componentes)
10. [Rutas (API y Web)](#10-rutas-api-y-web)
11. [Seeders (Datos Iniciales y Carga Idempotente)](#11-seeders-datos-iniciales-y-carga-idempotente)
12. [Flujos Completos de Operación](#12-flujos-completos-de-operación)
13. [Relación con Otros Módulos](#13-relación-con-otros-módulos)
14. [Mapa de Archivos del Módulo](#14-mapa-de-archivos-del-módulo)

---

## 1. Visión General del Módulo

El módulo de **Catálogo de Especialidades** es el componente maestro encargado de administrar el inventario centralizado de ramas de la medicina disponibles dentro de la institución médica. Funciona como una entidad de referencia (Lookup Entity / Master Data) indispensable para la operación de múltiplessubsistemas.

### Responsabilidades Principales

| Responsabilidad | Descripción Técnica |
|---|---|
| **Gestión del Catálogo Maestro** | Almacenar, organizar y servir la lista de especialidades médicas (ej. Medicina General, Pediatría, Cardiología). |
| **Control de Visibilidad Lógica** | Habilitar/Deshabilitar especialidades mediante un flag booleano (`activa`) sin destruir integridad referencial. |
| **Mapeo con Perfiles Médicos** | Actuar como entidad pivote Many-to-Many con `perfiles_doctor` para definir qué disciplinas puede atender un médico. |
| **Enrutamiento de Demanda Médica** | Servir como criterio primario de filtrado para la búsqueda de disponibilidades y agendamiento de citas de pacientes. |
| **Agregación Estadísticas** | Agrupar métricas operativas y generar reportes analíticos de consultas desglosadas por rama médica. |

### Estrategia de Acceso Dual (API vs Web)

Al igual que en los módulos anteriores, el catálogo de especialidades expone dos vías de consumo:
- **API REST (Pública / Protegida):** Proporciona un endpoint público (`GET /api/obtenerEspecialidades`) consumible por aplicaciones móviles o SPA frontend sin requerir autenticación, permitiendo a los pacientes consultar las ramas médicas disponibles. Además, incluye un endpoint administrativo (`POST /api/registrarEspecialidad`) protegido por Sanctum token y verificación de rol de administrador.
- **Web SSR (Panel Administrativo):** Proporciona interfaces basadas en Laravel Blade (`GET /especialidades`) protegidas por sesión para que los administradores gestionen el catálogo mediante modales interactivos y renderizado desde el servidor.

---

## 2. Diagrama de Arquitectura del Módulo

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PETICIÓN HTTP                                       │
│          API REST (/api/obtenerEspecialidades)   │   Web SSR (/especialidades)             │
└───────────────────────────┬───────────────────────────────┬────────────────────────────┘
                            │                               │
                            ▼                               ▼
               ┌──────────────────────────┐    ┌──────────────────────────────┐
               │ EspecialidadesController │    │ EspecialidadesWebController  │
               │        (API JSON)        │    │     (Blade SSR + Session)    │
               └────────────┬─────────────┘    └──────────────┬───────────────┘
                            │                                 │
                            │   ┌─────────────────────────┐   │
                            └──►│   Validación Inline     │◄──┘
                                │ (validate / rules)      │
                                └────────────┬────────────┘
                                             │
                                             ▼
                             ┌────────────────────────────────┐
                             │    EspecialidadesRepository    │
                             │  • obtenerEspecialidades()     │
                             │  • registrarEspecialidad()     │
                             │  • obtenerEspecialidad()       │
                             └───────────────┬────────────────┘
                                             │
                                             ▼
                             ┌────────────────────────────────┐
                             │      Modelo Especialidad       │
                             │  - $table = 'especialidades'   │
                             │  - Relación: doctores() (M:N)  │
                             │  - Relación: citas() (1:N)     │
                             └───────────────┬────────────────┘
                                             │
                                             ▼
                             ┌────────────────────────────────┐
                             │     Tabla: especialidades      │
                             │       (Base de Datos)          │
                             └────────────────────────────────┘
```

---

## 3. Modelo de Datos Relacional

El módulo se centra en la tabla `especialidades`, que se conecta con la tabla `perfiles_doctor` mediante una tabla de unión Many-to-Many (`doctor_especialidad`) y con la tabla `citas` mediante una relación One-to-Many.

```
┌─────────────────────┐       ┌────────────────────────┐       ┌─────────────────────────┐
│   perfiles_doctor   │       │   doctor_especialidad  │       │     especialidades      │
│─────────────────────│       │   (Tabla Pivote M:N)   │       │─────────────────────────│
│ id (PK)             │◄──┐   │────────────────────────│   ┌──►│ id (PK)                 │
│ usuario_id (FK)     │   └───│ perfil_doctor_id (FK)  │   │   │ nombre (VARCHAR UNIQUE) │
│ cedula_profesional  │       │ especialidad_id (FK)   │───┘   │ descripcion (TEXT NULL) │
│ ...                 │       │ PRIMARY KEY(ambas FKs) │       │ activa (BOOLEAN DEFAULT)│
└─────────────────────┘       └────────────────────────┘       │ timestamps              │
                                                               └────────────┬────────────┘
                                                                            │
                                                                            │ 1:N
                                                                            ▼
                                                               ┌─────────────────────────┐
                                                               │          citas          │
                                                               │─────────────────────────│
                                                               │ id (PK)                 │
                                                               │ perfil_doctor_id (FK)   │
                                                               │ perfil_paciente_id (FK) │
                                                               │ especialidad_id (FK) ───┘
                                                               │ fecha_cita              │
                                                               │ ...                     │
                                                               └─────────────────────────┘
```

---

## 4. Capa de Base de Datos — Migraciones

### 4.1 Tabla `especialidades`

**Archivo:** `database/migrations/2026_01_01_000002_crear_tabla_especialidades.php`

Esta migración crea el catálogo maestro de especialidades médicas.

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
            $table->id();                                              // Clave Primaria Autoincremental
            $table->string('nombre')->unique();                        // Nombre oficial (Índice UNIQUE)
            $table->text('descripcion')->nullable();                   // Descripción detallada opcional
            $table->boolean('activa')->default(true);                  // Estado de activación (Soft Toggle)
            $table->timestamps();                                      // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidades');
    }
};
```

### Análisis Técnico Detallado de Columnas y Restricciones

| Columna | Tipo de Dato SQL | Restricción / Constraint | Razón de Diseño y Comportamiento |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `PRIMARY KEY AUTO_INCREMENT` | Identificador único numérico para la especialidad. Utilizado como FK en relaciones. |
| `nombre` | `VARCHAR(255)` | `UNIQUE INDEX` | Garantiza a nivel de motor de base de datos que no existan nombres duplicados (evitando por ejemplo dos registros para "Pediatría"). Genera un índice B-Tree implícito que acelera búsquedas. |
| `descripcion` | `TEXT` | `NULLABLE` | Almacena una explicación larga de la rama médica. Se elige `TEXT` en lugar de `VARCHAR` para permitir textos explicativos extensos sin límite de 255 caracteres. |
| `activa` | `TINYINT(1)` / `BOOLEAN` | `DEFAULT 1` | Bandera booleana de activación lógica. En lugar de eliminar registros (lo cual causaría fallos de FK o registros huérfanos en citas históricas), se marca `activa = 0`. |
| `timestamps` | `TIMESTAMP` | `NULLABLE` | Manejo automático por Eloquent para auditoría de fecha de creación (`created_at`) y última modificación (`updated_at`). |

---

### 4.2 Tabla Pivote `doctor_especialidad`

**Archivo:** `database/migrations/2026_01_01_000006_crear_tabla_doctor_especialidad.php`

Esta migración crea la relación de unión Many-to-Many entre los médicos y las especialidades.

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

### Aspectos Clave de la Tabla Pivote:
1. **Clave Primaria Compuesta:** `primary(['perfil_doctor_id', 'especialidad_id'])` asegura que un médico no pueda tener vinculada exactamente la misma especialidad más de una vez.
2. **Eliminación en Cascada (`onDelete('cascade')`):** Si se elimina un registro de `especialidades` o de `perfiles_doctor`, la fila de unión en la tabla pivote se elimina automáticamente en la base de datos sin requerir intervención manual.

---

## 5. Capa de Modelos (Eloquent ORM)

**Archivo:** `app/Models/Especialidad.php`

El modelo `Especialidad` representa cada registro de la tabla `especialidades` y expone métodos de consulta y relaciones de Eloquent.

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
     * Relación Muchos a Muchos con PerfilDoctor
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

### Explicación Código por Código del Modelo

- **`protected $table = 'especialidades';`**  
  Especifica explícitamente el nombre de la tabla en base de datos.
- **`protected $fillable = ['nombre', 'descripcion', 'activa'];`**  
  Array de asignación masiva (*Mass Assignment*). Protege contra la inyección no deseada de campos restringidos durante llamadas como `Especialidad::create($request->all())`.
- **`protected $casts = ['activa' => 'boolean'];`**  
  Convierte automáticamente el atributo `activa` guardado en la base de datos como entero (`1` o `0`) a un valor booleano nativo en PHP (`true` o `false`).
- **`public function doctores()`**  
  Define la relación `belongsToMany` hacia `PerfilDoctor`. Indica que la tabla pivote es `'doctor_especialidad'`, donde la clave foránea del modelo actual es `'especialidad_id'` y la clave foránea del modelo a relacionar es `'perfil_doctor_id'`.
- **`public function citas()`**  
  Define la relación `hasMany` hacia la entidad `Cita`, relacionando la especialidad requerida para la consulta médica.

---

## 6. Capa de Repositorios (Lógica de Negocio)

**Archivo:** `app/Http/Repository/EspecialidadesRepository.php`

Encapsula la lógica de acceso a datos para las especialidades, desacoplando los controladores de las consultas directas de Eloquent ORM.

```php
<?php

namespace App\Http\Repository;

use App\Models\Especialidad;
use Exception;

class EspecialidadesRepository
{
    /**
     * Obtiene el listado de especialidades activas ordenadas alfabéticamente.
     *
     * @return array
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
     * Registra una nueva especialidad médica en el catálogo.
     *
     * @param array $data
     * @return array
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
     * Busca y obtiene una especialidad médica por su ID.
     *
     * @param int $id
     * @return array
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

### Análisis Detallado de Métodos del Repositorio

#### 1. `obtenerEspecialidades()`
- **Filtro de Estado:** Realiza `where('activa', true)` para asegurar que solo se retornen especialidades operativas.
- **Ordenamiento:** Aplica `orderBy('nombre')` para devolver la lista ordenada de forma alfabética (A-Z), facilitando el consumo tanto en combos HTML Web como en listados de apps móviles.
- **Respuesta Estructurada:** Retorna un array asociativo conteniendo un mensaje y la colección de objetos `Especialidad`.

#### 2. `registrarEspecialidad(array $data)`
- **Asignación de Campos:** Toma el campo `nombre` y `descripcion` del parámetro `$data`.
- **Estado Inicial:** Asigna por defecto `'activa' => true` para garantizar que la nueva especialidad esté disponible inmediatamente.
- **Manejo de Excepciones:** Envuelto en un bloque `try/catch` para capturar cualquier fallo a nivel de BD (por ejemplo, si se viola el índice de unicidad en `nombre`).

#### 3. `obtenerEspecialidad(int $id)`
- **Búsqueda por PK:** Utiliza `Especialidad::find($id)`.
- **Verificación de Existencia:** Si la especialidad no existe en BD, retorna una respuesta indicando `'Especialidad no encontrada'`.

---

## 7. Capa de Validaciones (Form Requests y Validación Inline)

En el módulo de especialidades, la validación de datos se maneja de dos formas según el canal de entrada:

### 7.1 Validación Inline en Controlador Web (`EspecialidadesWebController`)

Dado que el registro web de especialidades consta de solo 2 campos (`nombre` y `descripcion`), se implementa una **validación inline** limpia mediante el método `$request->validate()`:

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

### Explicación de Reglas Aplicadas:

| Campo | Reglas | Explicación Técnica |
|---|---|---|
| `nombre` | `required` | El parámetro no puede estar vacío ni ser `null`. |
| | `string` | Debe ser una cadena de texto. |
| | `max:100` | Limita la longitud máxima a 100 caracteres. |
| | `unique:especialidades,nombre` | Consulta la tabla `especialidades` en la columna `nombre` para asegurar que no exista otro registro idéntico. |
| `descripcion` | `nullable` | El campo es opcional. |
| | `string` | Si se envía, debe ser de tipo texto. |
| | `max:255` | Limita la longitud a 255 caracteres. |

---

## 8. Capa de Controladores (API REST vs Blade SSR)

### 8.1 Controlador API (`EspecialidadesController`)

**Archivo:** `app/Http/Controllers/EspecialidadesController.php`

Encargado de exponer los endpoints en formato JSON para consumo desde aplicaciones clientes (API REST).

```php
<?php

namespace App\Http\Controllers;

use App\Http\Repository\EspecialidadesRepository;
use Illuminate\Http\Request;

class EspecialidadesController extends Controller
{
    protected $especialidadesRepository;

    /**
     * Inyección de dependencia del repositorio de especialidades.
     */
    public function __construct(EspecialidadesRepository $especialidadesRepository)
    {
        $this->especialidadesRepository = $especialidadesRepository;
    }

    /**
     * Endpoint API para consultar el catálogo de especialidades.
     * GET /api/obtenerEspecialidades
     */
    public function obtenerEspecialidades()
    {
        try {
            $resultado = $this->especialidadesRepository->obtenerEspecialidades();
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint API para registrar una nueva especialidad médica.
     * POST /api/registrarEspecialidad
     */
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

### 8.2 Controlador Web (`EspecialidadesWebController`)

**Archivo:** `app/Http/Controllers/Web/EspecialidadesWebController.php`

Encargado de la interacción SSR (Server-Side Rendering) con el panel web Blade.

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

    /**
     * Muestra la vista con el catálogo de especialidades.
     * GET /especialidades
     */
    public function index()
    {
        $res = $this->especialidadesRepository->obtenerEspecialidades();
        $especialidades = $res['data'] ?? [];

        return view('especialidades.index', compact('especialidades'));
    }

    /**
     * Procesa la creación de una nueva especialidad desde el modal web.
     * POST /especialidades
     */
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

**Archivo:** `resources/views/especialidades/index.blade.php`

Representa la vista del catálogo de especialidades del panel de administración. Incluye la tabla de listado y un modal nativo de Bootstrap 5 para el registro de nuevas especialidades.

```html
@extends('layouts.app')
@section('titulo', 'Catálogo de Especialidades')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Catálogo de Especialidades</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_especialidad">
        <i data-lucide="plus" class="me-1"></i> Nueva Especialidad
    </button>
</div>

<p class="text-secondary mb-4">Especialidades configuradas para los servicios del centro médico.</p>

<div class="card border-0 shadow-sm rounded-3" style="max-width: 840px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"># ID</th>
                        <th>Nombre de la Especialidad</th>
                        <th>Descripción</th>
                        <th class="text-end pe-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($especialidades as $esp)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">#{{ $esp['id'] }}</td>
                            <td class="fw-semibold">{{ $esp['nombre'] }}</td>
                            <td class="text-secondary small">{{ $esp['descripcion'] ?? 'Sin descripción' }}</td>
                            <td class="text-end pe-3">
                                <span class="badge bg-success">Activo</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay especialidades registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nueva Especialidad Nativo Bootstrap 5 -->
<div class="modal fade" id="modal_especialidad" tabindex="-1" aria-labelledby="modal_esp_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modal_esp_title">Nueva Especialidad Médica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('especialidades.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="txt_nombre_esp" class="form-label fw-medium">Nombre de la Especialidad *</label>
                        <input type="text" id="txt_nombre_esp" name="nombre" class="form-control" placeholder="Ej: Cardiología, Pediatría" required>
                    </div>
                    <div class="mb-3">
                        <label for="txt_desc_esp" class="form-label fw-medium">Descripción</label>
                        <input type="text" id="txt_desc_esp" name="descripcion" class="form-control" placeholder="Descripción opcional...">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

## 10. Rutas (API y Web)

### 10.1 Rutas API (`routes/api.php`)

```php
// Endpoint público: Cualquier cliente puede consultar las especialidades activas
Route::get('/obtenerEspecialidades', [EspecialidadesController::class, 'obtenerEspecialidades']);

// Endpoints protegidos por token Sanctum, verificación de cuenta activa y rol Admin
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/registrarEspecialidad', [EspecialidadesController::class, 'registrarEspecialidad']);
    });
});
```

### Tabla de Endpoints API REST

| Método HTTP | Endpoint API | Autenticación | Rol Requerido | Descripción de Función |
|---|---|---|---|---|
| `GET` | `/api/obtenerEspecialidades` | Pública | Ninguno | Obtener listado de especialidades activas ordenadas alfabéticamente. |
| `POST` | `/api/registrarEspecialidad` | Bearer Token (`auth:sanctum`) | `admin` | Crear y registrar una nueva especialidad médica en el sistema. |

---

### 10.2 Rutas Web (`routes/web.php`)

```php
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        // Vista principal del catálogo
        Route::get('/especialidades', [EspecialidadesWebController::class, 'index'])
            ->name('especialidades.index');

        // Procesamiento del formulario de creación
        Route::post('/especialidades', [EspecialidadesWebController::class, 'store'])
            ->name('especialidades.store');
    });
});
```

### Tabla de Rutas Web (Blade SSR)

| Método HTTP | Ruta Web | Named Route | Middleware | Descripción de Función |
|---|---|---|---|---|
| `GET` | `/especialidades` | `especialidades.index` | `auth`, `check.status`, `role:admin` | Renderizar vista principal del catálogo con tabla de especialidades. |
| `POST` | `/especialidades` | `especialidades.store` | `auth`, `check.status`, `role:admin` | Procesar el formulario enviando los datos al repositorio para crear la especialidad. |

---

## 11. Seeders (Datos Iniciales y Carga Idempotente)

**Archivo:** `database/seeders/EspecialidadesSeeder.php`

Precarga 15 especialidades médicas predeterminadas al inicializar el sistema mediante `php artisan db:seed`. Utiliza `firstOrCreate` para asegurar la idempotencia (ejecuciones repetidas no generan duplicados).

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

### 12.1 Flujo de Consulta Pública de Especialidades (API REST)

```
   CLIENTE MÓVIL / FRONTEND                      API GATEWAY                              CONTROLLER / REPO                      BASE DE DATOS
              │                                      │                                           │                                     │
              │ GET /api/obtenerEspecialidades       │                                           │                                     │
              ├─────────────────────────────────────►│                                           │                                     │
              │                                      │ Petición Pública                          │                                     │
              │                                      ├──────────────────────────────────────────►│                                     │
              │                                      │                                           │ EspecialidadesRepository            │
              │                                      │                                           │ ::obtenerEspecialidades()           │
              │                                      │                                           ├────────────────────────────────────►│
              │                                      │                                           │                                     │
              │                                      │                                           │ SELECT * FROM especialidades        │
              │                                      │                                           │ WHERE activa = 1 ORDER BY nombre    │
              │                                      │                                           │◄────────────────────────────────────┤
              │                                      │                                           │                                     │
              │                                      │ JSON 200 OK                               │                                     │
              │◄─────────────────────────────────────┴───────────────────────────────────────────┤                                     │
              │ {                                                                                │                                     │
              │   "mensaje": "Especialidades obtenidas...",                                      │                                     │
              │   "data": [                                                                      │                                     │
              │     {"id": 3, "nombre": "Cardiología", "activa": true},                          │                                     │
              │     {"id": 1, "nombre": "Medicina General", "activa": true}                      │                                     │
              │   ]                                                                              │                                     │
              │ }                                                                                │                                     │
```

---

### 12.2 Flujo de Registro de Nueva Especialidad (Web Admin)

```
   ADMINISTRADOR (BROWSER)                  EspecialidadesWebController                  EspecialidadesRepository                BASE DE DATOS
              │                                          │                                           │                                 │
              │ 1. Clic en "Nueva Especialidad"          │                                           │                                 │
              │    (Abre Modal Bootstrap #modal_esp)     │                                           │                                 │
              │ 2. Llenar "Cardiología" + Guardar        │                                           │                                 │
              │ 3. POST /especialidades                  │                                           │                                 │
              ├─────────────────────────────────────────►│                                           │                                 │
              │                                          │ $request->validate()                      │                                 │
              │                                          ├──────────────────────────┐                │                                 │
              │                                          │ Validar Nombre Único     │                │                                 │
              │                                          │◄─────────────────────────┘                │                                 │
              │                                          │                                           │                                 │
              │                                          │ registrarEspecialidad($data)              │                                 │
              │                                          ├──────────────────────────────────────────►│                                 │
              │                                          │                                           │ Especialidad::create()          │
              │                                          │                                           ├────────────────────────────────►│
              │                                          │                                           │ INSERT INTO especialidades...   │
              │                                          │                                           │◄────────────────────────────────┤
              │                                          │                                           │                                 │
              │                                          │◄──────────────────────────────────────────┤                                 │
              │ 302 Redirect (/especialidades)           │                                           │                                 │
              │ + Flash Session: "Especialidad creada..."│                                           │                                 │
              │◄─────────────────────────────────────────┤                                           │                                 │
```

---

## 13. Relación con Otros Módulos

```
                               ┌──────────────────────────┐
                               │   Módulo 3: CATÁLOGO DE  │
                               │      ESPECIALIDADES      │
                               └────────────┬─────────────┘
                                            │
           ┌────────────────────────────────┼────────────────────────────────┐
           │                                │                                │
           ▼                                ▼                                ▼
┌──────────────────────┐        ┌──────────────────────┐        ┌──────────────────────┐
│ Mod 2: Doctores      │        │ Mod 4: Horarios y    │        │ Mod 5: Gestión de    │
│                      │        │        Disponibilidad│        │        Citas         │
│ Relación M:N         │        │ Filtrado por         │        │ FK especialidad_id   │
│ (doctor_especialidad)│        │ Especialidad         │        │ en la cita médica    │
└──────────────────────┘        └──────────────────────┘        └──────────────────────┘
```

- **Módulo 2 (Gestión de Doctores):** Relación de tabla pivote Many-to-Many (`doctor_especialidad`). Permite asociar múltiples especialidades a un perfil médico.
- **Módulo 4 (Horarios y Disponibilidad):** Permite filtrar los doctores disponibles según la especialidad seleccionada por el paciente en la aplicación móvil o portal web.
- **Módulo 5 (Gestión de Citas):** Guarda la referencia `especialidad_id` directamente en la tabla `citas` para asociar la consulta solicitada con el área médica correspondiente.
- **Módulo 9 (Reportes y Estadísticas):** Genera analíticas agrupadas sobre el volumen de citas agendadas por especialidad.

---

## 14. Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── EspecialidadesController.php        # Controlador API REST (JSON)
│   │   │   └── Web/
│   │   │       └── EspecialidadesWebController.php  # Controlador Web SSR (Blade)
│   │   └── Repository/
│   │       └── EspecialidadesRepository.php        # Repositorio de Lógica de Negocio
│   └── Models/
│       └── Especialidad.php                        # Modelo Eloquent y Relaciones
├── database/
│   ├── migrations/
│   │   ├── 2026_01_01_000002_crear_tabla_especialidades.php   # Migración Tabla Especialidades
│   │   └── 2026_01_01_000006_crear_tabla_doctor_especialidad.php# Migración Tabla Pivote
│   └── seeders/
│       └── EspecialidadesSeeder.php                # Seeder Idempotente (15 especialidades)
├── resources/views/
│   └── especialidades/
│       └── index.blade.php                         # Interfaz UI Blade + Tabla + Modal Bootstrap
└── routes/
    ├── api.php                                     # Rutas API (/api/obtenerEspecialidades, etc.)
    └── web.php                                     # Rutas Web (/especialidades)
```

---

> **Módulo anterior:** [02 - Gestión de Doctores](./02-Gestion-de-Doctores.md)  
> **Siguiente módulo:** [04 - Horarios y Bloqueos](./04-Horarios-y-Bloqueos.md)
