# 📅 Módulo 5: Gestión de Citas Médicas

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura del Módulo](#2-diagrama-de-arquitectura-del-módulo)
3. [Modelo de Datos Relacional y Máquina de Estados](#3-modelo-de-datos-relacional-y-máquina-de-estados)
4. [Capa de Base de Datos — Migraciones](#4-capa-de-base-de-datos--migraciones)
5. [Capa de Modelos (Eloquent ORM)](#5-capa-de-modelos-eloquent-orm)
6. [Capa de Repositorios (Lógica de Negocio y Transiciones de Estado)](#6-capa-de-repositorios-lógica-de-negocio-y-transiciones-de-estado)
7. [Capa de Validaciones (Form Requests)](#7-capa-de-validaciones-form-requests)
8. [Capa de Controladores (API REST vs Blade SSR)](#8-capa-de-controladores-api-rest-vs-blade-ssr)
9. [Capa de Vistas (Blade SSR UI y Agendamiento)](#9-capa-de-vistas-blade-ssr-ui-y-agendamiento)
10. [Rutas (API y Web)](#10-rutas-api-y-web)
11. [Flujos Completos de Operación](#11-flujos-completos-de-operación)
12. [Relación con Otros Módulos](#12-relación-con-otros-módulos)
13. [Mapa de Archivos del Módulo](#13-mapa-de-archivos-del-módulo)

---

## 1. Visión General del Módulo

El módulo de **Gestión de Citas Médicas** es el corazón operacional del sistema. Coordina el flujo transaccional completo de una consulta médica desde la solicitud inicial del paciente hasta la finalización del acto médico o su eventual cancelación.

### Responsabilidades Principales

| Responsabilidad | Descripción Técnica |
|---|---|
| **Ciclo de Vida de la Cita** | Gestionar la máquina de estados estricta (`agendada` $\rightarrow$ `confirmada` $\rightarrow$ `en_consulta` $\rightarrow$ `completada` o `cancelada`). |
| **Generación de Código Único** | Asignar un token corto de referencia único (`CITA-XXXXXX`) para identificación rápida del paciente en recepción. |
| **Validación Concurrente de Agenda** | Evitar reservas dobles asegurando la disponibilidad previa en el `DisponibilidadRepository` antes de insertar. |
| **Flujo de Check-In** | Permitir a recepcionistas marcar la llegada presencial del paciente a la clínica (`confirmada`). |
| **Atención Médica Activa** | Permitir al médico iniciar la consulta (`en_consulta`) y vincular la nota médica final (`completada`). |
| **Reglas de Cancelación Exigibles** | Imponer políticas de restricción (ej. el paciente solo puede cancelar con al menos 2 horas de anticipación). |

### Roles que Interactúan con este Módulo

| Rol | Permisos y Operaciones |
|---|---|
| **Paciente** | Agendar citas desde la app móvil, ver "Mis Citas", cancelar con $\ge 2$ horas de anticipación. |
| **Recepcionista** | Ver agenda completa, crear citas presenciales/telefónicas, realizar Check-In, reprogramar y cancelar. |
| **Doctor** | Consultar su agenda del día, iniciar consulta médica (`en_consulta`) y completar la cita (`completada`). |
| **Administrador** | Supervisión global, cancelación administrativa, reprogramaciones y reportes de desempeño. |

---

## 2. Diagrama de Arquitectura del Módulo

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PETICIÓN HTTP                                       │
│          API REST (/api/agendarCita, /misCitas) │  Web SSR (/citas, /citas/agendar)    │
└───────────────────────────┬───────────────────────────────┬────────────────────────────┘
                            │                               │
                            ▼                               ▼
               ┌──────────────────────────┐    ┌──────────────────────────────┐
               │     CitasController      │    │      CitasWebController      │
               │        (API JSON)        │    │     (Blade SSR + Session)    │
               └────────────┬─────────────┘    └──────────────┬───────────────┘
                            │                                 │
                            │   ┌─────────────────────────┐   │
                            └──►│      Form Requests      │◄──┘
                                │ (StoreCita / Update...) │
                                └────────────┬────────────┘
                                             │
                                             ▼
                             ┌────────────────────────────────┐
                             │        CitasRepository         │
                             │  • obtenerCitas()              │
                             │  • registrarCita()             │
                             │  • reprogramarCita()           │
                             │  • checkInCita()               │
                             │  • iniciarConsulta()           │
                             │  • completarCita()             │
                             │  • cancelarCita()              │
                             └───────────────┬────────────────┘
                                             │
                       ┌─────────────────────┴─────────────────────┐
                       ▼                                           ▼
         ┌───────────────────────────┐               ┌───────────────────────────┐
         │ DisponibilidadRepository  │               │        Modelo Cita        │
         │ (Verifica slot libre)     │               │  - $table = 'citas'       │
         └───────────────────────────┘               └─────────────┬─────────────┘
                                                                   │
                                                                   ▼
                                                     ┌───────────────────────────┐
                                                     │    Tabla: citas (DB)      │
                                                     └───────────────────────────┘
```

---

## 3. Modelo de Datos Relacional y Máquina de Estados

### 3.1 Máquina de Estados de la Cita

El campo `estado` de una cita evoluciona según un flujo determinista de estados:

```
                  ┌──────────────┐
                  │   AGENDADA   │ ◄─── (Estado inicial al crear la reserva)
                  └──────┬───────┘
                         │
             ┌───────────┴───────────┐
             │ Check-In              │ Cancelación (Admin/Recepcionista/Paciente)
             ▼                       ▼
      ┌──────────────┐       ┌──────────────┐
      │  CONFIRMADA  │       │  CANCELADA   │ (Estado final de término prematuro)
      └──────┬───────┘       └──────────────┘
             │
             │ Iniciar Consulta (Doctor)
             ▼
      ┌──────────────┐
      │ EN_CONSULTA  │
      └──────┬───────┘
             │
             │ Completar / Registrar Nota (Doctor)
             ▼
      ┌──────────────┐
      │  COMPLETADA  │ (Estado final exitoso)
      └──────────────┘
```

---

### 3.2 Diagrama Entidad-Relación

```
┌─────────────────────┐             ┌─────────────────────────┐             ┌─────────────────────┐
│  perfiles_paciente  │             │          citas          │             │   perfiles_doctor   │
│─────────────────────│             │─────────────────────────│             │─────────────────────│
│ id (PK)             │◄────────────│ perfil_paciente_id (FK) │────────────►│ id (PK)             │
│ usuario_id (FK)     │ 1:N         │ perfil_doctor_id (FK)   │ 1:N         │ usuario_id (FK)     │
│ numero_expediente   │             │ especialidad_id (FK) ───┼──┐          │ cedula_profesional  │
└─────────────────────┘             │ codigo_referencia (UNIQ)│  │          └─────────────────────┘
                                    │ fecha_cita (DATE)       │  │
                                    │ hora_cita (TIME)        │  │          ┌─────────────────────┐
                                    │ duracion_minutos (INT)  │  │          │   especialidades    │
                                    │ estado (ENUM)           │  │          │─────────────────────│
                                    │ motivo_cancelacion      │  └─────────►│ id (PK)             │
                                    │ cancelado_por (FK NULL) │             │ nombre              │
                                    │ checkin_por (FK NULL)   │             └─────────────────────┘
                                    │ timestamps              │
                                    └────────────┬────────────┘
                                                 │
                                                 │ 1:1
                                                 ▼
                                    ┌─────────────────────────┐
                                    │     notas_consulta      │
                                    │─────────────────────────│
                                    │ id (PK)                 │
                                    │ cita_id (FK)            │
                                    │ diagnostico             │
                                    │ tratamiento             │
                                    └─────────────────────────┘
```

---

## 4. Capa de Base de Datos — Migraciones

**Archivo:** `database/migrations/2026_01_01_000009_crear_tabla_citas.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_paciente_id')->constrained('perfiles_paciente')->onDelete('cascade');
            $table->foreignId('perfil_doctor_id')->constrained('perfiles_doctor')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->string('codigo_referencia')->unique();
            $table->date('fecha_cita');
            $table->time('hora_cita');
            $table->integer('duracion_minutos')->default(30);
            $table->enum('estado', ['agendada', 'confirmada', 'en_consulta', 'completada', 'cancelada'])->default('agendada');
            $table->text('motivo_cancelacion')->nullable();
            $table->foreignId('cancelado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamp('cancelado_en')->nullable();
            $table->timestamp('checkin_en')->nullable();
            $table->foreignId('checkin_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
```

### Análisis Técnico de Columnas de `citas`

| Columna | Tipo SQL | Constraint / Index | Propósito y Comportamiento |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `PRIMARY KEY AUTO_INCREMENT` | Identificador autoincremental de la cita. |
| `perfil_paciente_id` | `BIGINT UNSIGNED` | `FK constrained('perfiles_paciente') onDelete('cascade')` | Relación con el paciente citado. |
| `perfil_doctor_id` | `BIGINT UNSIGNED` | `FK constrained('perfiles_doctor') onDelete('cascade')` | Relación con el médico asignado. |
| `especialidad_id` | `BIGINT UNSIGNED` | `FK constrained('especialidades') onDelete('cascade')` | Relación con la especialidad médica requerida. |
| `codigo_referencia` | `VARCHAR(255)` | `UNIQUE INDEX` | Código alfanumérico único generado automáticamente (ej. `CITA-A3F9B2`). |
| `fecha_cita` | `DATE` | `NOT NULL` | Fecha de la cita en formato `YYYY-MM-DD`. |
| `hora_cita` | `TIME` | `NOT NULL` | Hora de la cita en formato `HH:MM:SS`. |
| `duracion_minutos` | `INTEGER` | `DEFAULT 30` | Duración del bloque reservado en minutos. |
| `estado` | `ENUM` | `DEFAULT 'agendada'` | Transición de estado (`agendada`, `confirmada`, `en_consulta`, `completada`, `cancelada`). |
| `motivo_cancelacion` | `TEXT` | `NULLABLE` | Razón documentada en caso de cancelación de la cita. |
| `cancelado_por` | `BIGINT UNSIGNED` | `FK constrained('usuarios') onDelete('set null')` | Usuario que registró la cancelación (para auditoría). |
| `cancelado_en` | `TIMESTAMP` | `NULLABLE` | Marca de tiempo de cuándo ocurrió la cancelación. |
| `checkin_en` | `TIMESTAMP` | `NULLABLE` | Marca de tiempo de cuando la recepcionista registró la recepción del paciente. |
| `checkin_por` | `BIGINT UNSIGNED` | `FK constrained('usuarios') onDelete('set null')` | Recepcionista o usuario que realizó el check-in. |

---

## 5. Capa de Modelos (Eloquent ORM)

**Archivo:** `app/Models/Cita.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'perfil_paciente_id',
        'perfil_doctor_id',
        'especialidad_id',
        'codigo_referencia',
        'fecha_cita',
        'hora_cita',
        'duracion_minutos',
        'estado',
        'motivo_cancelacion',
        'cancelado_por',
        'cancelado_en',
        'checkin_en',
        'checkin_por',
    ];

    protected $casts = [
        'fecha_cita'   => 'date',
        'cancelado_en' => 'datetime',
        'checkin_en'   => 'datetime',
    ];

    public function perfilPaciente()
    {
        return $this->belongsTo(PerfilPaciente::class, 'perfil_paciente_id');
    }

    public function perfilDoctor()
    {
        return $this->belongsTo(PerfilDoctor::class, 'perfil_doctor_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function notaConsulta()
    {
        return $this->hasOne(NotaConsulta::class, 'cita_id');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(Usuario::class, 'cancelado_por');
    }

    public function checkinPor()
    {
        return $this->belongsTo(Usuario::class, 'checkin_por');
    }
}
```

---

## 6. Capa de Repositorios (Lógica de Negocio y Transiciones de Estado)

**Archivo:** `app/Http/Repository/CitasRepository.php`

Concentra la lógica de agendamiento, verificación de concurrencia y gestión de estados de las citas.

```php
<?php

namespace App\Http\Repository;

use App\Models\Cita;
use Carbon\Carbon;
use Exception;

class CitasRepository
{
    protected DisponibilidadRepository $disponibilidadRepo;

    public function __construct(DisponibilidadRepository $disponibilidadRepo)
    {
        $this->disponibilidadRepo = $disponibilidadRepo;
    }

    public function obtenerCitas(array $filtros = [])
    {
        try {
            $query = Cita::with(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad']);

            if (!empty($filtros['estado'])) {
                $query->where('estado', $filtros['estado']);
            }
            if (!empty($filtros['doctor_id'])) {
                $query->where('perfil_doctor_id', $filtros['doctor_id']);
            }
            if (!empty($filtros['paciente_id'])) {
                $query->where('perfil_paciente_id', $filtros['paciente_id']);
            }
            if (!empty($filtros['fecha'])) {
                $query->where('fecha_cita', $filtros['fecha']);
            }
            if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
                $query->whereBetween('fecha_cita', [$filtros['fecha_inicio'], $filtros['fecha_fin']]);
            }

            $citas = $query->orderBy('fecha_cita')->orderBy('hora_cita')
                ->paginate($filtros['por_pagina'] ?? 15);

            return [
                'mensaje' => 'Citas obtenidas correctamente',
                'data'    => $citas,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarCita(array $data)
    {
        try {
            // 1. Verificar si ya existe una cita activa en ese mismo slot de tiempo
            $ocupado = Cita::where('perfil_doctor_id', $data['perfil_doctor_id'])
                ->whereDate('fecha_cita', $data['fecha_cita'])
                ->where('hora_cita', $data['hora_cita'])
                ->whereIn('estado', ['agendada', 'confirmada', 'en_consulta'])
                ->exists();

            if ($ocupado) {
                return ['mensaje' => 'Ya existe una cita agendada para este doctor en ese horario.'];
            }

            // 2. Verificar la disponibilidad médica (Horario regular y Bloqueos)
            $disponible = $this->disponibilidadRepo->verificarDisponibilidad(
                $data['perfil_doctor_id'],
                $data['fecha_cita'],
                $data['hora_cita']
            );

            if (!$disponible) {
                return ['mensaje' => 'El horario seleccionado no está disponible para este doctor.'];
            }

            // 3. Generar Código Único de Referencia (Ej: CITA-A3F9B2)
            $codigoReferencia = 'CITA-' . strtoupper(substr(uniqid(), -6));

            $cita = Cita::create([
                'perfil_paciente_id' => $data['perfil_paciente_id'],
                'perfil_doctor_id'   => $data['perfil_doctor_id'],
                'especialidad_id'    => $data['especialidad_id'],
                'codigo_referencia'  => $codigoReferencia,
                'fecha_cita'         => $data['fecha_cita'],
                'hora_cita'          => $data['hora_cita'],
                'duracion_minutos'   => $data['duracion_minutos'] ?? 30,
                'estado'             => 'agendada',
            ]);

            return [
                'mensaje' => 'Cita registrada correctamente',
                'data'    => $cita->load(['perfilPaciente.usuario', 'perfilDoctor.usuario', 'especialidad']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function reprogramarCita(int $id, array $data)
    {
        try {
            $cita = Cita::find($id);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if (in_array($cita->estado, ['completada', 'cancelada'])) {
                return ['mensaje' => 'No se puede reprogramar una cita completada o cancelada.'];
            }

            $disponible = $this->disponibilidadRepo->verificarDisponibilidad(
                $cita->perfil_doctor_id,
                $data['fecha_cita'],
                $data['hora_cita']
            );

            if (!$disponible) {
                return ['mensaje' => 'El nuevo horario no está disponible para este doctor.'];
            }

            $ocupado = Cita::where('perfil_doctor_id', $cita->perfil_doctor_id)
                ->whereDate('fecha_cita', $data['fecha_cita'])
                ->where('hora_cita', $data['hora_cita'])
                ->where('id', '!=', $id)
                ->whereIn('estado', ['agendada', 'confirmada', 'en_consulta'])
                ->exists();

            if ($ocupado) {
                return ['mensaje' => 'Ya existe una cita en el nuevo horario seleccionado.'];
            }

            $cita->update([
                'fecha_cita' => $data['fecha_cita'],
                'hora_cita'  => $data['hora_cita'],
                'estado'     => 'agendada',
            ]);

            return [
                'mensaje' => 'Cita reprogramada correctamente',
                'data'    => $cita->load(['perfilPaciente.usuario', 'perfilDoctor.usuario']),
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function checkInCita(int $id, int $usuarioId)
    {
        try {
            $cita = Cita::find($id);
            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if (!in_array($cita->estado, ['agendada', 'confirmada'])) {
                return ['mensaje' => 'Solo se puede hacer check-in a citas agendadas o confirmadas.'];
            }

            $cita->update([
                'estado'      => 'confirmada',
                'checkin_en'  => now(),
                'checkin_por' => $usuarioId,
            ]);

            return [
                'mensaje' => 'Check-in registrado correctamente',
                'data'    => $cita,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function cancelarCitaPaciente(int $id, array $data, int $pacienteId, int $usuarioId)
    {
        try {
            $cita = Cita::where('id', $id)
                ->where('perfil_paciente_id', $pacienteId)
                ->first();

            if (!$cita) {
                return ['mensaje' => 'Cita no encontrada'];
            }

            if (in_array($cita->estado, ['completada', 'cancelada'])) {
                return ['mensaje' => 'La cita ya está completada o cancelada.'];
            }

            // Regla de Negocio: Cancelar con al menos 2 horas de anticipación
            $horaLimite = Carbon::parse($cita->fecha_cita->format('Y-m-d') . ' ' . $cita->hora_cita)->subHours(2);
            if (now()->greaterThan($horaLimite)) {
                return ['mensaje' => 'Solo puedes cancelar con al menos 2 horas de anticipación a la cita.'];
            }

            $cita->update([
                'estado'             => 'cancelada',
                'motivo_cancelacion' => $data['motivo_cancelacion'] ?? 'Cancelada por el paciente',
                'cancelado_por'      => $usuarioId,
                'cancelado_en'       => now(),
            ]);

            return [
                'mensaje' => 'Cita cancelada correctamente',
                'data'    => $cita,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
```

---

## 7. Capa de Validaciones (Form Requests)

### 7.1 `StoreCitaRequest`

**Archivo:** `app/Http/Requests/StoreCitaRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'perfil_paciente_id' => 'nullable|exists:perfiles_paciente,id',
            'perfil_doctor_id'   => 'required|exists:perfiles_doctor,id',
            'especialidad_id'    => 'required|exists:especialidades,id',
            'fecha_cita'         => 'required|date|after_or_equal:today',
            'hora_cita'          => 'required|date_format:H:i:s',
            'duracion_minutos'   => 'nullable|integer|min:10|max:120',
        ];
    }

    public function messages(): array
    {
        return [
            'perfil_doctor_id.required' => 'El doctor es requerido.',
            'especialidad_id.required'  => 'La especialidad es requerida.',
            'fecha_cita.required'       => 'La fecha de la cita es requerida.',
            'fecha_cita.after_or_equal' => 'La fecha de la cita no puede ser en el pasado.',
            'hora_cita.required'        => 'La hora de la cita es requerida.',
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

### 8.1 Controlador API (`CitasController`)

**Archivo:** `app/Http/Controllers/CitasController.php`

Expone la API REST consumida por la aplicación móvil (Pacientes) y sistemas integrados.

- `agendarCita(StoreCitaRequest $request)`: Extrae `$request->user()->perfilPaciente->id` y llama a `CitasRepository::registrarCitaPaciente()`.
- `misCitas(Request $request)`: Retorna las citas asociadas al paciente autenticado.
- `cancelarMiCita(CancelacionCitaRequest $request, int $id)`: Aplica la regla de cancelación con al menos 2 horas de anticipación.
- `checkInCita(Request $request, int $id)`: Registra el check-in llevado a cabo por recepción.
- `iniciarConsulta(int $id)` y `completarCita(int $id)`: Utilizados por el médico para avanzar la cita a `en_consulta` y `completada`.

---

### 8.2 Controlador Web (`CitasWebController`)

**Archivo:** `app/Http/Controllers/Web/CitasWebController.php`

Maneja el panel administrativo de citas con calculador semanal de agenda.

```php
public function index(Request $request)
{
    $fechaRef = $request->query('fecha') ? Carbon::parse($request->query('fecha')) : Carbon::now();
    $doctorId = $request->query('doctor_id');

    // Calcular lunes y domingo de la semana activa
    $startOfWeek = $fechaRef->copy()->startOfWeek(Carbon::MONDAY);
    $endOfWeek   = $fechaRef->copy()->endOfWeek(Carbon::SUNDAY);

    $params = [
        'fecha_inicio' => $startOfWeek->format('Y-m-d'),
        'fecha_fin'    => $endOfWeek->format('Y-m-d'),
    ];
    if ($doctorId) {
        $params['doctor_id'] = $doctorId;
    }

    $resCitas = $this->citasRepository->obtenerCitas($params);
    $citas    = $resCitas['data'] ?? [];

    $resDoctores = $this->doctoresRepository->obtenerDoctores();
    $doctores    = $resDoctores['data'] ?? [];

    return view('citas.index', compact('citas', 'doctores', 'startOfWeek', 'endOfWeek', 'fechaRef', 'doctorId'));
}
```

---

## 9. Capa de Vistas (Blade SSR UI y Agendamiento)

El módulo web cuenta con 3 vistas especializadas:

1. **`resources/views/citas/index.blade.php`**: Tablero semanal de citas filtrable por fecha y doctor, con indicadores visuales de estado (`agendada`, `confirmada`, `completada`, `cancelada`) y botones de Check-in.
2. **`resources/views/citas/agendar.blade.php`**: Formulario interactivo para recepcionistas con selección dinámica de paciente, doctor, especialidad y horarios disponibles.
3. **`resources/views/citas/detalle.blade.php`**: Ficha detallada de la cita con expediente del paciente, código de referencia, datos del médico y nota médica asociada.

---

## 10. Rutas (API y Web)

### 10.1 Rutas API (`routes/api.php`)

```php
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {

    // Rutas exclusivas del Paciente (App Móvil)
    Route::middleware(['role:paciente'])->group(function () {
        Route::get('/misCitas', [CitasController::class, 'misCitas']);
        Route::post('/agendarCita', [CitasController::class, 'agendarCita']);
        Route::get('/miCita/{id}', [CitasController::class, 'miCita']);
        Route::patch('/cancelarMiCita/{id}', [CitasController::class, 'cancelarMiCita']);
    });

    // Rutas exclusivas del Médico
    Route::middleware(['role:doctor'])->group(function () {
        Route::patch('/iniciarConsulta/{id}', [CitasController::class, 'iniciarConsulta']);
        Route::patch('/completarCita/{id}', [CitasController::class, 'completarCita']);
    });

    // Rutas para Recepcionista y Administrador
    Route::middleware(['role:admin,recepcionista'])->group(function () {
        Route::get('/obtenerCitas', [CitasController::class, 'obtenerCitas']);
        Route::post('/registrarCita', [CitasController::class, 'registrarCita']);
        Route::get('/obtenerCita/{id}', [CitasController::class, 'obtenerCita']);
        Route::put('/reprogramarCita/{id}', [CitasController::class, 'reprogramarCita']);
        Route::patch('/cancelarCita/{id}', [CitasController::class, 'cancelarCita']);
        Route::patch('/checkInCita/{id}', [CitasController::class, 'checkInCita']);
    });
});
```

---

## 11. Flujos Completos de Operación

### 11.1 Flujo de Agendamiento desde App Móvil (Paciente)

```
   PACIENTE (APP MÓVIL)                   CitasController                         CitasRepository                     DisponibilidadRepo
            │                                    │                                       │                                    │
            │ POST /api/agendarCita              │                                       │                                    │
            │ (doctor_id, fecha, hora)           │                                       │                                    │
            ├───────────────────────────────────►│                                       │                                    │
            │                                    │ registrarCitaPaciente(...)            │                                    │
            │                                    ├──────────────────────────────────────►│                                    │
            │                                    │                                       │ 1. Validar cita duplicada paciente │
            │                                       │ 2. Cita::whereDate(ocupado)        │
            │                                    │                                       │ 3. verificarDisponibilidad()       │
            │                                    │                                       ├───────────────────────────────────►│
            │                                    │                                       │◄───────────────────────────────────┤
            │                                    │                                       │ 4. Generar 'CITA-XXXXXX'           │
            │                                    │                                       │ 5. Cita::create([estado=>agendada])│
            │                                    │                                       │                                    │
            │ JSON 200 OK (Cita + Ref Code)      │◄──────────────────────────────────────┤                                    │
            │◄───────────────────────────────────┤                                       │                                    │
```

---

## 12. Relación con Otros Módulos

```
                               ┌──────────────────────────┐
                               │    Módulo 5: GESTIÓN     │
                               │        DE CITAS          │
                               └────────────┬─────────────┘
                                            │
         ┌───────────────────────┬──────────┴────────────┬────────────────────────┐
         ▼                       ▼                       ▼                        ▼
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────────┐
│ Mod 4: Horarios y│  │ Mod 6: Pacientes │  │ Mod 7: Notas de  │  │ Mod 9: Reportes y    │
│ Disponibilidad   │  │                  │  │ Consulta         │  │ Estadísticas         │
│ Valida Slots     │  │ Enlace Paciente  │  │ Vinculación 1:1  │  │ Métricas de Citas    │
└──────────────────┘  └──────────────────┘  └──────────────────┘  └──────────────────────┘
```

---

## 13. Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CitasController.php                 # API REST (Paciente, Médico, Recepción)
│   │   │   └── Web/
│   │   │       └── CitasWebController.php          # Web SSR (Panel Semanal de Agenda)
│   │   ├── Repository/
│   │   │   └── CitasRepository.php                 # Lógica de estados, check-in, validaciones
│   │   └── Requests/
│   │       ├── StoreCitaRequest.php                # Validaciones de Agendamiento
│   │       ├── UpdateCitaRequest.php               # Validaciones de Reprogramación
│   │       └── CancelacionCitaRequest.php          # Validaciones de Cancelación
│   └── Models/
│       └── Cita.php                                # Modelo Eloquent y Transiciones
├── database/
│   └── migrations/
│       └── 2026_01_01_000009_crear_tabla_citas.php  # Migración de Tabla Citas
├── resources/views/
│   └── citas/
│       ├── index.blade.php                         # Tablero Semanal de Agenda
│       ├── agendar.blade.php                       # Formulario de Reserva
│       └── detalle.blade.php                       # Ficha Individual de Cita
└── routes/
    ├── api.php                                     # Endpoints REST (misCitas, agendarCita, etc.)
    └── web.php                                     # Rutas Panel Admin (/citas, /citas/agendar)
```

---

> **Módulo anterior:** [04 - Horarios y Bloqueos](./04-Horarios-y-Bloqueos.md)  
> **Siguiente módulo:** [06 - Gestión de Pacientes](./06-Gestion-de-Pacientes.md)
