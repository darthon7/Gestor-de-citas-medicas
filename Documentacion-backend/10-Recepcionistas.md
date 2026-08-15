# 🖥️ Módulo 10: Recepcionistas

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura del Módulo](#2-diagrama-de-arquitectura-del-módulo)
3. [Modelo de Datos Relacional](#3-modelo-de-datos-relacional)
4. [Capa de Base de Datos — Migraciones](#4-capa-de-base-de-datos--migraciones)
5. [Capa de Modelos (Eloquent ORM)](#5-capa-de-modelos-eloquent-orm)
6. [Capa de Repositorios (Lógica de Negocio y Trazabilidad)](#6-capa-de-repositorios-lógica-de-negocio-y-trazabilidad)
7. [Capa de Validaciones (Form Requests)](#7-capa-de-validaciones-form-requests)
8. [Capa de Controladores (Web SSR)](#8-capa-de-controladores-web-ssr)
9. [Capa de Vistas (Blade SSR UI y Componentes)](#9-capa-de-vistas-blade-ssr-ui-y-componentes)
10. [Rutas (API y Web)](#10-rutas-api-y-web)
11. [Flujos Completos de Operación](#11-flujos-completos-de-operación)
12. [Relación con Otros Módulos](#12-relación-con-otros-módulos)
13. [Mapa de Archivos del Módulo](#13-mapa-de-archivos-del-módulo)

---

## 1. Visión General del Módulo

El módulo de **Recepcionistas** administra las cuentas del personal operativo encargado de la recepción de la clínica médica. Este personal gestiona el flujo físico de llegada de los pacientes, el agendamiento telefónico/presencial y el registro de check-in antes de la atención con el médico.

### Responsabilidades Principales

| Responsabilidad | Descripción Técnica |
|---|---|
| **Alta Administrativa de Personal** | Permitir a los administradores registrar cuentas de usuario con rol `recepcionista`. |
| **Asignación Operativa** | Registrar el número de empleado, unidad clínica o módulo asignado y turno laboral (`matutino`, `vespertino`, `nocturno`). |
| **Auditoría de Creación** | Guardar la identidad del administrador que dio de alta la cuenta (`creado_por_admin_id`). |
| **Control de Operaciones Presenciales** | Habilitar permisos para agendar citas, consultar la lista de pacientes del día y realizar el check-in. |

### Roles que Interactúan con este Módulo

| Rol | Permisos y Operaciones |
|---|---|
| **Administrador** | Crear cuentas de recepcionista, ver la nómina de personal de recepción y gestionar su estado. |
| **Recepcionista** | Iniciar sesión en el portal web para operar la agenda del centro médico. |

---

## 2. Diagrama de Arquitectura del Módulo

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PETICIÓN HTTP                                       │
│                Web SSR (/recepcionistas, /recepcionistas.store)                        │
└───────────────────────────────────────────┬────────────────────────────────────────────┘
                                            │
                                            ▼
                               ┌──────────────────────────┐
                               │ RecepcionistasWebController
                               │   (Blade SSR + Session)  │
                               └────────────┬─────────────┘
                                            │
                                            ▼
                               ┌──────────────────────────┐
                               │StoreRegistroRecepcionistaRequest
                               │ (Validación de Datos)    │
                               └────────────┬─────────────┘
                                            │
                                            ▼
                               ┌──────────────────────────┐
                               │      AuthRepository      │
                               │ • registrarRecepcionista()│
                               └────────────┬─────────────┘
                                            │
                      ┌─────────────────────┴─────────────────────┐
                      ▼                                           ▼
        ┌───────────────────────────┐               ┌───────────────────────────┐
        │      Modelo Usuario       │               │  PerfilRecepcionista      │
        │  ($table = 'usuarios')    │               │  ($table='perfiles_r..')  │
        └─────────────┬─────────────┘               └─────────────┬─────────────┘
                      │                                           │
                      └─────────────────────┬─────────────────────┘
                                            │
                                            ▼
                            ┌────────────────────────────────┐
                            │         BASE DE DATOS          │
                            │   [usuarios] [perfiles_recep]  │
                            └────────────────────────────────┘
```

---

## 3. Modelo de Datos Relacional

El modelo vincula la tabla `usuarios` (cuenta e identidad) con `perfiles_recepcionista` mediante una relación **One-to-One** estricta, y registra qué usuario administrador dio de alta la cuenta.

```
┌─────────────────────────┐             ┌─────────────────────────┐
│        usuarios         │             │  perfiles_recepcionista │
│─────────────────────────│             │─────────────────────────│
│ id (PK)                 │◄────────────│ usuario_id (FK UNIQUE)  │
│ nombre                  │ 1:1         │ numero_empleado (VARCHAR│
│ email (UNIQUE)          │             │ unidad_asignada (VARCHAR│
│ password (HASH)         │             │ turno (VARCHAR)         │
│ curp (UNIQUE NULLABLE)  │             │ creado_por_admin_id (FK)┼───┐
│ telefono                │             │ timestamps              │   │
│ rol = 'recepcionista'   │             └─────────────────────────┘   │
│ estado                  │                                           │ 1:N (Inversa)
│ timestamps              │◄──────────────────────────────────────────┘ (Admin Creador)
└─────────────────────────┘
```

---

## 4. Capa de Base de Datos — Migraciones

**Archivo:** `database/migrations/2026_01_01_000005_crear_tabla_perfiles_recepcionista.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles_recepcionista', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('numero_empleado')->nullable();
            $table->string('unidad_asignada')->nullable();
            $table->string('turno')->nullable();
            $table->foreignId('creado_por_admin_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles_recepcionista');
    }
};
```

### Análisis Técnico de Columnas de `perfiles_recepcionista`

| Columna | Tipo SQL | Constraint / Index | Propósito y Comportamiento |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `PRIMARY KEY AUTO_INCREMENT` | Identificador único del perfil de recepción. |
| `usuario_id` | `BIGINT UNSIGNED` | `FK constrained('usuarios') onDelete('cascade')` | Relación con la cuenta base del usuario. |
| `numero_empleado` | `VARCHAR(255)` | `NULLABLE` | Código o número de nómina interno del empleado. |
| `unidad_asignada` | `VARCHAR(255)` | `NULLABLE` | Nombre del centro médico, clínica o piso asignado. |
| `turno` | `VARCHAR(255)` | `NULLABLE` | Turno de trabajo (`matutino`, `vespertino`, `nocturno`). |
| `creado_por_admin_id` | `BIGINT UNSIGNED` | `FK constrained('usuarios') onDelete('set null')` | Auditoría de qué administrador autorizó la alta. |

---

## 5. Capa de Modelos (Eloquent ORM)

**Archivo:** `app/Models/PerfilRecepcionista.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilRecepcionista extends Model
{
    use HasFactory;

    protected $table = 'perfiles_recepcionista';

    protected $fillable = [
        'usuario_id',
        'numero_empleado',
        'unidad_asignada',
        'turno',
        'creado_por_admin_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por_admin_id');
    }
}
```

---

## 6. Capa de Repositorios (Lógica de Negocio y Trazabilidad)

**Archivo:** `app/Http/Repository/AuthRepository.php` (Método de Alta de Recepcionista)

```php
public function registrarRecepcionista(array $data, int $adminId)
{
    try {
        // 1. Crear usuario base con rol 'recepcionista'
        $usuario = Usuario::create([
            'nombre'   => $data['nombre'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'curp'     => isset($data['curp']) ? strtoupper($data['curp']) : null,
            'telefono' => $data['telefono'] ?? null,
            'rol'      => 'recepcionista',
            'estado'   => 'activo',
        ]);

        // 2. Crear sub-perfil operativo asignando trazabilidad de admin
        PerfilRecepcionista::create([
            'usuario_id'          => $usuario->id,
            'numero_empleado'     => $data['numero_empleado'] ?? null,
            'unidad_asignada'     => $data['unidad_asignada'] ?? null,
            'turno'               => $data['turno'] ?? null,
            'creado_por_admin_id' => $adminId,
        ]);

        return [
            'mensaje' => 'Recepcionista registrada correctamente',
            'usuario' => $usuario->load('perfilRecepcionista'),
        ];
    } catch (Exception $e) {
        return ['mensaje' => $e->getMessage()];
    }
}
```

---

## 7. Capa de Validaciones (Form Requests)

**Archivo:** `app/Http/Requests/StoreRegistroRecepcionistaRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRegistroRecepcionistaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'          => 'required|string|max:255',
            'email'           => 'required|email|unique:usuarios,email',
            'password'        => 'required|string|min:8|confirmed',
            'curp'            => 'nullable|string|size:18',
            'telefono'        => 'nullable|string|max:20',
            'numero_empleado' => 'nullable|string|max:50',
            'unidad_asignada' => 'nullable|string|max:255',
            'turno'           => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre completo es requerido.',
            'email.required'     => 'El correo electrónico es requerido.',
            'email.unique'       => 'Este correo electrónico ya está registrado.',
            'password.required'  => 'La contraseña es requerida.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
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

## 8. Capa de Controladores (Web SSR)

**Archivo:** `app/Http/Controllers/Web/RecepcionistasWebController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\AuthRepository;
use App\Http\Requests\StoreRegistroRecepcionistaRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;

class RecepcionistasWebController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function index()
    {
        $recepcionistas = Usuario::where('rol', 'recepcionista')->orderBy('id', 'desc')->get();
        return view('recepcionistas.index', compact('recepcionistas'));
    }

    public function store(StoreRegistroRecepcionistaRequest $request)
    {
        try {
            $adminId = $request->user()->id;
            $this->authRepository->registrarRecepcionista($request->all(), $adminId);
            return redirect()->route('recepcionistas.index')->with('success', 'Recepcionista registrada con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
```

---

## 9. Capa de Vistas (Blade SSR UI y Componentes)

**Archivo:** `resources/views/recepcionistas/index.blade.php`

```html
@extends('layouts.app')
@section('titulo', 'Gestión de Recepcionistas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Gestión de Recepcionistas</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_recep">
        <i data-lucide="user-plus" class="me-1"></i> + Registrar Recepcionista
    </button>
</div>

<p class="text-secondary mb-4">Personal administrativo con permisos para agendar citas y gestionar pacientes.</p>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nombre Completo</th>
                        <th>Correo Institucional</th>
                        <th>Teléfono / CURP</th>
                        <th class="text-end pe-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recepcionistas as $recep)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $recep->nombre }}</td>
                            <td>{{ $recep->email }}</td>
                            <td class="small text-secondary">{{ $recep->telefono ?? 'N/A' }} / <span class="font-monospace">{{ $recep->curp ?? 'N/A' }}</span></td>
                            <td class="text-end pe-3">
                                <span class="badge {{ $recep->estado === 'activo' ? 'bg-success' : 'bg-danger' }} text-capitalize">
                                    {{ $recep->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay recepcionistas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Registro Recepcionista Bootstrap 5 -->
<div class="modal fade" id="modal_recep" tabindex="-1" aria-labelledby="modal_recep_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modal_recep_title">Registrar Nueva Recepcionista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('recepcionistas.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="txt_nombre_recep" class="form-label fw-medium">Nombre Completo *</label>
                        <input type="text" id="txt_nombre_recep" name="nombre" class="form-control" placeholder="María López Hernández" required>
                    </div>

                    <div class="mb-3">
                        <label for="txt_email_recep" class="form-label fw-medium">Correo Institucional *</label>
                        <input type="email" id="txt_email_recep" name="email" class="form-control" placeholder="recepcion@clinicamedica.com" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="txt_curp_recep" class="form-label fw-medium">CURP *</label>
                            <input type="text" id="txt_curp_recep" name="curp" class="form-control text-uppercase" placeholder="18 caracteres" maxlength="18" required>
                        </div>
                        <div class="col-6">
                            <label for="txt_tel_recep" class="form-label fw-medium">Teléfono *</label>
                            <input type="tel" id="txt_tel_recep" name="telefono" class="form-control" placeholder="10 dígitos" maxlength="10" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="txt_pass_recep" class="form-label fw-medium">Contraseña Inicial *</label>
                        <input type="password" id="txt_pass_recep" name="password" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
                    </div>

                    <div class="mb-3">
                        <label for="txt_pass_conf_recep" class="form-label fw-medium">Confirmar Contraseña *</label>
                        <input type="password" id="txt_pass_conf_recep" name="password_confirmation" class="form-control" placeholder="Repetir contraseña" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Cuenta</button>
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
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/registrarRecepcionista', [AuthController::class, 'registrarRecepcionista']);
    });
});
```

### 10.2 Rutas Web (`routes/web.php`)

```php
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/recepcionistas', [RecepcionistasWebController::class, 'index'])->name('recepcionistas.index');
        Route::post('/recepcionistas', [RecepcionistasWebController::class, 'store'])->name('recepcionistas.store');
    });
});
```

---

## 11. Flujos Completos de Operación

### 11.1 Registro de Cuenta de Recepcionista por Administrador

```
   ADMINISTRADOR (PANEL WEB)                RecepcionistaWebController               AuthRepository                          BASE DE DATOS
            │                                           │                                   │                                              │
            │ POST /recepcionistas                      │                                   │                                              │
            │ (nombre, email, pass, curp, tel)          │                                   │                                              │
            ├──────────────────────────────────────────►│                                   │                                              │
            │                                           │ StoreRegistroRecepcionistaReq     │                                              │
            │                                           │ (Valida email unique, pass conf)  │                                              │
            │                                           ├──────────────────────────────────►│                                              │
            │                                           │                                   │ 1. Usuario::create([rol => 'recepcionista']) │
            │                                           │                                   ├─────────────────────────────────────────────►│
            │                                           │                                   │◄─────────────────────────────────────────────┤
            │                                           │                                   │ 2. PerfilRecepcionista::create([             │
            │                                           │                                   │    creado_por_admin_id => $adminId           │
            │                                           │                                   │ ])                                           │
            │                                           │                                   ├─────────────────────────────────────────────►│
            │                                           │                                   │◄─────────────────────────────────────────────┤
            │ 302 Redirect (/recepcionistas)            │◄──────────────────────────────────┤                                              │
            │ + Session Flash: "Registrada con éxito"   │                                                                                  │
            │◄──────────────────────────────────────────┤                                                                                  │
```

---

## 12. Relación con Otros Módulos

```
                               ┌──────────────────────────┐
                               │  Módulo 10: GESTIÓN DE   │
                               │      RECEPCIONISTAS      │
                               └────────────┬─────────────┘
                                            │
         ┌───────────────────────┬──────────┴────────────┬────────────────────────┐
         ▼                       ▼                       ▼                        ▼
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────────┐
│ Mod 1: Auth y    │  │ Mod 5: Gestión de│  │ Mod 6: Pacientes │  │ Mod 8: Perfil de     │
│ Seguridad        │  │ Citas            │  │                  │  │ Usuario              │
│ Creación Cuentas │  │ Check-In Citas   │  │ Registro Público │  │ Sub-perfil Recepcion.│
└──────────────────┘  └──────────────────┘  └──────────────────┘  └──────────────────────┘
```

---

## 13. Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Web/
│   │   │       └── RecepcionistasWebController.php # Controller Web SSR Recepcionistas
│   │   ├── Repository/
│   │   │   └── AuthRepository.php                  # Lógica de alta de recepcionistas
│   │   └── Requests/
│   │       └── StoreRegistroRecepcionistaRequest.php# Validaciones de Alta de Recepcionista
│   └── Models/
│       └── PerfilRecepcionista.php                 # Modelo Eloquent perfiles_recepcionista
├── database/
│   └── migrations/
│       └── 2026_01_01_000005_crear_tabla_perfiles_recepcionista.php # Migración tabla perfiles_recepcionista
├── resources/views/
│   └── recepcionistas/
│       └── index.blade.php                         # Vista Tabla de Recepcionistas + Modal Bootstrap
└── routes/
    ├── api.php                                     # Endpoint API REST (/api/registrarRecepcionista)
    └── web.php                                     # Rutas Web SSR (/recepcionistas)
```

---

> **Módulo anterior:** [09 - Reportes y Estadísticas](./09-Reportes-y-Estadisticas.md)  
> **Inicio de Documentación:** [01 - Autenticación y Seguridad](./01-Autenticacion-y-Seguridad.md)
