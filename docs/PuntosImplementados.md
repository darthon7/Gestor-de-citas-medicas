# Puntos Implementados — Correcciones y Funcionalidades (Citas y Doctores)

Documento de referencia de los 6 puntos solicitados para el módulo web de
**Gestor-de-citas-medicas**. Estado actualizado a la fecha de este documento.

> **Regla general aplicada:** no se modificó nada de la app móvil
> (`Movil-citasmedicas/`) ni de sus rutas API; donde un Form Request es compartido
> con la API, se usaron reglas condicionales para preservar el comportamiento móvil.

---

## 1. Botón de disponibilidad de doctores — ✅ IMPLEMENTADO (completo)

Permite configurar los horarios de atención y bloqueos de cada doctor desde la
interfaz de gestión de doctores.

| Capa | Archivo | Detalle |
|---|---|---|
| Ruta | `routes/web.php:89-94` | `doctores.horarios` (GET/POST), `horarios.update`, `horarios.destroy`, `bloqueos.store` |
| Controlador | `app/Http/Controllers/Web/DoctoresWebController.php` | `horarios()` (82), `storeHorario` (97), `updateHorario` (107), `deleteHorario` (117), `storeBloqueo` (127) |
| Vista | `resources/views/doctores/index.blade.php` | Botón **"Disponibilidad"** (líneas 122-136) + modal de registro rápido (393+) |
| Vista | `resources/views/doctores/horarios.blade.php` | Disponibilidad semanal + bloqueos de agenda |
| Validación | `StoreHorarioRequest`, `StoreBloqueoRequest` | Reglas de día/hora/bloqueo |

**Comportamiento:** el botón "Disponibilidad" solo se habilita para doctores
**validados y activos**; el resto lo ve deshabilitado con tooltip explicativo.

---

## 2. Filtro de doctores en la ventana de citas — ✅ IMPLEMENTADO (backend + frontend)

**Problema original:** el filtro cargaba todos los doctores (sin importar la
validación) y el mapeo del nombre era incorrecto.

**Solución aplicada:**

- **Backend (nuevo):** `CitasWebController.php:76 y :104` ahora consultan
  `obtenerDoctores(['estado_validacion' => 'validado'])`. El repositorio
  (`DoctoresRepository.php:22-24`) ya soportaba el filtro; la consulta SQL solo
  carga doctores con `estado_validacion = 'validado'`.
- **Frontend (ya existía):** el nombre se mapea desde `usuario.nombre` y los
  selects se llenan con `Dr. <nombre>` en tres puntos:
  - `resources/views/citas/index.blade.php:341-359` (filtro superior)
  - `resources/views/citas/agendar.blade.php:204-216`
  - `resources/views/components/modal-nueva-cita.blade.php:298-310`

**Alcance:** solo el módulo web de citas; dashboard, reportes y la API móvil
conservan su comportamiento original.

---

## 3. Validación obligatoria del "Asunto" (motivo de la consulta) — ✅ IMPLEMENTADO (web, sin romper móvil)

**Frontend (ya existía):** el avance queda bloqueado sin asunto:

- `citas/agendar.blade.php:218-251` — botón "Confirmar Cita" deshabilitado +
  `preventDefault` en el submit.
- `components/motivo-consulta.blade.php` — lógica de selección + "Otro" (input
  oculto `motivo_consulta`).

**Backend (nuevo):** `StoreCitaRequest.php` — regla condicional:

```php
use Illuminate\Validation\Rule;

'motivo_consulta' => [
    'nullable', 'string', 'max:200',
    Rule::requiredIf(!$this->is('api/*')),
    'not_in:__otro__',
],
```

Mensaje: `'motivo_consulta.required' => 'El motivo de la consulta es requerido.'`

**Por qué condicional:** `StoreCitaRequest` es compartido con la API móvil
(`CitasController::registrarCita` y `agendarCita`). Con `Rule::requiredIf(!$this->is('api/*'))`:
- **Web (SSR):** el envío sin motivo falla con 422 (refuerzo de las vistas).
- **App móvil (`/api/*`):** el campo permanece `nullable` — cero impacto.

---

## 4. Corrección en los motivos de la consulta — ✅ IMPLEMENTADO (columna string + lista en config)

**Problema corregido:** el campo `motivo_consulta` (asunto) se capturaba en el
formulario pero **no se persistía**: la tabla `citas` no tenía columna y
`CitasRepository::registrarCita` descartaba el valor. Las vistas del doctor y del
detalle ya lo esperaban (`doctor/agenda.blade.php:110`,
`citas/detalle.blade.php:77`), por lo que siempre se mostraba
"Sin motivo especificado".

**Solución aplicada (por eso se descartó el catálogo en BD: las vistas leen el texto directo):**

1. **Migración** `2026_08_12_000001_agregar_motivo_consulta_a_citas.php` —
   columna `motivo_consulta` (`string(200)`, `nullable`) en `citas`. Nullable para
   no romper registros históricos ni las citas creadas por la API móvil.
