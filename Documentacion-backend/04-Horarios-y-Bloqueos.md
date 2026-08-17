# 🕐 Módulo 4: Horarios, Bloqueos y Disponibilidad

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura del Módulo](#2-diagrama-de-arquitectura-del-módulo)
3. [Modelo de Datos Relacional](#3-modelo-de-datos-relacional)
4. [Capa de Base de Datos — Migraciones](#4-capa-de-base-de-datos--migraciones)
5. [Capa de Modelos (Eloquent ORM)](#5-capa-de-modelos-eloquent-orm)
6. [Capa de Repositorios (Lógica de Negocio y Motor de Disponibilidad)](#6-capa-de-repositorios-lógica-de-negocio-y-motor-de-disponibilidad)
7. [Capa de Validaciones (Form Requests)](#7-capa-de-validaciones-form-requests)
8. [Capa de Controladores (API REST vs Blade SSR)](#8-capa-de-controladores-api-rest-vs-blade-ssr)
9. [Capa de Vistas (Blade SSR UI y Componentes)](#9-capa-de-vistas-blade-ssr-ui-y-componentes)
10. [Rutas (API y Web)](#10-rutas-api-y-web)
11. [Flujos Completos de Operación](#11-flujos-completos-de-operación)
12. [Relación con Otros Módulos](#12-relación-con-otros-módulos)
13. [Mapa de Archivos del Módulo](#13-mapa-de-archivos-del-módulo)

---

## 1. Visión General del Módulo

El módulo de **Horarios, Bloqueos y Disponibilidad** es el motor cronológico y operacional del sistema de citas médicas. Su función primordial es definir las ventanas de tiempo en las que un profesional médico atiende pacientes, gestionar las excepciones de su agenda (vacaciones, ausencias o permisos) y calcular dinámicamente en tiempo real los intervalos (*slots*) libres para el agendamiento de citas.

### Responsabilidades Principales

| Responsabilidad | Descripción Técnica |
|---|---|
| **Definición de Jornada Regular** | Establecer la disponibilidad semanal recurrente de un médico (días, hora de inicio, hora de fin y duración estimada por consulta). |
| **Prevención de Solapamiento** | Validar algorítmicamente a nivel de repositorio que no existan tramos de horarios superpuestos para un mismo doctor. |
| **Gestión de Excepciones y Bloqueos** | Registrar fechas o rangos de horas específicos donde la atención queda inhabilitada (vacaciones, incapacidades, eventos). |
| **Detección de Citas Afectadas** | Evaluar y alertar si la creación de un bloqueo afecta a citas que ya se encontraban agendadas previamente. |
| **Motor de Cálculo de Slots Libres** | Generar dinámicamente los turnos atendibles para una fecha dada, cruzando el horario base, los bloqueos activos y las citas existentes. |

### Roles que Interactúan con este Módulo

| Rol | Permisos |
|---|---|
| **Administrador** | Configurar horarios semanales, crear bloqueos y eliminar restricciones para cualquier médico. |
| **Médico** | Consultar su agenda de disponibilidad regular e identificar sus bloqueos aplicados. |
| **Paciente** | Consultar en tiempo real los slots de tiempo disponibles para agendar una cita (vía API o Portal Web). |

---

## 2. Diagrama de Arquitectura del Módulo

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PETICIÓN HTTP                                       │
│    API REST (/api/obtenerDisponibilidad)   │   Web SSR (/doctores/{id}/horarios)       │
└───────────────────────────┬───────────────────────────────┬────────────────────────────┘
                            │                               │
                            ▼                               ▼
       ┌───────────────────────────┐         ┌──────────────────────────────┐
       │ HorariosController /      │         │    DoctoresWebController     │
       │ BloqueosController /      │         │     (Blade SSR + Session)    │
       │ DisponibilidadController  │         └──────────────┬───────────────┘
       └────────────┬──────────────┘                        │
                    │                                       │
                    │   ┌───────────────────────────────┐   │
                    └──►│ Form Requests                 │◄──┘
                        │ (StoreHorario / StoreBloqueo) │
                        └──────────────┬────────────────┘
                                       │
            ┌──────────────────────────┼──────────────────────────┐
            ▼                          ▼                          ▼
┌───────────────────────┐  ┌───────────────────────┐  ┌────────────────────────┐
│  HorariosRepository   │  │  BloqueosRepository   │  │DisponibilidadRepository│
│ • obtenerHorarios()   │  │ • obtenerBloqueos()   │  │ • obtenerSlots...()    │
│ • registrarHorario()  │  │ • registrarBloqueo()  │  │ • verificarDisponib..()│
│ • actualizarHorario() │  │ • eliminarBloqueo()   │  └───────────┬────────────┘
│ • eliminarHorario()   │  └───────────┬───────────┘              │
│ • verificarSolap...() │              │                          │
└───────────┬───────────┘              │                          │
            │                          │                          │
            ▼                          ▼                          ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                   MODELOS ELOQUENT                                     │
│  ┌──────────────────────┐    ┌──────────────────────┐    ┌──────────────────────────┐  │
│  │    HorarioDoctor     │    │    BloqueoHorario    │    │           Cita           │  │
│  │ ($table='horarios_') │    │ ($table='bloqueos_') │    │   (Consulta Ocupados)    │  │
│  └──────────────────────┘    └──────────────────────┘    └──────────────────────────┘  │
└──────────────────────────────────────────┬─────────────────────────────────────────────┘
                                           │
                                           ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                   BASE DE DATOS                                        │
│         [horarios_doctor]        [bloqueos_horario]               [citas]              │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Modelo de Datos Relacional

El módulo se compone de dos tablas principales (`horarios_doctor` y `bloqueos_horario`), vinculadas a la tabla `perfiles_doctor` y a la tabla `usuarios`. Además, consulta la tabla `citas` para determinar la disponibilidad en tiempo real.

```
┌─────────────────────┐
│   perfiles_doctor   │
│─────────────────────│
│ id (PK)             │◄─────────────────────────────┐
│ usuario_id (FK)     │                              │
│ ...                 │                              │
└──────────┬──────────┘                              │
           │                                         │
           │ 1:N                                     │ 1:N
           ▼                                         ▼
┌─────────────────────────┐             ┌─────────────────────────┐
│     horarios_doctor     │             │    bloqueos_horario     │
│─────────────────────────│             │─────────────────────────│
│ id (PK)                 │             │ id (PK)                 │
│ perfil_doctor_id (FK)   │             │ perfil_doctor_id (FK)   │
│ dia_semana (ENUM)       │             │ fecha_bloqueo (DATE)    │
│ hora_inicio (TIME)      │             │ hora_inicio_blq (TIME)  │
│ hora_fin (TIME)         │             │ hora_fin_blq (TIME)     │
│ duracion_cons_min (INT) │             │ motivo (VARCHAR NULL)   │
│ activo (BOOLEAN)        │             │ creado_por (FK NULL) ───┼───┐
│ timestamps              │             │ timestamps              │   │
└─────────────────────────┘             └─────────────────────────┘   │
                                                                      │
                                                                      │ 1:N (Inversa)
                                                                      ▼
                                                        ┌─────────────────────────┐
                                                        │        usuarios         │
                                                        │─────────────────────────│
                                                        │ id (PK)                 │
                                                        │ nombre                  │
                                                        │ ...                     │
                                                        └─────────────────────────┘
```

---

## 4. Capa de Base de Datos — Migraciones

### 4.1 Tabla `horarios_doctor`

**Archivo:** `database/migrations/2026_01_01_000007_crear_tabla_horarios_doctor.php`

Establece las franjas de atención semanales recurrentes del médico.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_doctor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_doctor_id')->constrained('perfiles_doctor')->onDelete('cascade');
            $table->enum('dia_semana', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('duracion_consulta_minutos')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_doctor');
    }
};
```

### Análisis Técnico de Columnas de `horarios_doctor`

| Columna | Tipo de Dato SQL | Constraint | Explicación Técnica |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `PRIMARY KEY AUTO_INCREMENT` | Clave primaria del horario. |
| `perfil_doctor_id` | `BIGINT UNSIGNED` | `FK constrained('perfiles_doctor') onDelete('cascade')` | Relación con el perfil del médico. Si el perfil es eliminado, se borran sus horarios. |
| `dia_semana` | `ENUM` | `'lunes' ... 'domingo'` | Restringe los valores posibles únicamente a los 7 días de la semana en español. |
| `hora_inicio` | `TIME` | `NOT NULL` | Hora exacta de inicio de la jornada (ej. `08:00:00`). |
| `hora_fin` | `TIME` | `NOT NULL` | Hora exacta de fin de la jornada (ej. `14:00:00`). |
| `duracion_consulta_minutos` | `INTEGER` | `DEFAULT 30` | Duración fija estimada para cada turno médico en minutos. |
| `activo` | `BOOLEAN` | `DEFAULT 1` | Bandera booleana de habilitación de la franja. |

---

### 4.2 Tabla `bloqueos_horario`

**Archivo:** `database/migrations/2026_01_01_000008_crear_tabla_bloqueos_horario.php`

Registra excepciones temporales que deshabilitan la atención en fechas o rangos específicos.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloqueos_horario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_doctor_id')->constrained('perfiles_doctor')->onDelete('cascade');
            $table->date('fecha_bloqueo');
            $table->time('hora_inicio_bloqueo')->nullable();
            $table->time('hora_fin_bloqueo')->nullable();
            $table->string('motivo')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueos_horario');
    }
};
```

### Análisis Técnico de Columnas de `bloqueos_horario`

| Columna | Tipo de Dato SQL | Constraint | Explicación Técnica |
|---|---|---|---|
| `fecha_bloqueo` | `DATE` | `NOT NULL` | Fecha del día bloqueado (ej. `2026-08-15`). |
| `hora_inicio_bloqueo` | `TIME` | `NULLABLE` | Hora de inicio del bloqueo parcial. Si es `NULL`, representa bloqueo del **día completo**. |
| `hora_fin_bloqueo` | `TIME` | `NULLABLE` | Hora de fin del bloqueo parcial. |
| `motivo` | `VARCHAR(255)` | `NULLABLE` | Justificación del bloqueo (ej. "Vacaciones", "Congreso médico"). |
| `creado_por` | `BIGINT UNSIGNED` | `FK constrained('usuarios') onDelete('set null')` | Auditoría de qué usuario (admin) registró el bloqueo. |

---

## 5. Capa de Modelos (Eloquent ORM)

### 5.1 Modelo `HorarioDoctor`

**Archivo:** `app/Models/HorarioDoctor.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioDoctor extends Model
{
    use HasFactory;

    protected $table = 'horarios_doctor';

    protected $fillable = [
        'perfil_doctor_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'duracion_consulta_minutos',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(PerfilDoctor::class, 'perfil_doctor_id');
    }
}
```

---

### 5.2 Modelo `BloqueoHorario`

**Archivo:** `app/Models/BloqueoHorario.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueoHorario extends Model
{
    use HasFactory;

    protected $table = 'bloqueos_horario';

    protected $fillable = [
        'perfil_doctor_id',
        'fecha_bloqueo',
        'hora_inicio_bloqueo',
        'hora_fin_bloqueo',
        'motivo',
        'creado_por',
    ];

    protected $casts = [
        'fecha_bloqueo' => 'date',
    ];

    public function doctor()
    {
        return $this->belongsTo(PerfilDoctor::class, 'perfil_doctor_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }
}
```

---

## 6. Capa de Repositorios (Lógica de Negocio y Motor de Disponibilidad)

### 6.1 `HorariosRepository`

**Archivo:** `app/Http/Repository/HorariosRepository.php`

Gestiona los horarios regulares con algoritmos de prevención de solapamientos.

```php
<?php

namespace App\Http\Repository;

use App\Models\HorarioDoctor;
use Exception;

class HorariosRepository
{
    public function obtenerHorarios(int $doctorId)
    {
        try {
            $horarios = HorarioDoctor::where('perfil_doctor_id', $doctorId)
                ->orderByRaw("FIELD(dia_semana, 'lunes','martes','miercoles','jueves','viernes','sabado','domingo')")
                ->get();

            return [
                'mensaje' => 'Horarios obtenidos correctamente',
                'data'    => $horarios,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarHorario(int $doctorId, array $data)
    {
        try {
            // Verificar solapamiento de horarios
            $solapamiento = $this->verificarSolapamiento(
                $doctorId, 
                $data['dia_semana'], 
                $data['hora_inicio'], 
                $data['hora_fin']
            );
            
            if ($solapamiento) {
                return ['mensaje' => 'Ya existe un horario solapado para este doctor en ese día y horario.'];
            }

            $horario = HorarioDoctor::create([
                'perfil_doctor_id'          => $doctorId,
                'dia_semana'                => $data['dia_semana'],
                'hora_inicio'               => $data['hora_inicio'],
                'hora_fin'                  => $data['hora_fin'],
                'duracion_consulta_minutos' => $data['duracion_consulta_minutos'] ?? 30,
                'activo'                    => true,
            ]);

            return [
                'mensaje' => 'Horario registrado correctamente',
                'data'    => $horario,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function actualizarHorario(int $id, array $data)
    {
        try {
            $horario = HorarioDoctor::find($id);
            if (!$horario) {
                return ['mensaje' => 'Horario no encontrado'];
            }

            $diaFinal    = $data['dia_semana']  ?? $horario->dia_semana;
            $inicioFinal  = $data['hora_inicio'] ?? $horario->hora_inicio;
            $finFinal     = $data['hora_fin']    ?? $horario->hora_fin;

            // Verificar solapamiento excluyendo el horario actual
            $solapamiento = $this->verificarSolapamiento(
                $horario->perfil_doctor_id, 
                $diaFinal, 
                $inicioFinal, 
                $finFinal, 
                $id
            );

            if ($solapamiento) {
                return ['mensaje' => 'El horario actualizado se solaparía con otro existente.'];
            }

            $horario->update([
                'dia_semana'                => $diaFinal,
                'hora_inicio'               => $inicioFinal,
                'hora_fin'                  => $finFinal,
                'duracion_consulta_minutos' => $data['duracion_consulta_minutos'] ?? $horario->duracion_consulta_minutos,
                'activo'                    => $data['activo'] ?? $horario->activo,
            ]);

            return [
                'mensaje' => 'Horario actualizado correctamente',
                'data'    => $horario,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function eliminarHorario(int $id)
    {
        try {
            $horario = HorarioDoctor::find($id);
            if (!$horario) {
                return ['mensaje' => 'Horario no encontrado'];
            }
            $horario->delete();
            return ['mensaje' => 'Horario eliminado correctamente'];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    /**
     * Algoritmo de detección de solapamiento de tramos horarios en SQL.
     */
    public function verificarSolapamiento(int $doctorId, string $dia, string $inicio, string $fin, int $excluirId = null): bool
    {
        $query = HorarioDoctor::where('perfil_doctor_id', $doctorId)
            ->where('dia_semana', $dia)
            ->where('activo', true)
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('hora_inicio', [$inicio, $fin])
                    ->orWhereBetween('hora_fin', [$inicio, $fin])
                    ->orWhere(function ($q2) use ($inicio, $fin) {
                        $q2->where('hora_inicio', '<=', $inicio)
                            ->where('hora_fin', '>=', $fin);
                    });
            });

        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }
}
```

### Explicación del Algoritmo de Solapamiento
El método `verificarSolapamiento` evalúa los 3 casos matemáticos en que dos rangos de tiempo $[A_{inicio}, A_{fin}]$ y $[B_{inicio}, B_{fin}]$ se traslapan:
1. **$B_{inicio}$ cae dentro de $[A_{inicio}, A_{fin}]$:** Proprobado por `whereBetween('hora_inicio', [$inicio, $fin])`.
2. **$B_{fin}$ cae dentro de $[A_{inicio}, A_{fin}]$:** Proprobado por `orWhereBetween('hora_fin', [$inicio, $fin])`.
3. **El nuevo rango envuelve completamente al rango existente:** Proprobado por `hora_inicio <= $inicio` AND `hora_fin >= $fin`.

---

### 6.2 `BloqueosRepository`

**Archivo:** `app/Http/Repository/BloqueosRepository.php`

```php
<?php

namespace App\Http\Repository;

use App\Models\BloqueoHorario;
use App\Models\Cita;
use Exception;

class BloqueosRepository
{
    public function obtenerBloqueos(int $doctorId)
    {
        try {
            $bloqueos = BloqueoHorario::where('perfil_doctor_id', $doctorId)
                ->orderBy('fecha_bloqueo', 'desc')
                ->get();

            return [
                'mensaje' => 'Bloqueos obtenidos correctamente',
                'data'    => $bloqueos,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function registrarBloqueo(int $doctorId, array $data, int $usuarioId)
    {
        try {
            // Verificar si existen citas agendadas que resulten afectadas
            $citasAfectadas = Cita::where('perfil_doctor_id', $doctorId)
                ->where('fecha_cita', $data['fecha_bloqueo'])
                ->whereIn('estado', ['agendada', 'confirmada'])
                ->when(!empty($data['hora_inicio_bloqueo']) && !empty($data['hora_fin_bloqueo']), function ($q) use ($data) {
                    $q->whereBetween('hora_cita', [$data['hora_inicio_bloqueo'], $data['hora_fin_bloqueo']]);
                })
                ->count();

            $alerta = $citasAfectadas > 0
                ? "ALERTA: Hay $citasAfectadas cita(s) agendada(s) en este horario que serán afectadas."
                : null;

            $bloqueo = BloqueoHorario::create([
                'perfil_doctor_id'    => $doctorId,
                'fecha_bloqueo'       => $data['fecha_bloqueo'],
                'hora_inicio_bloqueo' => $data['hora_inicio_bloqueo'] ?? null,
                'hora_fin_bloqueo'    => $data['hora_fin_bloqueo'] ?? null,
                'motivo'              => $data['motivo'] ?? null,
                'creado_por'          => $usuarioId,
            ]);

            return [
                'mensaje' => 'Bloqueo registrado correctamente' . ($alerta ? '. ' . $alerta : ''),
                'data'    => $bloqueo,
                'alerta'  => $alerta,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function eliminarBloqueo(int $id)
    {
        try {
            $bloqueo = BloqueoHorario::find($id);
            if (!$bloqueo) {
                return ['mensaje' => 'Bloqueo no encontrado'];
            }
            $bloqueo->delete();
            return ['mensaje' => 'Bloqueo eliminado correctamente'];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }
}
```

---

### 6.3 `DisponibilidadRepository` — Motor de Cálculo de Slots

**Archivo:** `app/Http/Repository/DisponibilidadRepository.php`

Contiene el algoritmo central que genera dinámicamente las horas disponibles para agendar citas.

```php
<?php

namespace App\Http\Repository;

use App\Models\BloqueoHorario;
use App\Models\Cita;
use App\Models\HorarioDoctor;
use Carbon\Carbon;
use Exception;

class DisponibilidadRepository
{
    /**
     * Genera los slots de tiempo atendibles para una fecha dada.
     */
    public function obtenerSlotsDisponibles(int $doctorId, string $fecha)
    {
        try {
            $fechaCarbon = Carbon::parse($fecha);
            $diaSemana   = $this->traducirDia($fechaCarbon->dayOfWeek);

            // 1. Obtener horario activo del doctor para ese día
            $horario = HorarioDoctor::where('perfil_doctor_id', $doctorId)
                ->where('dia_semana', $diaSemana)
                ->where('activo', true)
                ->first();

            if (!$horario) {
                return [
                    'mensaje' => 'El doctor no tiene horario configurado para ese día.',
                    'data'    => [],
                ];
            }

            // 2. Obtener bloqueos aplicados en la fecha
            $bloqueos = BloqueoHorario::where('perfil_doctor_id', $doctorId)
                ->where('fecha_bloqueo', $fecha)
                ->get();

            // 3. Obtener citas ya ocupadas en la fecha
            $citasOcupadas = Cita::where('perfil_doctor_id', $doctorId)
                ->whereDate('fecha_cita', $fecha)
                ->whereIn('estado', ['agendada', 'confirmada', 'en_consulta'])
                ->pluck('hora_cita')
                ->map(fn($h) => substr($h, 0, 8)) // Normalizar formato H:i:s
                ->toArray();

            // 4. Algoritmo iterativo de generación de turnos (slots)
            $slots    = [];
            $duracion = $horario->duracion_consulta_minutos;
            $inicio   = Carbon::createFromTimeString($horario->hora_inicio);
            $fin      = Carbon::createFromTimeString($horario->hora_fin);

            while ($inicio->copy()->addMinutes($duracion) <= $fin) {
                $horaSlot   = $inicio->format('H:i:s');
                $disponible = !in_array($horaSlot, $citasOcupadas) && !$this->estaBloqueado($horaSlot, $bloqueos);

                $slots[] = [
                    'hora'       => $horaSlot,
                    'disponible' => $disponible,
                ];

                $inicio->addMinutes($duracion);
            }

            return [
                'mensaje'      => 'Disponibilidad obtenida correctamente',
                'fecha'        => $fecha,
                'doctor_id'    => $doctorId,
                'duracion_min' => $duracion,
                'data'         => $slots,
            ];
        } catch (Exception $e) {
            return ['mensaje' => $e->getMessage()];
        }
    }

    public function verificarDisponibilidad(int $doctorId, string $fecha, string $hora): bool
    {
        $resultado = $this->obtenerSlotsDisponibles($doctorId, $fecha);
        if (!isset($resultado['data'])) {
            return false;
        }

        foreach ($resultado['data'] as $slot) {
            if ($slot['hora'] === $hora && $slot['disponible']) {
                return true;
            }
        }

        return false;
    }

    private function estaBloqueado(string $hora, $bloqueos): bool
    {
        foreach ($bloqueos as $bloqueo) {
            if ($bloqueo->hora_inicio_bloqueo && $bloqueo->hora_fin_bloqueo) {
                if ($hora >= $bloqueo->hora_inicio_bloqueo && $hora < $bloqueo->hora_fin_bloqueo) {
                    return true;
                }
            } else {
                // Bloqueo de todo el día
                return true;
            }
        }
        return false;
    }

    private function traducirDia(int $dayOfWeek): string
    {
        $dias = [
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
        ];
        return $dias[$dayOfWeek] ?? 'lunes';
    }
}
```

---

## 7. Capa de Validaciones (Form Requests)

### 7.1 `StoreHorarioRequest`

**Archivo:** `app/Http/Requests/StoreHorarioRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreHorarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'dia_semana'                => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora_inicio'               => 'required|date_format:H:i:s',
            'hora_fin'                  => 'required|date_format:H:i:s|after:hora_inicio',
            'duracion_consulta_minutos' => 'nullable|integer|min:10|max:120',
        ];
    }

    public function messages(): array
    {
        return [
            'dia_semana.required' => 'El día de la semana es requerido.',
            'dia_semana.in'       => 'El día de la semana no es válido.',
            'hora_inicio.required' => 'La hora de inicio es requerida.',
            'hora_fin.required'   => 'La hora de fin es requerida.',
            'hora_fin.after'      => 'La hora de fin debe ser posterior a la hora de inicio.',
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

### 7.2 `StoreBloqueoRequest`

**Archivo:** `app/Http/Requests/StoreBloqueoRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBloqueoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'fecha_bloqueo'       => 'required|date',
            'hora_inicio_bloqueo' => 'nullable|date_format:H:i:s',
            'hora_fin_bloqueo'    => 'nullable|date_format:H:i:s|after:hora_inicio_bloqueo',
            'motivo'              => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_bloqueo.required' => 'La fecha del bloqueo es requerida.',
            'fecha_bloqueo.date'     => 'El formato de la fecha no es válido.',
            'hora_fin_bloqueo.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
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

### 8.1 Controladores API REST

#### `HorariosController`
- `obtenerHorarios(int $doctorId)`: Llama a `HorariosRepository::obtenerHorarios`.
- `registrarHorario(StoreHorarioRequest $request, int $doctorId)`: Llama a `HorariosRepository::registrarHorario`.
- `actualizarHorario(Request $request, int $id)`: Llama a `HorariosRepository::actualizarHorario`.
- `eliminarHorario(int $id)`: Llama a `HorariosRepository::eliminarHorario`.

#### `BloqueosController`
- `obtenerBloqueos(int $doctorId)`: Llama a `BloqueosRepository::obtenerBloqueos`.
- `registrarBloqueo(StoreBloqueoRequest $request, int $doctorId)`: Extrae `$request->user()->id` y llama a `BloqueosRepository::registrarBloqueo`.
- `eliminarBloqueo(int $id)`: Llama a `BloqueosRepository::eliminarBloqueo`.

#### `DisponibilidadController`
- `obtenerDisponibilidad(Request $request, int $doctorId)`: Recibe query param `fecha` y llama a `DisponibilidadRepository::obtenerSlotsDisponibles`.

---

### 8.2 Métodos Web SSR en `DoctoresWebController`

**Archivo:** `app/Http/Controllers/Web/DoctoresWebController.php`

```php
// Carga vista con matriz semanal y side panel de bloqueos
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

public function storeHorario(StoreHorarioRequest $request, int $doctorId) { ... }
public function updateHorario(Request $request, int $id) { ... }
public function deleteHorario(int $id) { ... }
public function storeBloqueo(StoreBloqueoRequest $request, int $doctorId) { ... }
public function deleteBloqueo(int $id) { ... }
```

---

## 9. Capa de Vistas (Blade SSR UI y Componentes)

**Archivo:** `resources/views/doctores/horarios.blade.php`

Renderiza la matriz semanal de turnos y el panel lateral de bloqueos de agenda.

```html
@extends('layouts.app')
@section('titulo', 'Horarios de Atención')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4 pb-2 border-bottom">
    <a href="{{ route('doctores.index') }}" class="btn btn-outline-secondary btn-sm"><i data-lucide="arrow-left"></i></a>
    <h1 class="h3 fw-bold mb-0">Horarios de Atención</h1>
</div>

<!-- Mini Card Doctor -->
<div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 18px;">
                {{ strtoupper(substr($doctor['nombre'] ?? 'D', 0, 2)) }}
            </div>
            <div>
                <h4 class="fw-bold mb-1">Dr. {{ $doctor['nombre'] ?? 'Médico' }}</h4>
                <span class="badge bg-success">{{ $doctor['especialidad'] ?? 'General' }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_horario">+ Agregar Horario</button>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal_bloqueo">+ Bloquear Horario</button>
        </div>
    </div>
</div>

<!-- Main Layout Grid + Side Panel -->
<div class="row g-4">
    <!-- Weekly Schedule Grid -->
    <div class="col-lg-8 col-xl-9">
        <h5 class="fw-bold mb-3">Disponibilidad Semanal</h5>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-xl-7 g-2">
            @php
                $diasMap = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
            @endphp
            @foreach($diasMap as $numDia => $nombreDia)
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-3 h-100 p-2 bg-light">
                        <div class="fw-bold text-center small pb-2 border-bottom mb-2 text-dark">{{ $nombreDia }}</div>
                        <div class="d-flex flex-column gap-2">
                            @php
                                $horariosDia = array_filter($horarios, fn($h) => ($h['dia_semana'] ?? 0) == $numDia);
                            @endphp
                            @forelse($horariosDia as $h)
                                <div class="p-2 bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-2 position-relative small">
                                    <form method="POST" action="{{ route('horarios.destroy', $h['id']) }}" onsubmit="return confirm('¿Eliminar horario?');" class="position-absolute top-0 end-0 me-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0 text-decoration-none" title="Eliminar">&times;</button>
                                    </form>
                                    <div class="fw-bold" style="font-size: 11px;">{{ \Carbon\Carbon::parse($h['hora_inicio'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($h['hora_fin'])->format('h:i A') }}</div>
                                    <div class="text-secondary" style="font-size: 10px;">{{ $h['duracion_cita_minutos'] ?? 30 }} min</div>
                                </div>
                            @empty
                                <span class="text-muted text-center extra-small py-2" style="font-size: 11px;">Sin horario</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Bloqueos Registrados -->
    <div class="col-lg-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <h5 class="fw-bold mb-3">Bloqueos de Agenda</h5>
            <div class="d-flex flex-column gap-2">
                @forelse($bloqueos as $bloqueo)
                    <div class="p-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-3 position-relative small">
                        <form method="POST" action="{{ route('bloqueos.destroy', $bloqueo['id']) }}" onsubmit="return confirm('¿Eliminar bloqueo?');" class="position-absolute top-0 end-0 me-2 mt-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0 text-decoration-none" title="Eliminar Bloqueo">&times;</button>
                        </form>
                        <div class="fw-bold mb-1">{{ \Carbon\Carbon::parse($bloqueo['fecha_inicio'])->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($bloqueo['fecha_fin'])->format('d/m/Y H:i') }}</div>
                        <div class="text-secondary small">Motivo: {{ $bloqueo['motivo'] ?? 'Sin motivo' }}</div>
                    </div>
                @empty
                    <p class="text-muted small mb-0 py-2">No hay bloqueos activos para este médico.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Horario -->
<div class="modal fade" id="modal_horario" tabindex="-1" aria-labelledby="modal_horario_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modal_horario_title">Agregar Horario de Atención</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('horarios.store', $doctorId) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="sel_dia" class="form-label fw-medium">Día de la Semana *</label>
                        <select id="sel_dia" name="dia_semana" class="form-select" required>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="7">Domingo</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="inp_hora_inicio" class="form-label fw-medium">Hora Inicio *</label>
                            <input type="time" id="inp_hora_inicio" name="hora_inicio" value="08:00" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label for="inp_hora_fin" class="form-label fw-medium">Hora Fin *</label>
                            <input type="time" id="inp_hora_fin" name="hora_fin" value="14:00" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inp_duracion" class="form-label fw-medium">Duración por Cita (Minutos)</label>
                        <input type="number" id="inp_duracion" name="duracion_cita_minutos" value="30" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Horario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Registrar Bloqueo -->
<div class="modal fade" id="modal_bloqueo" tabindex="-1" aria-labelledby="modal_bloqueo_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title text-danger fw-bold" id="modal_bloqueo_title">Registrar Bloqueo de Agenda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('bloqueos.store', $doctorId) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="inp_f_inicio" class="form-label fw-medium">Fecha / Hora Inicio *</label>
                        <input type="datetime-local" id="inp_f_inicio" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="inp_f_fin" class="form-label fw-medium">Fecha / Hora Fin *</label>
                        <input type="datetime-local" id="inp_f_fin" name="fecha_fin" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="txt_motivo_blq" class="form-label fw-medium">Motivo del Bloqueo *</label>
                        <input type="text" id="txt_motivo_blq" name="motivo" placeholder="Vacaciones, congreso, etc." class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Registrar Bloqueo</button>
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
// Consulta pública de slots de disponibilidad para agendar citas
Route::get('/obtenerDisponibilidad/{doctorId}', [DisponibilidadController::class, 'obtenerDisponibilidad']);

// Administrador: Gestión de horarios y bloqueos
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        // Horarios
        Route::get('/obtenerHorarios/{doctorId}', [HorariosController::class, 'obtenerHorarios']);
        Route::post('/registrarHorario/{doctorId}', [HorariosController::class, 'registrarHorario']);
        Route::put('/actualizarHorario/{id}', [HorariosController::class, 'actualizarHorario']);
        Route::delete('/eliminarHorario/{id}', [HorariosController::class, 'eliminarHorario']);

        // Bloqueos
        Route::get('/obtenerBloqueos/{doctorId}', [BloqueosController::class, 'obtenerBloqueos']);
        Route::post('/registrarBloqueo/{doctorId}', [BloqueosController::class, 'registrarBloqueo']);
        Route::delete('/eliminarBloqueo/{id}', [BloqueosController::class, 'eliminarBloqueo']);
    });
});
```

### 10.2 Rutas Web (`routes/web.php`)

```php
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/doctores/{id}/horarios', [DoctoresWebController::class, 'horarios'])->name('doctores.horarios');
        Route::post('/doctores/{id}/horarios', [DoctoresWebController::class, 'storeHorario'])->name('horarios.store');
        Route::put('/horarios/{id}', [DoctoresWebController::class, 'updateHorario'])->name('horarios.update');
        Route::delete('/horarios/{id}', [DoctoresWebController::class, 'deleteHorario'])->name('horarios.destroy');
        Route::post('/doctores/{id}/bloqueos', [DoctoresWebController::class, 'storeBloqueo'])->name('bloqueos.store');
        Route::delete('/bloqueos/{id}', [DoctoresWebController::class, 'deleteBloqueo'])->name('bloqueos.destroy');
    });
});
```

---

## 11. Flujos Completos de Operación

### 11.1 Flujo del Algoritmo de Cálculo de Disponibilidad Dinámica

```
    PACIENTE (App Móvil/Web)                  DisponibilidadController            DisponibilidadRepository                  Base de Datos
               │                                         │                                    │                                  │
               │ GET /api/obtenerDisponibilidad/5        │                                    │                                  │
               │     ?fecha=2026-08-10                   │                                    │                                  │
               ├────────────────────────────────────────►│                                    │                                  │
               │                                         │ obtenerSlotsDisponibles(5, fecha)  │                                  │
               │                                         ├───────────────────────────────────►│                                  │
               │                                         │                                    │ 1. HorarioDoctor::where(...)     │
               │                                         │                                    ├─────────────────────────────────►│
               │                                         │                                    │◄─────────────────────────────────┤
               │                                         │                                    │ 2. BloqueoHorario::where(...)    │
               │                                         │                                    ├─────────────────────────────────►│
               │                                         │                                    │◄─────────────────────────────────┤
               │                                         │                                    │ 3. Cita::whereDate(...)          │
               │                                         │                                    ├─────────────────────────────────►│
               │                                         │                                    │◄─────────────────────────────────┤
               │                                         │                                    │                                  │
               │                                         │                                    │ 4. Ciclo Iterativo Slots:        │
               │                                         │                                    │    while(inicio + 30m <= fin)    │
               │                                         │                                    │    check (!ocupado && !bloqueado)│
               │                                         │                                    │                                  │
               │ JSON 200 OK (Slots con booleano)        │◄───────────────────────────────────┤                                  │
               │◄────────────────────────────────────────┤                                    │                                  │
```

---

## 12. Relación con Otros Módulos

```
                               ┌──────────────────────────┐
                               │   Módulo 4: HORARIOS Y   │
                               │       DISPONIBILIDAD     │
                               └────────────┬─────────────┘
                                            │
           ┌────────────────────────────────┼────────────────────────────────┐
           │                                │                                │
           ▼                                ▼                                ▼
┌──────────────────────┐        ┌──────────────────────┐        ┌──────────────────────┐
│ Mod 2: Doctores      │        │ Mod 5: Gestión de    │        │ Mod 6: Pacientes     │
│                      │        │        Citas         │        │                      │
│ Pertenece a          │        │ Validación previa al │        │ Visualización de     │
│ PerfilDoctor         │        │ insert de agendamiento│        │ turnos para agendar  │
└──────────────────────┘        └──────────────────────┘        └──────────────────────┘
```

- **Módulo 2 (Gestión de Doctores):** Los horarios y bloqueos dependen jerárquicamente de la existencia de un `perfil_doctor_id`.
- **Módulo 5 (Gestión de Citas):** Antes de crear una cita en la BD, el `CitasRepository` invoca a `DisponibilidadRepository::verificarDisponibilidad()` para validar que el turno no haya sido tomado simultáneamente.
- **Módulo 6 (Gestión de Pacientes):** Sirve como la capa de catálogo de consulta en tiempo real desde la aplicación móvil para pacientes.

---

## 13. Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HorariosController.php                # Controller API REST Horarios
│   │   │   ├── BloqueosController.php                # Controller API REST Bloqueos
│   │   │   ├── DisponibilidadController.php          # Controller API REST Disponibilidad
│   │   │   └── Web/
│   │   │       └── DoctoresWebController.php        # Controller Web SSR (Agenda semanal y bloqueos)
│   │   ├── Repository/
│   │   │   ├── HorariosRepository.php                # Lógica de horarios y solapamiento
│   │   │   ├── BloqueosRepository.php                # Lógica de bloqueos y alertas
│   │   │   └── DisponibilidadRepository.php          # Algoritmo de cálculo de slots libres
│   │   └── Requests/
│   │       ├── StoreHorarioRequest.php               # Form Request validación horario
│   │       └── StoreBloqueoRequest.php               # Form Request validación bloqueo
│   └── Models/
│       ├── HorarioDoctor.php                         # Modelo Eloquent horarios_doctor
│       └── BloqueoHorario.php                        # Modelo Eloquent bloqueos_horario
├── database/
│   └── migrations/
│       ├── 2026_01_01_000007_crear_tabla_horarios_doctor.php # Migración tabla horarios_doctor
│       └── 2026_01_01_000008_crear_tabla_bloqueos_horario.php# Migración tabla bloqueos_horario
├── resources/views/
│   └── doctores/
│       └── horarios.blade.php                        # Vista Blade SSR (Matriz semanal + Modal)
└── routes/
    ├── api.php                                       # Endpoints API REST (/api/obtenerDisponibilidad, etc.)
    └── web.php                                       # Rutas Web SSR (/doctores/{id}/horarios, etc.)
```

---

> **Módulo anterior:** [02 - Gestión de Doctores y Especialidades](./02-Gestion-de-Doctores.md)  
> **Siguiente módulo:** [05 - Gestión de Citas](./05-Gestion-de-Citas.md)
