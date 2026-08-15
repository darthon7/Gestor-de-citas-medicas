# 📝 Módulo 7: Notas de Consulta y Diagnóstico

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura del Módulo](#2-diagrama-de-arquitectura-del-módulo)
3. [Modelo de Datos Relacional](#3-modelo-de-datos-relacional)
4. [Capa de Base de Datos — Migraciones](#4-capa-de-base-de-datos--migraciones)
5. [Capa de Modelos (Eloquent ORM)](#5-capa-de-modelos-eloquent-orm)
6. [Capa de Repositorios (Lógica de Negocio y Completado de Cita)](#6-capa-de-repositorios-lógica-de-negocio-y-completado-de-cita)
7. [Capa de Validaciones (Form Requests)](#7-capa-de-validaciones-form-requests)
8. [Capa de Controladores (API REST vs Blade SSR)](#8-capa-de-controladores-api-rest-vs-blade-ssr)
9. [Capa de Vistas (Blade SSR UI y Componentes)](#9-capa-de-vistas-blade-ssr-ui-y-componentes)
10. [Rutas (API y Web)](#10-rutas-api-y-web)
11. [Flujos Completos de Operación](#11-flujos-completos-de-operación)
12. [Relación con Otros Módulos](#12-relación-con-otros-módulos)
13. [Mapa de Archivos del Módulo](#13-mapa-de-archivos-del-módulo)

---

## 1. Visión General del Módulo

El módulo de **Notas de Consulta y Diagnóstico** es el componente clínico del sistema donde se registra la evidencia médica del acto de atención. Permite al profesional de la salud documentar el juicio diagnóstico, la receta/tratamiento prescripto y las recomendaciones clínicas derivadas de una cita médica.

### Responsabilidades Principales

| Responsabilidad | Descripción Técnica |
|---|---|
| **Documentación Clínica** | Almacenar el diagnóstico médico, plan de tratamiento y observaciones adicionales de la consulta. |
| **Unicidad de Nota por Cita** | Garantizar mediante restricciones de negocio y BD que cada cita médica tenga como máximo una única nota registrada (Relación 1:1). |
| **Cierre de Ciclo de Atención** | Cambiar automáticamente el estado de la cita a `completada` al momento de registrar la nota de consulta. |
| **Trazabilidad de Emisión** | Registrar la autoría del médico que elaboró la nota (`creado_por`) con marca de tiempo. |
| **Consulta en Expediente** | Exponer la nota clínica al paciente en su historial clínico y al administrador/recepcionista en los detalles de la cita. |

### Roles que Interactúan con este Módulo

| Rol | Permisos y Operaciones |
|---|---|
| **Médico (Doctor)** | Iniciar la atención de la cita, redactar la nota médica (diagnóstico y tratamiento) y finalizar la consulta. |
| **Paciente** | Consultar las notas y recetas emitidas en sus consultas finalizadas a través de su historial clínico. |
| **Administrador / Recepcionista** | Ver las notas clínicas vinculadas a las citas completadas para fines administrativos o expedición de duplicados. |

---

## 2. Diagrama de Arquitectura del Módulo

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PETICIÓN HTTP                                       │
│    API REST (/api/registrarNota/{citaId})     │   Web SSR (/diagnostico/{citaId})      │
└───────────────────────────┬───────────────────────────────┬────────────────────────────┘
                            │                               │
                            ▼                               ▼
               ┌──────────────────────────┐    ┌──────────────────────────────┐
               │  NotasConsultaController │    │      DoctorWebController     │
               │        (API JSON)        │    │     (Blade SSR + Session)    │
               └────────────┬─────────────┘    └──────────────┬───────────────┘
                            │                                 │
                            │   ┌─────────────────────────┐   │
                            └──►│ StoreNotaConsultaRequest│◄──┘
                                │ (Validación de Campos)  │
                                └────────────┬────────────┘
                                             │
                                             ▼
                             ┌────────────────────────────────┐
                             │    NotasConsultaRepository     │
                             │  • registrarNota()             │
                             │  • obtenerNotas()              │
                             └───────────────┬────────────────┘
                                             │
                       ┌─────────────────────┴─────────────────────┐
                       ▼                                           ▼
         ┌───────────────────────────┐               ┌───────────────────────────┐
         │    Modelo NotaConsulta    │               │        Modelo Cita        │
         │  ($table='notas_consulta')│               │ ($cita->update('complet.')│
         └─────────────┬─────────────┘               └─────────────┬─────────────┘
                       │                                           │
                       └─────────────────────┬─────────────────────┘
                                             │
                                             ▼
                             ┌────────────────────────────────┐
                             │         BASE DE DATOS          │
                             │   [notas_consulta]   [citas]   │
                             └────────────────────────────────┘
```

---

## 3. Modelo de Datos Relacional

El módulo se estructura alrededor de la tabla `notas_consulta`, que se relaciona de forma **One-to-One** con la tabla `citas` y **Many-to-One** con la tabla `usuarios` (para la autoría del médico).

```
┌─────────────────────────┐             ┌─────────────────────────┐             ┌─────────────────────────┐
│          citas          │             │     notas_consulta      │             │        usuarios         │
│─────────────────────────│             │─────────────────────────│             │─────────────────────────│
│ id (PK)                 │◄────────────│ cita_id (FK UNIQUE)     │             │ id (PK)                 │
│ perfil_paciente_id (FK) │ 1:1         │ diagnostico (TEXT)      │             │ nombre                  │
│ perfil_doctor_id (FK)   │             │ tratamiento (TEXT)      │             │ email                   │
│ estado                  │             │ notas_adicionales (TEXT)│ 1:N (Inversa)│ rol = 'doctor'          │
│ timestamps              │             │ creado_por (FK NULL) ───┼────────────►│ timestamps              │
└─────────────────────────┘             │ timestamps              │             └─────────────────────────┘
                                        └─────────────────────────┘
```

---

## 4. Capa de Base de Datos — Migraciones

**Archivo:** `database/migrations/2026_01_01_000010_crear_tabla_notas_consulta.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_consulta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->text('diagnostico');
            $table->text('tratamiento');
            $table->text('notas_adicionales')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_consulta');
    }
};
```

### Análisis Técnico de Columnas de `notas_consulta`

| Columna | Tipo SQL | Constraint / Index | Propósito y Comportamiento |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `PRIMARY KEY AUTO_INCREMENT` | Identificador autoincremental de la nota clínica. |
| `cita_id` | `BIGINT UNSIGNED` | `FK constrained('citas') onDelete('cascade')` | Enlace con la cita médica correspondiente. Si la cita es eliminada, la nota se borra en cascada. |
| `diagnostico` | `TEXT` | `NOT NULL` | Descripción médica formal del diagnóstico encontrado durante la evaluación. |
| `tratamiento` | `TEXT` | `NOT NULL` | Plan terapéutico, medicamentos recetados, posología e indicaciones al paciente. |
| `notas_adicionales` | `TEXT` | `NULLABLE` | Observaciones clínicas secundarias o comentarios internos del médico. |
| `creado_por` | `BIGINT UNSIGNED` | `FK constrained('usuarios') onDelete('set null')` | Referencia al usuario médico que emitió la nota clínica (para auditoría). |

---

## 5. Capa de Modelos (Eloquent ORM)

**Archivo:** `app/Models/NotaConsulta.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaConsulta extends Model
{
    use HasFactory;

    protected $table = 'notas_consulta';

    protected $fillable = [
        'cita_id',
        'diagnostico',
        'tratamiento',
        'notas_adicionales',
        'creado_por',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }
}
```

---

## 6. Capa de Repositorios (Lógica de Negocio y Completado de Cita)

**Archivo:** `app/Http/Repository/NotasConsultaRepository.php`

Maneja las reglas de negocio para el registro de notas diagnósticas y su impacto en la cita.

```php
<?php

namespace App\Http\Repository;

use App\Models\Cita;
use App\Models\NotaConsulta;
use Exception;

class NotasConsultaRepository
{
    /**
     * Registra la nota clínica y actualiza automáticamente el estado de la cita a 'completada'.
     */
    public function registrarNota(int $citaId, array $data, int $doctorUsuarioId)
    {
        try {
            $cita = Cita::find($citaId);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            // Regla de Negocio 1: Solo registrar nota en citas en consulta o completadas
            if ($cita->estado !== 'en_consulta' && $cita->estado !== 'completada') {
                return ['mensaje' => 'Solo se pueden registrar notas en citas en consulta o completadas.'];
            }

            // Regla de Negocio 2: Verificar unicidad de nota por cita (1:1)
            if ($cita->notaConsulta) {
                return ['mensaje' => 'Esta cita ya tiene una nota de consulta registrada.'];
            }

            // Crear la nota médica
            $nota = NotaConsulta::create([
                'cita_id'           => $citaId,
                'diagnostico'       => $data['diagnostico'],
                'tratamiento'       => $data['tratamiento'],
                'notas_adicionales' => $data['notas_adicionales'] ?? null,
                'creado_por'        => $doctorUsuarioId,
            ]);

            // Efecto Secundario Atómico: Marcar la cita como completada
            $cita->update(['estado' => 'completada']);

            return [
                'mensaje' => 'Nota de consulta registrada correctamente',
                'data'    => $nota->load('cita'),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Obtiene la nota de consulta asociada a una cita.
     */
    public function obtenerNotas(int $citaId)
    {
        try {
            $nota = NotaConsulta::with('cita.perfilPaciente.usuario')
                ->where('cita_id', $citaId)
                ->first();

            if (!$nota) {
                return ['mensaje' => 'No hay notas para esta cita'];
            }

            return [
                'mensaje' => 'Nota de consulta obtenida correctamente',
                'data'    => $nota,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
```

---

## 7. Capa de Validaciones (Form Requests)

**Archivo:** `app/Http/Requests/StoreNotaConsultaRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNotaConsultaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'diagnostico'       => 'required|string',
            'tratamiento'       => 'required|string',
            'notas_adicionales' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'diagnostico.required' => 'El diagnóstico es requerido.',
            'tratamiento.required' => 'El tratamiento es requerido.',
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

### 8.1 Controlador API (`NotasConsultaController`)

**Archivo:** `app/Http/Controllers/NotasConsultaController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Repository\NotasConsultaRepository;
use App\Http\Requests\StoreNotaConsultaRequest;

class NotasConsultaController extends Controller
{
    protected $notasRepository;

    public function __construct(NotasConsultaRepository $notasRepository)
    {
        $this->notasRepository = $notasRepository;
    }

    public function registrarNota(StoreNotaConsultaRequest $request, int $citaId)
    {
        try {
            $doctorUsuarioId = $request->user()->id;
            $resultado       = $this->notasRepository->registrarNota($citaId, $request->all(), $doctorUsuarioId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }

    public function obtenerNotas(int $citaId)
    {
        try {
            $resultado = $this->notasRepository->obtenerNotas($citaId);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => $e->getMessage()], 500);
        }
    }
}
```

---

### 8.2 Controlador Web (`DoctorWebController`)

**Archivo:** `app/Http/Controllers/Web/DoctorWebController.php`

Administra el flujo de trabajo médico del panel web SSR.

```php
public function agenda(Request $request)
{
    $usuario = $request->user();
    $perfilDoctor = PerfilDoctor::where('usuario_id', $usuario->id)->first();

    if (!$perfilDoctor) {
        return redirect()->route('dashboard')->with('error', 'Perfil de médico no encontrado.');
    }

    $fecha = $request->query('fecha', Carbon::today()->format('Y-m-d'));

    $citas = Cita::with(['perfilPaciente.usuario', 'especialidad', 'notaConsulta'])
        ->where('perfil_doctor_id', $perfilDoctor->id)
        ->whereDate('fecha_hora', $fecha)
        ->orderBy('fecha_hora', 'asc')
        ->get();

    return view('doctor.agenda', compact('citas', 'fecha', 'perfilDoctor'));
}

public function diagnostico(int $citaId)
{
    $cita = Cita::with(['perfilPaciente.usuario', 'especialidad', 'notaConsulta'])->findOrFail($citaId);
    return view('doctor.diagnostico', compact('cita'));
}

public function iniciarConsulta(int $id)
{
    try {
        $this->citasRepository->iniciarConsulta($id);
        return redirect()->route('doctor.diagnostico', $id)->with('success', 'Consulta iniciada.');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}

public function registrarNota(StoreNotaConsultaRequest $request, int $citaId)
{
    try {
        $doctorUsuarioId = $request->user()->id;
        $this->notasRepository->registrarNota($citaId, $request->all(), $doctorUsuarioId);
        $this->citasRepository->completarCita($citaId);

        return redirect()->route('doctor.agenda')->with('success', 'Nota médica registrada y consulta completada.');
    } catch (\Exception $e) {
        return back()->withInput()->with('error', $e->getMessage());
    }
}
```

---

## 9. Capa de Vistas (Blade SSR UI y Componentes)

### 9.1 Agenda Médica (`resources/views/doctor/agenda.blade.php`)

Muestra el listado cronológico de consultas asignadas al médico para la fecha seleccionada. Los botones cambian dinámicamente según el estado:
- Si la cita está `confirmada`: Muestra el botón **"Iniciar Consulta"** (`PATCH /citas/{id}/iniciar`).
- Si la cita está `en_consulta`: Muestra el botón **"Registrar Diagnóstico"** (Enlace a `/diagnostico/{id}`).
- Si la cita está `completada`: Muestra el botón **"Ver Nota"**.

### 9.2 Formulario de Registro de Diagnóstico (`resources/views/doctor/diagnostico.blade.php`)

Permite redactar la nota médica con campos para diagnóstico, tratamiento y notas adicionales.

```html
@extends('layouts.app')
@section('titulo', 'Registro de Consulta')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4 pb-2 border-bottom">
    <a href="{{ route('doctor.agenda') }}" class="btn btn-outline-secondary btn-sm"><i data-lucide="arrow-left"></i></a>
    <h1 class="h3 fw-bold mb-0">Registro de Consulta y Diagnóstico</h1>
</div>

<div class="mx-auto" style="max-width: 840px;">
    <!-- Patient Mini Card -->
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 20px;">
                    {{ strtoupper(substr($cita->perfilPaciente?->usuario?->nombre ?? 'P', 0, 2)) }}
                </div>
                <div>
                    <h5 class="fw-bold mb-1">{{ $cita->perfilPaciente?->usuario?->nombre ?? 'Paciente' }}</h5>
                    <span class="text-secondary small">
                        Expediente: {{ $cita->perfilPaciente?->numero_expediente ?? 'N/A' }} | Cita #{{ $cita->id }}
                    </span>
                </div>
            </div>
            <span class="badge bg-warning text-dark fs-6">En Consulta</span>
        </div>
    </div>

    <!-- Form Card -->
    <form method="POST" action="{{ route('notas.store', $cita->id) }}">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
            <!-- Section 1: Diagnóstico -->
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                    <i data-lucide="clipboard"></i> Diagnóstico Médico *
                </h5>
                <textarea id="txt_diagnostico" name="diagnostico" class="form-control" rows="5" placeholder="Descripción detallada del diagnóstico del paciente..." required>{{ old('diagnostico') }}</textarea>
            </div>

            <hr class="my-4 text-secondary opacity-25">

            <!-- Section 2: Tratamiento Indicado -->
            <div class="mb-4">
                <h5 class="fw-bold text-info mb-3 d-flex align-items-center gap-2">
                    <i data-lucide="pill"></i> Tratamiento y Recomendaciones *
                </h5>
                <textarea id="txt_tratamiento" name="tratamiento" class="form-control" rows="4" placeholder="Medicamentos, dosis y recomendaciones clínicas..." required>{{ old('tratamiento') }}</textarea>
            </div>

            <!-- Section 3: Notas Adicionales -->
            <div class="mb-2">
                <label for="txt_notas_adicionales" class="form-label small fw-semibold text-secondary">Observaciones Adicionales (Opcional)</label>
                <textarea id="txt_notas_adicionales" name="notas_adicionales" class="form-control" rows="3" placeholder="Comentarios adicionales o notas internas...">{{ old('notas_adicionales') }}</textarea>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('doctor.agenda') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                <i data-lucide="check-circle" class="me-1"></i> Registrar Nota y Completar Consulta
            </button>
        </div>
    </form>
</div>
@endsection
```

---

## 10. Rutas (API y Web)

### 10.1 Rutas API (`routes/api.php`)

```php
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    // Rutas protegidas exclusivas para Médicos
    Route::middleware(['role:doctor'])->group(function () {
        Route::post('/registrarNota/{citaId}', [NotasConsultaController::class, 'registrarNota']);
        Route::get('/obtenerNotas/{citaId}', [NotasConsultaController::class, 'obtenerNotas']);
    });
});
```

---

### 10.2 Rutas Web (`routes/web.php`)

```php
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::middleware(['role:doctor'])->group(function () {
        Route::get('/mi-agenda', [DoctorWebController::class, 'agenda'])->name('doctor.agenda');
        Route::get('/diagnostico/{citaId}', [DoctorWebController::class, 'diagnostico'])->name('doctor.diagnostico');
        Route::patch('/citas/{id}/iniciar', [DoctorWebController::class, 'iniciarConsulta'])->name('citas.iniciar');
        Route::patch('/citas/{id}/completar', [DoctorWebController::class, 'completarCita'])->name('citas.completar');
        Route::post('/citas/{citaId}/nota', [DoctorWebController::class, 'registrarNota'])->name('notas.store');
    });
});
```

---

## 11. Flujos Completos de Operación

### 11.1 Flujo de Atención Médica y Registro Diagnóstico

```
   MÉDICO (PANEL WEB)                    DoctorWebController                     NotasConsultaRepo                     BASE DE DATOS
           │                                      │                                      │                                           │
           │ 1. Clic en "Iniciar Consulta"         │                                      │                                           │
           │ PATCH /citas/{id}/iniciar            │                                      │                                           │
           ├─────────────────────────────────────►│                                      │                                           │
           │                                      │ CitasRepository::iniciarConsulta()   │                                           │
           │                                      │ (Actualiza estado => 'en_consulta')  ├──────────────────────────────────────────►│
           │ 302 Redirect (/diagnostico/{id})     │                                      │                                           │
           │◄─────────────────────────────────────┤                                      │                                           │
           │                                      │                                      │                                           │
           │ 2. Redacta Diagnóstico y Tratamiento │                                      │                                           │
           │ POST /citas/{id}/nota                │                                      │                                           │
           ├─────────────────────────────────────►│                                      │                                           │
           │                                      │ registrarNota($citaId, $data)        │                                           │
           │                                      ├─────────────────────────────────────►│                                           │
           │                                      │                                      │ 1. NotaConsulta::create(...)              │
           │                                      │                                      ├──────────────────────────────────────────►│
           │                                      │                                      │ 2. Cita::update(['estado'=>'completada']) │
           │                                      │                                      ├──────────────────────────────────────────►│
           │                                      │                                      │◄──────────────────────────────────────────┤
           │ 302 Redirect (/mi-agenda)            │◄─────────────────────────────────────┤                                           │
           │ + Flash: "Consulta completada"       │                                                                                  │
           │◄─────────────────────────────────────┤                                                                                  │
```

---

## 12. Relación con Otros Módulos

```
                               ┌──────────────────────────┐
                               │   Módulo 7: NOTAS DE     │
                               │  CONSULTA Y DIAGNÓSTICO  │
                               └────────────┬─────────────┘
                                            │
         ┌───────────────────────┬──────────┴────────────┬────────────────────────┐
         ▼                       ▼                       ▼                        ▼
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────────┐
│ Mod 2: Doctores  │  │ Mod 5: Gestión de│  │ Mod 6: Pacientes │  │ Mod 9: Reportes y    │
│                  │  │ Citas            │  │                  │  │ Estadísticas         │
│ Autoría Médica   │  │ Cierre Cita (1:1)│  │ Historial Clínico│  │ Reportes de Atenciones│
└──────────────────┘  └──────────────────┘  └──────────────────┘  └──────────────────────┘
```

---

## 13. Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── NotasConsultaController.php         # Controller API REST Notas
│   │   │   └── Web/
│   │   │       └── DoctorWebController.php          # Controller Web SSR Doctor (Agenda y Diagnóstico)
│   │   ├── Repository/
│   │   │   └── NotasConsultaRepository.php         # Lógica de notas y completado de citas
│   │   └── Requests/
│   │       └── StoreNotaConsultaRequest.php        # Form Request validación de diagnóstico
│   └── Models/
│       └── NotaConsulta.php                        # Modelo Eloquent notas_consulta
├── database/
│   └── migrations/
│       └── 2026_01_01_000010_crear_tabla_notas_consulta.php # Migración tabla notas_consulta
├── resources/views/
│   └── doctor/
│       ├── agenda.blade.php                        # Vista Agenda del Médico y línea de tiempo
│       └── diagnostico.blade.php                   # Formulario de registro de consulta
└── routes/
    ├── api.php                                     # Endpoints API REST (/api/registrarNota/{citaId}, etc.)
    └── web.php                                     # Rutas Web SSR (/mi-agenda, /diagnostico/{citaId}, etc.)
```

---

> **Módulo anterior:** [06 - Gestión de Pacientes](./06-Gestion-de-Pacientes.md)  
> **Siguiente módulo:** [08 - Perfil de Usuario](./08-Perfil-de-Usuario.md)
