# 🧑‍🤝‍🧑 Módulo 6: Gestión de Pacientes

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura del Módulo](#2-diagrama-de-arquitectura-del-módulo)
3. [Modelo de Datos Relacional](#3-modelo-de-datos-relacional)
4. [Capa de Base de Datos — Migraciones](#4-capa-de-base-de-datos--migraciones)
5. [Capa de Modelos (Eloquent ORM)](#5-capa-de-modelos-eloquent-orm)
6. [Capa de Repositorios (Lógica de Negocio y Restricciones)](#6-capa-de-repositorios-lógica-de-negocio-y-restricciones)
7. [Capa de Validaciones (Form Requests)](#7-capa-de-validaciones-form-requests)
8. [Capa de Controladores (API REST vs Blade SSR)](#8-capa-de-controladores-api-rest-vs-blade-ssr)
9. [Capa de Vistas (Blade SSR UI y Componentes)](#9-capa-de-vistas-blade-ssr-ui-y-componentes)
10. [Rutas (API y Web)](#10-rutas-api-y-web)
11. [Flujos Completos de Operación](#11-flujos-completos-de-operación)
12. [Relación con Otros Módulos](#12-relación-con-otros-módulos)
13. [Mapa de Archivos del Módulo](#13-mapa-de-archivos-del-módulo)

---

## 1. Visión General del Módulo

El módulo de **Gestión de Pacientes** administra la información clínica, demográfica y de contacto de todos los usuarios registrados como pacientes en el sistema. Su objetivo central es organizar el expediente médico del paciente y controlar su historial de consultas.

### Responsabilidades Principales

| Responsabilidad | Descripción Técnica |
|---|---|
| **Generación de Expediente Único** | Asignar automáticamente un número de expediente formateado (`EXP-YYYYMMDD-ID`) en el momento de la creación de la cuenta. |
| **Administración Demográfica** | Registrar fecha de nacimiento, sexo, CURP, NSS (Número de Seguro Social), dirección y teléfonos de contacto de emergencia. |
| **Búsqueda Multicriterio** | Permitir la localización rápida de pacientes por Nombre, CURP o Número de Expediente. |
| **Protección contra Desactivación** | Impedir la desactivación lógica de la cuenta de un paciente si tiene citas médicas activas pendientes (`agendada`, `confirmada` o `en_consulta`). |
| **Historial de Consultas** | Proveer la vista agregada de todas las citas agendadas, atendidas y notas de consulta vinculadas a su perfil. |

### Roles que Interactúan con este Módulo

| Rol | Permisos y Operaciones |
|---|---|
| **Administrador / Recepcionista** | Crear expedientes, editar datos demográficos, consultar perfiles completos y realizar desactivaciones. |
| **Médico** | Consultar la ficha técnica del paciente al iniciar una consulta o revisar su historial clínico previo. |
| **Paciente** | Actualizar su dirección y teléfonos de contacto desde la aplicación móvil o portal web. |

---

## 2. Diagrama de Arquitectura del Módulo

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PETICIÓN HTTP                                       │
│          API REST (/api/obtenerPacientes)       │      Web SSR (/pacientes)            │
└───────────────────────────┬───────────────────────────────┬────────────────────────────┘
                            │                               │
                            ▼                               ▼
               ┌──────────────────────────┐    ┌──────────────────────────────┐
               │   PacientesController    │    │    PacientesWebController    │
               │        (API JSON)        │    │     (Blade SSR + Session)    │
               └────────────┬─────────────┘    └──────────────┬───────────────┘
                            │                                 │
                            │   ┌─────────────────────────┐   │
                            └──►│      Form Requests      │◄──┘
                                │ (StorePaciente/Update..)│
                                └────────────┬────────────┘
                                             │
                                             ▼
                             ┌────────────────────────────────┐
                             │      PacientesRepository       │
                             │  • obtenerPacientes()          │
                             │  • registrarPaciente()         │
                             │  • obtenerPaciente()           │
                             │  • actualizarPaciente()        │
                             │  • desactivarPaciente()        │
                             └───────────────┬────────────────┘
                                             │
                       ┌─────────────────────┴─────────────────────┐
                       ▼                                           ▼
         ┌───────────────────────────┐               ┌───────────────────────────┐
         │      Modelo Usuario       │               │   Modelo PerfilPaciente   │
         │  ($table = 'usuarios')    │               │  ($table = 'perfiles_p..')│
         └─────────────┬─────────────┘               └─────────────┬─────────────┘
                       │                                           │
                       └─────────────────────┬─────────────────────┘
                                             │
                                             ▼
                             ┌────────────────────────────────┐
                             │         BASE DE DATOS          │
                             │    [usuarios]  [perfiles_p..]  │
                             └────────────────────────────────┘
```

---

## 3. Modelo de Datos Relacional

El paciente se representa mediante una relación **One-to-One** estricta entre las tablas `usuarios` (credenciales e identidad base) y `perfiles_paciente` (expediente clínico y datos demográficos), la cual a su vez se conecta **One-to-Many** con la tabla `citas`.

```
┌─────────────────────────┐             ┌─────────────────────────┐             ┌─────────────────────────┐
│        usuarios         │             │    perfiles_paciente    │             │          citas          │
│─────────────────────────│             │─────────────────────────│             │─────────────────────────│
│ id (PK)                 │◄────────────│ usuario_id (FK UNIQUE)  │             │ id (PK)                 │
│ nombre                  │ 1:1         │ numero_expediente (UNIQ)│◄────────────│ perfil_paciente_id (FK) │
│ email (UNIQUE)          │             │ fecha_nacimiento (DATE) │ 1:N         │ fecha_cita              │
│ curp (UNIQUE NULLABLE)  │             │ sexo (ENUM M/F)         │             │ hora_cita               │
│ telefono                │             │ direccion (TEXT)        │             │ estado                  │
│ rol = 'paciente'        │             │ contacto_emergencia_nom │             │ ...                     │
│ estado (activo/inactivo)│             │ contacto_emergencia_tel │             └─────────────────────────┘
│ timestamps              │             │ nss (VARCHAR NULLABLE)  │
└─────────────────────────┘             │ timestamps              │
                                        └─────────────────────────┘
```

---

## 4. Capa de Base de Datos — Migraciones

**Archivo:** `database/migrations/2026_01_01_000004_crear_tabla_perfiles_paciente.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles_paciente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('numero_expediente')->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->text('direccion')->nullable();
            $table->string('contacto_emergencia_nombre')->nullable();
            $table->string('contacto_emergencia_telefono', 20)->nullable();
            $table->string('nss', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles_paciente');
    }
};
```

### Análisis Técnico de Columnas de `perfiles_paciente`

| Columna | Tipo SQL | Constraint / Index | Propósito y Comportamiento |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `PRIMARY KEY AUTO_INCREMENT` | Identificador autoincremental del perfil de paciente. |
| `usuario_id` | `BIGINT UNSIGNED` | `FK constrained('usuarios') onDelete('cascade')` | Relación con la cuenta base de usuario. Si se elimina el usuario, se elimina el perfil de paciente. |
| `numero_expediente` | `VARCHAR(255)` | `UNIQUE INDEX` | Código único de expediente del paciente (ej. `EXP-20260729-0004`). |
| `fecha_nacimiento` | `DATE` | `NULLABLE` | Fecha de nacimiento para cálculo de edad del paciente. |
| `sexo` | `ENUM` | `['M', 'F'] NULLABLE` | Sexo biológico del paciente. |
| `direccion` | `TEXT` | `NULLABLE` | Dirección domiciliaria del paciente. |
| `contacto_emergencia_nombre` | `VARCHAR(255)` | `NULLABLE` | Nombre de la persona de contacto en caso de emergencia. |
| `contacto_emergencia_telefono`| `VARCHAR(20)` | `NULLABLE` | Teléfono de la persona de contacto de emergencia. |
| `nss` | `VARCHAR(20)` | `NULLABLE` | Número de Seguro Social o identificador de póliza médica. |

---

## 5. Capa de Modelos (Eloquent ORM)

**Archivo:** `app/Models/PerfilPaciente.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilPaciente extends Model
{
    use HasFactory;

    protected $table = 'perfiles_paciente';

    protected $fillable = [
        'usuario_id',
        'numero_expediente',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'nss',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'perfil_paciente_id');
    }
}
```

---

## 6. Capa de Repositorios (Lógica de Negocio y Restricciones)

**Archivo:** `app/Http/Repository/PacientesRepository.php`

Encapsula la búsqueda multicriterio, la generación de número de expediente y la desactivación protegida.

```php
<?php

namespace App\Http\Repository;

use App\Models\PerfilPaciente;
use App\Models\Usuario;
use Exception;

class PacientesRepository
{
    /**
     * Búsqueda multicriterio de pacientes (Nombre, CURP o Expediente).
     */
    public function obtenerPacientes(array $filtros = [])
    {
        try {
            $query = Usuario::with('perfilPaciente')
                ->where('rol', 'paciente');

            if (!empty($filtros['buscar'])) {
                $buscar = $filtros['buscar'];
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%$buscar%")
                        ->orWhere('curp', 'like', "%$buscar%")
                        ->orWhereHas('perfilPaciente', function ($q2) use ($buscar) {
                            $q2->where('numero_expediente', 'like', "%$buscar%");
                        });
                });
            }

            if (!empty($filtros['estado'])) {
                $query->where('estado', $filtros['estado']);
            }

            $pacientes = $query->paginate($filtros['por_pagina'] ?? 15);

            return [
                'mensaje' => 'Pacientes obtenidos correctamente',
                'data'    => $pacientes,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Registra el usuario y perfil del paciente asignando número de expediente.
     */
    public function registrarPaciente(array $data)
    {
        try {
            $usuario = Usuario::create([
                'nombre'   => $data['nombre'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
                'curp'     => strtoupper($data['curp']),
                'telefono' => $data['telefono'] ?? null,
                'rol'      => 'paciente',
                'estado'   => 'activo',
            ]);

            // Generación de expediente automático: EXP-20260729-0004
            $numeroExpediente = 'EXP-' . now()->format('Ymd') . '-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT);

            PerfilPaciente::create([
                'usuario_id'                   => $usuario->id,
                'numero_expediente'            => $numeroExpediente,
                'fecha_nacimiento'             => $data['fecha_nacimiento'] ?? null,
                'sexo'                         => $data['sexo'] ?? null,
                'direccion'                    => $data['direccion'] ?? null,
                'contacto_emergencia_nombre'   => $data['contacto_emergencia_nombre'] ?? null,
                'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? null,
                'nss'                          => $data['nss'] ?? null,
            ]);

            return [
                'mensaje' => 'Paciente registrado correctamente',
                'data'    => $usuario->load('perfilPaciente'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function obtenerPaciente(int $id)
    {
        try {
            $usuario = Usuario::with([
                'perfilPaciente.citas.notaConsulta', 
                'perfilPaciente.citas.perfilDoctor.usuario', 
                'perfilPaciente.citas.especialidad'
            ])
            ->where('rol', 'paciente')
            ->find($id);

            if (!$usuario) {
                return ['mensaje' => 'Paciente no encontrado'];
            }

            return [
                'mensaje' => 'Paciente obtenido correctamente',
                'data'    => $usuario,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function actualizarPaciente(int $id, array $data)
    {
        try {
            $usuario = Usuario::where('rol', 'paciente')->find($id);
            if (!$usuario) {
                return ['mensaje' => 'Paciente no encontrado'];
            }

            $usuario->update([
                'nombre'   => $data['nombre']   ?? $usuario->nombre,
                'email'    => $data['email']     ?? $usuario->email,
                'telefono' => $data['telefono']  ?? $usuario->telefono,
                'curp'     => isset($data['curp']) ? strtoupper($data['curp']) : $usuario->curp,
            ]);

            if ($usuario->perfilPaciente) {
                $usuario->perfilPaciente->update([
                    'fecha_nacimiento'             => $data['fecha_nacimiento']             ?? $usuario->perfilPaciente->fecha_nacimiento,
                    'sexo'                         => $data['sexo']                         ?? $usuario->perfilPaciente->sexo,
                    'direccion'                    => $data['direccion']                    ?? $usuario->perfilPaciente->direccion,
                    'contacto_emergencia_nombre'   => $data['contacto_emergencia_nombre']   ?? $usuario->perfilPaciente->contacto_emergencia_nombre,
                    'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? $usuario->perfilPaciente->contacto_emergencia_telefono,
                    'nss'                          => $data['nss']                          ?? $usuario->perfilPaciente->nss,
                ]);
            }

            return [
                'mensaje' => 'Paciente actualizado correctamente',
                'data'    => $usuario->load('perfilPaciente'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Regla de Negocio: No se puede desactivar a un paciente con citas activas pendientes.
     */
    public function desactivarPaciente(int $id)
    {
        try {
            $usuario = Usuario::where('rol', 'paciente')->find($id);
            if (!$usuario) {
                return ['mensaje' => 'Paciente no encontrado'];
            }

            $citasActivas = $usuario->perfilPaciente?->citas()
                ->whereIn('estado', ['agendada', 'confirmada', 'en_consulta'])
                ->count();

            if ($citasActivas > 0) {
                return ['mensaje' => 'No se puede desactivar un paciente con citas activas pendientes.'];
            }

            $usuario->update(['estado' => 'inactivo']);

            return ['mensaje' => 'Paciente desactivado correctamente'];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
```

---

## 7. Capa de Validaciones (Form Requests)

### 7.1 `UpdatePacienteRequest`

**Archivo:** `app/Http/Requests/UpdatePacienteRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePacienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'                       => 'sometimes|string|max:255',
            'telefono'                     => 'sometimes|nullable|string|max:20',
            'direccion'                    => 'sometimes|nullable|string',
            'contacto_emergencia_nombre'   => 'sometimes|nullable|string|max:255',
            'contacto_emergencia_telefono' => 'sometimes|nullable|string|max:20',
            'fecha_nacimiento'             => 'sometimes|nullable|date',
            'sexo'                         => 'sometimes|nullable|in:M,F',
            'nss'                          => 'sometimes|nullable|string|max:20',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'msj'    => 'Error de validación',
            'errors' => $validator->errors(),
        ], 422));
    }
}
```

---

## 8. Capa de Controladores (API REST vs Blade SSR)

### 8.1 Controlador API (`PacientesController`)

**Archivo:** `app/Http/Controllers/PacientesController.php`

Maneja los endpoints RESTful protegidos para la administración de pacientes.

```php
<?php

namespace App\Http\Controllers;

use App\Http\Repository\PacientesRepository;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use Illuminate\Http\Request;

class PacientesController extends Controller
{
    protected $pacientesRepository;

    public function __construct(PacientesRepository $pacientesRepository)
    {
        $this->pacientesRepository = $pacientesRepository;
    }

    public function obtenerPacientes(Request $request)
    {
        try {
            $resultado = $this->pacientesRepository->obtenerPacientes($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function registrarPaciente(StorePacienteRequest $request)
    {
        try {
            $resultado = $this->pacientesRepository->registrarPaciente($request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function obtenerPaciente(int $id)
    {
        try {
            $resultado = $this->pacientesRepository->obtenerPaciente($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function actualizarPaciente(UpdatePacienteRequest $request, int $id)
    {
        try {
            $resultado = $this->pacientesRepository->actualizarPaciente($id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function desactivarPaciente(int $id)
    {
        try {
            $resultado = $this->pacientesRepository->desactivarPaciente($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
```

---

### 8.2 Controlador Web (`PacientesWebController`)

**Archivo:** `app/Http/Controllers/Web/PacientesWebController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\PacientesRepository;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use Illuminate\Http\Request;

class PacientesWebController extends Controller
{
    protected $pacientesRepository;

    public function __construct(PacientesRepository $pacientesRepository)
    {
        $this->pacientesRepository = $pacientesRepository;
    }

    public function index(Request $request)
    {
        $query = $request->query('buscar');
        $pacientes = \App\Models\PerfilPaciente::with('usuario')
            ->when($query, function ($q) use ($query) {
                $q->whereHas('usuario', function ($u) use ($query) {
                    $u->where('nombre', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('curp', 'like', "%{$query}%")
                        ->orWhere('telefono', 'like', "%{$query}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pacientes.index', compact('pacientes', 'query'));
    }

    public function store(StorePacienteRequest $request)
    {
        try {
            $this->pacientesRepository->registrarPaciente($request->all());
            return redirect()->route('pacientes.index')->with('success', 'Paciente registrado correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(int $id)
    {
        try {
            $paciente = \App\Models\PerfilPaciente::with(['usuario', 'citas.perfilDoctor.usuario', 'citas.especialidad'])->findOrFail($id);
            return view('pacientes.perfil', compact('paciente'));
        } catch (\Exception $e) {
            return redirect()->route('pacientes.index')->with('error', 'Paciente no encontrado.');
        }
    }

    public function update(UpdatePacienteRequest $request, int $id)
    {
        try {
            $this->pacientesRepository->actualizarPaciente($id, $request->all());
            return redirect()->route('pacientes.index')->with('success', 'Paciente actualizado con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function desactivar(int $id)
    {
        try {
            $this->pacientesRepository->desactivarPaciente($id);
            return redirect()->route('pacientes.index')->with('success', 'Paciente desactivado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

---

## 9. Capa de Vistas (Blade SSR UI y Componentes)

### 9.1 Tabla y Modal de Pacientes (`resources/views/pacientes/index.blade.php`)

Ofrece búsqueda en vivo y un modal dinámico reutilizable para crear y editar datos de pacientes usando funciones de JavaScript nativas (`prepararNuevoPaciente` y `editarPaciente`).

```html
@extends('layouts.app')
@section('titulo', 'Gestión de Pacientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Gestión de Pacientes</h1>
</div>

<!-- Controls Bar -->
<div class="row g-3 mb-4 align-items-center">
    <div class="col-md-6 col-lg-5">
        <form method="GET" action="{{ route('pacientes.index') }}" class="input-group">
            <span class="input-group-text bg-white border-end-0"><i data-lucide="search" class="text-muted"></i></span>
            <input type="text" name="buscar" value="{{ $query }}" class="form-control border-start-0 ps-0" placeholder="Buscar por nombre, CURP o expediente...">
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
        </form>
    </div>
    <div class="col-md-6 col-lg-7 text-md-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_paciente" onclick="prepararNuevoPaciente()">
            <i data-lucide="user-plus" class="me-1"></i> + Nuevo Paciente
        </button>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"># Expediente</th>
                        <th>Nombre Completo</th>
                        <th>CURP</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pacientes as $paciente)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">{{ $paciente->numero_expediente ?? 'EXP-' . str_pad($paciente->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="fw-semibold">{{ $paciente->usuario?->nombre ?? 'N/A' }}</td>
                            <td class="font-monospace small">{{ $paciente->usuario?->curp ?? 'N/A' }}</td>
                            <td>{{ $paciente->usuario?->telefono ?? 'N/A' }}</td>
                            <td>{{ $paciente->usuario?->email ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $estado = strtolower($paciente->usuario?->estado ?? 'activo');
                                    $badgeClass = match($estado) {
                                        'activo' => 'bg-success',
                                        'inactivo' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} text-capitalize">{{ $estado }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('pacientes.show', $paciente->id) }}" class="btn btn-outline-secondary" title="Ver Perfil">
                                        <i data-lucide="eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-primary" title="Editar" onclick="editarPaciente({{ json_encode([
                                        'id' => $paciente->id,
                                        'nombre' => $paciente->usuario?->nombre,
                                        'fecha_nacimiento' => $paciente->fecha_nacimiento,
                                        'sexo' => $paciente->sexo,
                                        'curp' => $paciente->usuario?->curp,
                                        'telefono' => $paciente->usuario?->telefono,
                                        'email' => $paciente->usuario?->email,
                                        'direccion' => $paciente->direccion
                                    ]) }})">
                                        <i data-lucide="pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('pacientes.desactivar', $paciente->id) }}" onsubmit="return confirm('¿Está seguro de que desea desactivar este paciente?');" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-danger" title="Desactivar">
                                            <i data-lucide="user-x"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se encontraron pacientes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

---

## 10. Rutas (API y Web)

### 10.1 Rutas API (`routes/api.php`)

```php
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::middleware(['role:admin,recepcionista'])->group(function () {
        Route::get('/obtenerPacientes', [PacientesController::class, 'obtenerPacientes']);
        Route::post('/registrarPaciente', [PacientesController::class, 'registrarPaciente']);
        Route::get('/obtenerPaciente/{id}', [PacientesController::class, 'obtenerPaciente']);
        Route::put('/actualizarPaciente/{id}', [PacientesController::class, 'actualizarPaciente']);
        Route::patch('/desactivarPaciente/{id}', [PacientesController::class, 'desactivarPaciente']);
    });
});
```

### 10.2 Rutas Web (`routes/web.php`)

```php
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::middleware(['role:admin,recepcionista'])->group(function () {
        Route::get('/pacientes', [PacientesWebController::class, 'index'])->name('pacientes.index');
        Route::post('/pacientes', [PacientesWebController::class, 'store'])->name('pacientes.store');
        Route::get('/pacientes/{id}', [PacientesWebController::class, 'show'])->name('pacientes.show');
        Route::put('/pacientes/{id}', [PacientesWebController::class, 'update'])->name('pacientes.update');
        Route::patch('/pacientes/{id}/desactivar', [PacientesWebController::class, 'desactivar'])->name('pacientes.desactivar');
    });
});
```

---

## 11. Flujos Completos de Operación

### 11.1 Registro de Paciente en Clínica y Asignación de Expediente

```
   RECEPCIONISTA (WEB)                    PacientesWebController                 PacientesRepository                     BASE DE DATOS
            │                                        │                                    │                                           │
            │ POST /pacientes                        │                                    │                                           │
            │ (nombre, email, curp, fecha_nac...)    │                                    │                                           │
            ├───────────────────────────────────────►│                                    │                                           │
            │                                        │ StorePacienteRequest               │                                           │
            │                                        │ (Valida CURP 18, email unique...) │                                           │
            │                                        ├───────────────────────────────────►│                                           │
            │                                        │                                    │ 1. Usuario::create([rol => 'paciente'])   │
            │                                        │                                    ├──────────────────────────────────────────►│
            │                                        │                                    │◄──────────────────────────────────────────┤
            │                                        │                                    │ 2. Formatear Expediente:                  │
            │                                        │                                    │    EXP-YYYYMMDD-0004                      │
            │                                        │                                    │ 3. PerfilPaciente::create(...)            │
            │                                        │                                    ├──────────────────────────────────────────►│
            │                                        │                                    │◄──────────────────────────────────────────┤
            │                                        │                                    │                                           │
            │ 302 Redirect (/pacientes)              │◄───────────────────────────────────┤                                           │
            │ + Session Flash "Paciente registrado"  │                                                                                │
            │◄───────────────────────────────────────┤                                                                                │
```

---

## 12. Relación con Otros Módulos

```
                               ┌──────────────────────────┐
                               │   Módulo 6: GESTIÓN DE   │
                               │        PACIENTES         │
                               └────────────┬─────────────┘
                                            │
         ┌───────────────────────┬──────────┴────────────┬────────────────────────┐
         ▼                       ▼                       ▼                        ▼
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────────┐
│ Mod 1: Auth y    │  │ Mod 5: Gestión de│  │ Mod 7: Notas de  │  │ Mod 9: Reportes y    │
│ Seguridad        │  │ Citas            │  │ Consulta         │  │ Estadísticas         │
│ Modelo Usuario   │  │ FK Paciente      │  │ Historial Clínico│  │ Demografía Pacientes │
└──────────────────┘  └──────────────────┘  └──────────────────┘  └──────────────────────┘
```

---

## 13. Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PacientesController.php             # Controller API REST Pacientes
│   │   │   └── Web/
│   │   │       └── PacientesWebController.php       # Controller Web SSR Pacientes
│   │   ├── Repository/
│   │   │   └── PacientesRepository.php             # Lógica de expedientes, búsquedas y desactivación
│   │   └── Requests/
│   │       ├── StorePacienteRequest.php            # Validaciones de Registro
│   │       └── UpdatePacienteRequest.php           # Validaciones de Edición
│   └── Models/
│       └── PerfilPaciente.php                      # Modelo Eloquent perfiles_paciente
├── database/
│   └── migrations/
│       └── 2026_01_01_000004_crear_tabla_perfiles_paciente.php # Migración tabla perfiles_paciente
├── resources/views/
│   └── pacientes/
│       ├── index.blade.php                         # Listado, búsqueda y Modal CRUD
│       └── perfil.blade.php                        # Ficha del Expediente e Historial
└── routes/
    ├── api.php                                     # Endpoints API REST (/api/obtenerPacientes, etc.)
    └── web.php                                     # Rutas Web SSR (/pacientes, etc.)
```

---

> **Módulo anterior:** [05 - Gestión de Citas](./05-Gestion-de-Citas.md)  
> **Siguiente módulo:** [07 - Notas de Consulta](./07-Notas-de-Consulta.md)
