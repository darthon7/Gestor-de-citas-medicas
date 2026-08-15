# 👤 Módulo 8: Perfil de Usuario

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura del Módulo](#2-diagrama-de-arquitectura-del-módulo)
3. [Modelo de Datos Relacional](#3-modelo-de-datos-relacional)
4. [Capa de Base de Datos y Almacenamiento (Storage)](#4-capa-de-base-de-datos-y-almacenamiento-storage)
5. [Capa de Modelos (Eloquent ORM)](#5-capa-de-modelos-eloquent-orm)
6. [Capa de Repositorios (Lógica de Negocio y Seguridad)](#6-capa-de-repositorios-lógica-de-negocio-y-seguridad)
7. [Capa de Validaciones](#7-capa-de-validaciones)
8. [Capa de Controladores (API REST vs Blade SSR)](#8-capa-de-controladores-api-rest-vs-blade-ssr)
9. [Capa de Vistas (Blade SSR UI y Componentes)](#9-capa-de-vistas-blade-ssr-ui-y-componentes)
10. [Rutas (API y Web)](#10-rutas-api-y-web)
11. [Flujos Completos de Operación](#11-flujos-completos-de-operación)
12. [Relación con Otros Módulos](#12-relación-con-otros-módulos)
13. [Mapa de Archivos del Módulo](#13-mapa-de-archivos-del-módulo)

---

## 1. Visión General del Módulo

El módulo de **Perfil de Usuario** gestiona la configuración personal de la cuenta para todos los usuarios autenticados del sistema, independientemente de su rol (`admin`, `doctor`, `recepcionista`, `paciente`).

### Responsabilidades Principales

| Responsabilidad | Descripción Técnica |
|---|---|
| **Consulta de Perfil Integrado** | Devolver la información personal del usuario unida a su perfil específico según su rol (Especialidades para médicos, Expediente para pacientes, etc.). |
| **Actualización de Datos de Contacto** | Permitir modificar nombre, teléfono y datos de contacto/emergencia. |
| **Cambio Seguro de Contraseña** | Verificar la contraseña actual mediante `Hash::check()` antes de aplicar un nuevo hash bcrypt. |
| **Carga de Foto de Perfil** | Gestionar el procesamiento y almacenamiento de imágenes avatar en el sistema de archivos (`storage/app/public/fotos_perfil`). |
| **Historial Médico Propio (Paciente)** | Proporcionar a los pacientes el acceso directo a sus citas completadas y recetas/notas de consulta desde su aplicación móvil. |

### Roles que Interactúan con este Módulo

| Rol | Alcance de Operaciones |
|---|---|
| **Todos los Roles** | Consultar datos, editar teléfono, cambiar contraseña y actualizar foto de perfil. |
| **Paciente** | Además, consultar expediente completo y descargar notas de consultas anteriores. |

---

## 2. Diagrama de Arquitectura del Módulo

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PETICIÓN HTTP                                       │
│          API REST (/api/miPerfil, /cambiarPassword) │  Web SSR (/perfil, /perfil/update) │
└───────────────────────────┬───────────────────────────────┬────────────────────────────┘
                            │                               │
                            ▼                               ▼
               ┌──────────────────────────┐    ┌──────────────────────────────┐
               │     PerfilController     │    │     PerfilWebController      │
               │        (API JSON)        │    │     (Blade SSR + Session)    │
               └────────────┬─────────────┘    └──────────────┬───────────────┘
                            │                                 │
                            │   ┌─────────────────────────┐   │
                            └──►│ Validaciones Inline     │◄──┘
                                │ (validate / rules)      │
                                └────────────┬────────────┘
                                             │
                                             ▼
                             ┌────────────────────────────────┐
                             │       UsuariosRepository       │
                             │  • obtenerPerfil()             │
                             │  • actualizarPerfil()          │
                             │  • cambiarPassword()           │
                             │  • actualizarFoto()            │
                             └───────────────┬────────────────┘
                                             │
                       ┌─────────────────────┴─────────────────────┐
                       ▼                                           ▼
         ┌───────────────────────────┐               ┌───────────────────────────┐
         │      Modelo Usuario       │               │      Laravel Storage      │
         │  - Relationships de Rol   │               │ (disk: public/fotos_p..)  │
         └─────────────┬─────────────┘               └─────────────┬─────────────┘
                       │                                           │
                       ▼                                           ▼
         ┌───────────────────────────┐               ┌───────────────────────────┐
         │    Tabla: usuarios (DB)   │               │   FileSystem (Disk Storage│
         └───────────────────────────┘               └───────────────────────────┘
```

---

## 3. Modelo de Datos Relacional

El modelo central `usuarios` se vincula de forma condicional a uno de los tres perfiles especializados según el campo `rol`:

```
                               ┌─────────────────────────┐
                               │        usuarios         │
                               │─────────────────────────│
                               │ id (PK)                 │
                               │ nombre                  │
                               │ email (UNIQUE)          │
                               │ password (HASH)         │
                               │ curp                    │
                               │ telefono                │
                               │ rol (ENUM)              │
                               │ foto_perfil (VARCHAR)   │
                               │ estado                  │
                               └────────────┬────────────┘
                                            │
           ┌────────────────────────────────┼────────────────────────────────┐
           │ (Si rol='doctor')              │ (Si rol='paciente')            │ (Si rol='recepcionista')
           ▼                                ▼                                ▼
┌─────────────────────┐          ┌─────────────────────┐          ┌─────────────────────┐
│   perfiles_doctor   │          │  perfiles_paciente  │          │perfiles_recep...    │
│─────────────────────│          │─────────────────────│          │─────────────────────│
│ id (PK)             │          │ id (PK)             │          │ id (PK)             │
│ usuario_id (FK 1:1) │          │ usuario_id (FK 1:1) │          │ usuario_id (FK 1:1) │
│ cedula_profesional  │          │ numero_expediente   │          │ turno               │
└─────────────────────┘          └─────────────────────┘          └─────────────────────┘
```

---

## 4. Capa de Base de Datos y Almacenamiento (Storage)

### 4.1 Estructura de la Tabla `usuarios` (Extracto Relevante)

```php
Schema::create('usuarios', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('curp', 18)->unique()->nullable();
    $table->string('telefono', 20)->nullable();
    $table->enum('rol', ['admin', 'doctor', 'recepcionista', 'paciente']);
    $table->enum('estado', ['activo', 'inactivo', 'bloqueado'])->default('activo');
    $table->string('foto_perfil')->nullable(); // Ruta relativa en Storage
    $table->rememberToken();
    $table->timestamps();
});
```

### 4.2 Almacenamiento de Archivos (Laravel Storage Driver)
- **Disco Utilizado:** `'public'`
- **Directorio de Destino:** `storage/app/public/fotos_perfil`
- **Enlace Simbólico:** Generado mediante `php artisan storage:link`, exponiendo los archivos cargados públicamente en la URL `/storage/fotos_perfil/filename.jpg`.

---

## 5. Capa de Modelos (Eloquent ORM)

**Archivo:** `app/Models/Usuario.php`

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'curp',
        'telefono',
        'rol',
        'estado',
        'foto_perfil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function perfilDoctor()
    {
        return $this->hasOne(PerfilDoctor::class, 'usuario_id');
    }

    public function perfilPaciente()
    {
        return $this->hasOne(PerfilPaciente::class, 'usuario_id');
    }

    public function perfilRecepcionista()
    {
        return $this->hasOne(PerfilRecepcionista::class, 'usuario_id');
    }
}
```

---

## 6. Capa de Repositorios (Lógica de Negocio y Seguridad)

**Archivo:** `app/Http/Repository/UsuariosRepository.php`

```php
<?php

namespace App\Http\Repository;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Exception;

class UsuariosRepository
{
    /**
     * Carga el perfil del usuario junto con las relaciones de su rol especifico.
     */
    public function obtenerPerfil(int $id)
    {
        try {
            $usuario = Usuario::with([
                'perfilDoctor.especialidades',
                'perfilPaciente',
                'perfilRecepcionista',
            ])->find($id);

            if (!$usuario) {
                return ['mensaje' => 'Usuario no encontrado'];
            }

            return [
                'mensaje' => 'Perfil obtenido correctamente',
                'data'    => $usuario,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Actualiza los datos generales del usuario y su perfil especifico si aplica.
     */
    public function actualizarPerfil(int $id, array $data)
    {
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return ['mensaje' => 'Usuario no encontrado'];
            }

            $usuario->update([
                'nombre'   => $data['nombre']   ?? $usuario->nombre,
                'telefono' => $data['telefono']  ?? $usuario->telefono,
            ]);

            // Actualizar sub-perfil de paciente si aplica
            if ($usuario->rol === 'paciente' && $usuario->perfilPaciente) {
                $usuario->perfilPaciente->update([
                    'direccion'                    => $data['direccion']                    ?? $usuario->perfilPaciente->direccion,
                    'contacto_emergencia_nombre'   => $data['contacto_emergencia_nombre']   ?? $usuario->perfilPaciente->contacto_emergencia_nombre,
                    'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? $usuario->perfilPaciente->contacto_emergencia_telefono,
                ]);
            }

            return [
                'mensaje' => 'Perfil actualizado correctamente',
                'data'    => $usuario->load(['perfilDoctor', 'perfilPaciente', 'perfilRecepcionista']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Verifica la contraseña actual e incrementa la seguridad aplicando Hash::make.
     */
    public function cambiarPassword(int $id, array $data)
    {
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return ['mensaje' => 'Usuario no encontrado'];
            }

            // Validación estricta de hash
            if (!Hash::check($data['password_actual'], $usuario->password)) {
                return ['mensaje' => 'La contraseña actual es incorrecta.'];
            }

            $usuario->update(['password' => Hash::make($data['password'])]);

            return ['mensaje' => 'Contraseña actualizada correctamente'];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function actualizarFoto(int $id, string $rutaFoto)
    {
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                return ['mensaje' => 'Usuario no encontrado'];
            }

            $usuario->update(['foto_perfil' => $rutaFoto]);

            return [
                'mensaje' => 'Foto de perfil actualizada correctamente',
                'data'    => ['foto_perfil' => $rutaFoto],
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
```

---

## 7. Capa de Validaciones

Las validaciones para la actualización de perfil y cambio de contraseña se ejecutan mediante validación inline en los controladores:

### 7.1 Validación para Cambio de Contraseña
```php
$request->validate([
    'password_actual' => 'required|string',
    'password'        => 'required|string|min:8|confirmed',
], [
    'password_actual.required' => 'Debes ingresar tu contraseña actual.',
    'password.required'        => 'La nueva contraseña es requerida.',
    'password.min'             => 'La nueva contraseña debe tener al menos 8 caracteres.',
    'password.confirmed'       => 'La confirmación de la contraseña no coincide.',
]);
```

### 7.2 Validación para Carga de Foto de Perfil
```php
$request->validate([
    'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
], [
    'foto.required' => 'Selecciona un archivo de imagen.',
    'foto.image'    => 'El archivo debe ser una imagen válida.',
    'foto.max'      => 'La imagen no debe pesar más de 2MB.',
]);
```

---

## 8. Capa de Controladores (API REST vs Blade SSR)

### 8.1 Controlador API (`PerfilController`)

**Archivo:** `app/Http/Controllers/PerfilController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Repository\UsuariosRepository;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    protected $usuariosRepository;

    public function __construct(UsuariosRepository $usuariosRepository)
    {
        $this->usuariosRepository = $usuariosRepository;
    }

    public function miPerfil(Request $request)
    {
        try {
            $resultado = $this->usuariosRepository->obtenerPerfil($request->user()->id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function actualizarMiPerfil(Request $request)
    {
        try {
            $resultado = $this->usuariosRepository->actualizarPerfil($request->user()->id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function cambiarPassword(Request $request)
    {
        try {
            $request->validate([
                'password_actual' => 'required|string',
                'password'        => 'required|string|min:8|confirmed',
            ]);
            $resultado = $this->usuariosRepository->cambiarPassword($request->user()->id, $request->all());
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function actualizarFoto(Request $request)
    {
        try {
            $request->validate(['foto' => 'required|image|max:2048']);
            $ruta      = $request->file('foto')->store('fotos_perfil', 'public');
            $resultado = $this->usuariosRepository->actualizarFoto($request->user()->id, $ruta);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function miHistorial(Request $request)
    {
        try {
            $usuario = $request->user()->load([
                'perfilPaciente.citas' => function ($q) {
                    $q->where('estado', 'completada')
                        ->with(['notaConsulta', 'perfilDoctor.usuario', 'especialidad'])
                        ->orderBy('fecha_cita', 'desc');
                },
            ]);
            return response()->json([
                'mensaje' => 'Historial médico obtenido correctamente',
                'data'    => $usuario->perfilPaciente?->citas ?? [],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
```

---

### 8.2 Controlador Web (`PerfilWebController`)

**Archivo:** `app/Http/Controllers/Web/PerfilWebController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Repository\UsuariosRepository;
use Illuminate\Http\Request;

class PerfilWebController extends Controller
{
    protected $usuariosRepository;

    public function __construct(UsuariosRepository $usuariosRepository)
    {
        $this->usuariosRepository = $usuariosRepository;
    }

    public function index(Request $request)
    {
        $usuario = $request->user();
        return view('perfil.index', compact('usuario'));
    }

    public function update(Request $request)
    {
        try {
            $this->usuariosRepository->actualizarPerfil($request->user()->id, $request->all());
            return back()->with('success', 'Perfil actualizado con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password'        => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->usuariosRepository->cambiarPassword($request->user()->id, $request->all());
            return back()->with('success', 'Contraseña modificada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function actualizarFoto(Request $request)
    {
        $request->validate(['foto' => 'required|image|max:2048']);
        try {
            $ruta = $request->file('foto')->store('fotos_perfil', 'public');
            $this->usuariosRepository->actualizarFoto($request->user()->id, $ruta);
            return back()->with('success', 'Foto de perfil actualizada.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

---

## 9. Capa de Vistas (Blade SSR UI y Componentes)

**Archivo:** `resources/views/perfil/index.blade.php`

Maneja la edición de datos personales y la actualización segura de contraseña.

```html
@extends('layouts.app')
@section('titulo', 'Mi Perfil')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="h3 fw-bold mb-0">Configuración de Mi Perfil</h1>
</div>

<div class="mx-auto" style="max-width: 720px;">
    <!-- Profile Hero Header -->
    <div class="card border-0 shadow-sm rounded-4 text-white mb-4 p-4" style="background: linear-gradient(135deg, #1d3557 0%, #2a9d8f 100%);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-25 text-white fw-bold d-flex align-items-center justify-content-center border border-white border-opacity-50" style="width: 72px; height: 72px; font-size: 28px;">
                {{ strtoupper(substr($usuario->nombre ?? 'U', 0, 2)) }}
            </div>
            <div>
                <h2 class="h4 fw-bold mb-1 text-white">{{ $usuario->nombre }}</h2>
                <span class="badge bg-success text-capitalize fs-6">{{ $usuario->rol }}</span>
            </div>
        </div>
    </div>

    <!-- Personal Info Form -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 p-4">
        <h5 class="fw-bold mb-3 border-bottom pb-2">Datos Personales</h5>
        <form method="POST" action="{{ route('perfil.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold">Nombre Completo</label>
                <input type="text" value="{{ $usuario->nombre }}" class="form-control bg-light" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold">Correo Electrónico</label>
                <input type="email" value="{{ $usuario->email }}" class="form-control bg-light" disabled>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="txt_telefono" class="form-label text-secondary small fw-semibold">Teléfono de Contacto *</label>
                    <input type="tel" id="txt_telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" class="form-control" placeholder="10 dígitos" maxlength="10" required>
                </div>

                <div class="col-md-6">
                    <label for="txt_curp" class="form-label text-secondary small fw-semibold">CURP</label>
                    <input type="text" id="txt_curp" name="curp" value="{{ old('curp', $usuario->curp) }}" class="form-control text-uppercase" placeholder="18 caracteres">
                </div>
            </div>

            <div class="text-end pt-2">
                <button type="submit" class="btn btn-primary fw-semibold px-4">Actualizar Datos</button>
            </div>
        </form>
    </div>

    <!-- Password Change Form -->
    <div class="card border-0 shadow-sm rounded-3 p-4">
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-danger">Cambiar Contraseña</h5>
        <form method="POST" action="{{ route('perfil.password') }}">
            @csrf
            <div class="mb-3">
                <label for="txt_pass_actual" class="form-label text-secondary small fw-semibold">Contraseña Actual *</label>
                <input type="password" id="txt_pass_actual" name="password_actual" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="txt_pass_nueva" class="form-label text-secondary small fw-semibold">Nueva Contraseña *</label>
                <input type="password" id="txt_pass_nueva" name="password" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" required>
            </div>

            <div class="mb-3">
                <label for="txt_pass_conf" class="form-label text-secondary small fw-semibold">Confirmar Nueva Contraseña *</label>
                <input type="password" id="txt_pass_conf" name="password_confirmation" class="form-control" placeholder="Repetir contraseña" minlength="8" required>
            </div>

            <div class="text-end pt-2">
                <button type="submit" class="btn btn-outline-danger fw-semibold px-4">Cambiar Contraseña</button>
            </div>
        </form>
    </div>
</div>
@endsection
```

---

## 10. Rutas (API y Web)

### 10.1 Rutas API (`routes/api.php`)

```php
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::get('/miPerfil', [PerfilController::class, 'miPerfil']);
    Route::put('/actualizarMiPerfil', [PerfilController::class, 'actualizarMiPerfil']);
    Route::post('/cambiarPassword', [PerfilController::class, 'cambiarPassword']);
    Route::post('/actualizarFoto', [PerfilController::class, 'actualizarFoto']);

    // Pacientes Móviles
    Route::get('/miHistorial', [PerfilController::class, 'miHistorial']);
    Route::get('/miConsulta/{id}', [PerfilController::class, 'miConsulta']);
});
```

---

### 10.2 Rutas Web (`routes/web.php`)

```php
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/perfil', [PerfilWebController::class, 'index'])->name('perfil.index');
    Route::put('/perfil', [PerfilWebController::class, 'update'])->name('perfil.update');
    Route::post('/perfil/password', [PerfilWebController::class, 'cambiarPassword'])->name('perfil.password');
    Route::post('/perfil/foto', [PerfilWebController::class, 'actualizarFoto'])->name('perfil.foto');
});
```

---

## 11. Flujos Completos de Operación

### 11.1 Cambio de Contraseña Seguro

```
   USUARIO (WEB/APP)                      PerfilController                      UsuariosRepository                       BASE DE DATOS
           │                                      │                                      │                                           │
           │ POST /cambiarPassword                │                                      │                                           │
           │ (pass_actual, pass_nueva, conf)      │                                      │                                           │
           ├─────────────────────────────────────►│                                      │                                           │
           │                                      │ validate(min:8, confirmed)           │                                           │
           │                                      ├─────────────────────────────────────►│                                           │
           │                                      │                                      │ 1. Hash::check(actual, usuario->password) │
           │                                      │                                      ├──────────────────────────────────────────►│
           │                                      │                                      │◄──────────────────────────────────────────┤
           │                                      │                                      │ Si OK:                                    │
           │                                      │                                      │ 2. Hash::make(pass_nueva)                 │
           │                                      │                                      │ 3. usuario->update(['password' => ...])   │
           │                                      │                                      ├──────────────────────────────────────────►│
           │                                      │                                      │◄──────────────────────────────────────────┤
           │ JSON 200 OK ("Contraseña cambiada")  │◄─────────────────────────────────────┤                                           │
           │◄─────────────────────────────────────┤                                                                                  │
```

---

## 12. Relación con Otros Módulos

```
                               ┌──────────────────────────┐
                               │   Módulo 8: PERFIL DE    │
                               │         USUARIO          │
                               └────────────┬─────────────┘
                                            │
         ┌───────────────────────┬──────────┴────────────┬────────────────────────┐
         ▼                       ▼                       ▼                        ▼
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────────┐
│ Mod 1: Auth y    │  │ Mod 2: Doctores  │  │ Mod 6: Pacientes │  │ Mod 10: Recepción    │
│ Seguridad        │  │                  │  │                  │  │                      │
│ Hash y Tokens    │  │ Sub-perfil Doctor│  │ Sub-perfil Pacie.│  │ Sub-perfil Recepcion.│
└──────────────────┘  └──────────────────┘  └──────────────────┘  └──────────────────────┘
```

---

## 13. Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PerfilController.php                # Controller API REST Perfil
│   │   │   └── Web/
│   │   │       └── PerfilWebController.php          # Controller Web SSR Perfil
│   │   └── Repository/
│   │       └── UsuariosRepository.php              # Lógica de perfil, contraseña y fotos
│   └── Models/
│       └── Usuario.php                             # Modelo Eloquent usuarios
├── resources/views/
│   └── perfil/
│       └── index.blade.php                         # Vista de Configuración de Perfil
└── routes/
    ├── api.php                                     # Endpoints API REST (/api/miPerfil, etc.)
    └── web.php                                     # Rutas Web SSR (/perfil, etc.)
```

---

> **Módulo anterior:** [07 - Notas de Consulta](./07-Notas-de-Consulta.md)  
> **Siguiente módulo:** [09 - Reportes y Estadísticas](./09-Reportes-y-Estadisticas.md)