2. **`config/citas.php` (nuevo)** — lista central de 11 motivos (fuente única para
   Blade y reglas futuras).
3. **`components/motivo-consulta.blade.php`** — el array hardcodeado se reemplazó
   por `config('citas.motivos_consulta', [...fallback...])`.
4. **`app/Models/Cita.php`** — `motivo_consulta` agregado a `$fillable`.
5. **`CitasRepository::registrarCita`** — persiste
   `'motivo_consulta' => $data['motivo_consulta'] ?? null`. Como este método es
   compartido con la API móvil, `?? null` conserva el comportamiento móvil intacto.

**Sin impacto móvil:** columna nullable + `?? null` + regla web ya condicional.

---

## 5. Eliminar la hora en la ventana de citas (modal "Agendar Nueva Cita") — ✅ IMPLEMENTADO

`resources/views/components/modal-nueva-cita.blade.php` — Paso 2 ("Doctor y Horario"):

- **Blade:** el campo visible "Hora de Consulta *" (`type="time"`) se convirtió en
  un input **oculto** (`type="hidden"`, conserva `id="inp_hora_cita"`,
  `name="hora_cita"`, `required`). Se eliminaron el label y el contenedor visual;
  la Fecha quedó en ancho completo.
- **Interacción:** el usuario elige la hora únicamente con los botones de
  "Horarios Sugeridos Disponibles".
- **JavaScript:**
  - `seleccionarHoraCita(hora)` asigna el valor al input oculto y resalta solo el
    botón pulsado (clase `.active`, fondo oscuro `--primary-container` + texto
    blanco, descartando el resaltado anterior).
  - `validarPasoCita` (caso 2): si no hay hora, marca con borde rojo el contenedor
    de slots (error visible, a diferencia del input oculto) y bloquea "Siguiente".
  - `consultarDisponibilidadCita`: al re-renderizar chips restaura `.active` si la
    hora elegida sigue disponible; limpia el error y el valor obsoleto.

---

## 6. Regex en el registro de doctores — ✅ IMPLEMENTADO

Formatos aplicados: **cédulas 7-8 dígitos**, **teléfono 10 dígitos**, **CURP oficial**.

**Backend:**

| Archivo | Campo | Regla |
|---|---|---|
| `StoreRegistroMedicoRequest.php` | `telefono` | `nullable|string|regex:/^\d{10}$/` |
| `StoreRegistroMedicoRequest.php` | `cedula_profesional` | `required|...|unique|regex:/^\d{7,8}$/` |
| `StoreRegistroMedicoRequest.php` | `cedula_especialidad` | `nullable|string|regex:/^\d{7,8}$/` |
| `StoreDoctorRequest.php` | mismos 3 campos | mismas reglas |
| `StoreDoctorRequest.php` | `curp` | `nullable|size:18|regex:/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/` |

Mensajes nuevos: `cedula_profesional.regex`, `cedula_especialidad.regex`,
`telefono.regex` y `curp.regex`.

**Frontend (atributos `pattern` + `title`):**

- `auth/registro-doctor.blade.php` — CURP (formato oficial), teléfono `[0-9]{10}`,
  cédula profesional y de especialidad `[0-9]{7,8}`.
- `doctores/index.blade.php` — modales **crear** (líneas ~281-289) y **editar**
  (líneas ~220-238): cédula profesional, teléfono y cédula de especialidad.

> **Aviso:** ambos Form Requests son compartidos con la API móvil
> (`AuthController:73`, `DoctoresController:28`). Si la app envía formatos que no
> cumplen estas regex, esas peticiones fallarán con 422.

---

## Archivos modificados (resumen)

| Archivo | Punto |
|---|---|
| `app/Http/Controllers/Web/CitasWebController.php` | 2 |
| `app/Http/Requests/StoreCitaRequest.php` | 3 |
| `resources/views/components/modal-nueva-cita.blade.php` | 5 |
| `app/Http/Requests/StoreRegistroMedicoRequest.php` | 6 |
| `app/Http/Requests/StoreDoctorRequest.php` | 6 |
| `resources/views/auth/registro-doctor.blade.php` | 6 |
| `resources/views/doctores/index.blade.php` | 6 |
| `database/migrations/2026_08_12_000001_agregar_motivo_consulta_a_citas.php` | 4 |
| `config/citas.php` | 4 |
| `resources/views/components/motivo-consulta.blade.php` | 4 |
| `app/Models/Cita.php` | 4 |
| `app/Http/Repository/CitasRepository.php` | 4 |

**Nota:** la migración del punto 4 debe ejecutarse con `php artisan migrate`
(local y en el despliegue).

**No se modificó:** `Movil-citasmedicas/`, rutas API ni seeders. Única migración
nueva: `2026_08_12_000001_agregar_motivo_consulta_a_citas.php` (punto 4).