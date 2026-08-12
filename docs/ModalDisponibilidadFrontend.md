# Modal de Disponibilidad de Doctores (Frontend)

> Estado: **implementado y verificado** (agosto 2026).
> Alcance: **solo frontend** — `resources/views/doctores/index.blade.php`.
> Regla cumplida: **cero cambios en backend, rutas, controladores, repositories o base de datos.**

## 1. Objetivo

Agregar un botón **"Disponibilidad"** en cada tarjeta de doctor de la vista de
Gestión de Doctores que, al hacer clic, abra un modal limpio para **registrar un
horario de atención** (día de la semana + rango horario + duración por cita),
reutilizando la infraestructura de horarios que el sistema ya tenía para la página
`doctores.horarios`.

## 2. Tecnología y convenciones utilizadas

- **Blade SSR + Tailwind CSS (CDN)** — sin React/Vue/Alpine. El "estado local" se
  implementa con el mismo patrón vanilla JS que ya usa la vista: funciones
  `onclick` + atributos `data-*` + manipulación de DOM (`classList`).
- **Tokens semánticos del tema por rol** (`bg-primary`, `text-primary-dark`,
  `border-border`, `bg-surface`, `bg-background`, `bg-secondary-light`,
  `text-on-secondary-container`, etc.) — misma estética que el resto de la página.
- **Material Symbols** para iconografía (`calendar_month`, `close`, `add`).

## 3. Archivo modificado

`resources/views/doctores/index.blade.php` — única modificación del proyecto.
Se tocaron 3 zonas:

### 3.1. Botón en la tarjeta del doctor (reemplaza el enlace)

El enlace de texto que apuntaba a `route('doctores.horarios', $doc['id'])` ahora es
un `<button>` que abre el modal:

```blade
<button type="button" onclick="abrirModalDisponibilidad(this)"
        data-id="{{ $doc['id'] }}"
        data-nombre="{{ $nombreCompleto }}"
        data-iniciales="{{ $iniciales }}"
        data-especialidad="{{ $primeraEsp }}"
        class="flex items-center gap-1 text-primary font-semibold text-caption hover:underline cursor-pointer"
        title="Registrar horarios de atención">
    <span class="material-symbols-outlined text-lg">calendar_month</span>
    Disponibilidad
</button>
```

- Los `data-*` transportan los datos del doctor al modal (mismo patrón que
  `abrirEditarDoctor` / `abrirValidarDoctor`).
- Se conserva el estado **deshabilitado** para médicos no validados/inactivos
  (la condición `@if($valEstado === 'validado' && !$inactivo)` y el `<span>` con
  `opacity-40 cursor-not-allowed` no cambian).

### 3.2. Modal "Disponibilidad del Doctor"

Nuevo bloque insertado al final de `@section('content')`, siguiendo el molde
exacto de los modales existentes (`modal_editar_doctor`, `modal_validar_doctor`):

- **Contenedor**: `fixed inset-0 z-50 flex items-center justify-center bg-black/50
  backdrop-blur-sm hidden p-4` — idéntico al resto.
- **Header**: `bg-background border-b border-border` con icono `calendar_month`,
  título "Disponibilidad del Doctor" y botón de cierre.
- **Mini-ficha del doctor**: avatar circular con iniciales + nombre + badge de
  especialidad (mismo estilo de `modal_validar_doctor`).
- **Formulario** `#form_disponibilidad` con los mismos `name` que valida el
  `StoreHorarioRequest` existente:
  - `dia_semana` (select: lunes–domingo)
  - `hora_inicio` (`type="time"`, default 08:00)
  - `hora_fin` (`type="time"`, default 14:00)
  - `duracion_consulta_minutos` (`type="number"`, default 30, min 10, max 120)
  - `@csrf` incluido.
- **Footer**: botones "Cancelar" (outline) y "Guardar Horario"
  (`bg-primary hover:bg-primary-dark`).
- **Enlace de apoyo**: "Ver horarios existentes" → página `doctores.horarios`,
  para administrar/eliminar los horarios ya registrados (su `href` se rellena
  dinámicamente con el id del doctor).

### 3.3. JavaScript de estado local (`@section('scripts')`)

```js
const estadoDisponibilidad = { doctorId: null, nombre: '' };

function abrirModalDisponibilidad(btn) {
    estadoDisponibilidad.doctorId = btn.getAttribute('data-id');
    estadoDisponibilidad.nombre = btn.getAttribute('data-nombre');

    const urlHorarios = '/doctores/' + estadoDisponibilidad.doctorId + '/horarios';
    document.getElementById('form_disponibilidad').action = urlHorarios;
    document.getElementById('lnk_horarios_completos').href = urlHorarios;
    document.getElementById('d_doc_iniciales').innerText = btn.getAttribute('data-iniciales') || 'D';
    document.getElementById('d_doc_nombre').innerText = estadoDisponibilidad.nombre || 'Dr. Médico';
    document.getElementById('d_doc_especialidad').innerText = btn.getAttribute('data-especialidad') || 'General';

    document.getElementById('modal_disponibilidad').classList.remove('hidden');
}

function cerrarModalDisponibilidad() {
    document.getElementById('modal_disponibilidad').classList.add('hidden');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarModalDisponibilidad();
});

const modalDisponibilidad = document.getElementById('modal_disponibilidad');
if (modalDisponibilidad) {
    modalDisponibilidad.addEventListener('click', function (e) {
        if (e.target === this) cerrarModalDisponibilidad();
    });
}
```

Comportamiento:
- Al abrir, se actualiza la `action` del formulario con el id del doctor y se
  rellena la mini-ficha desde los `data-*`.
- Se cierra con el botón ✕, con **Escape** o haciendo clic en el **backdrop**
  (patrón ya usado en `components/modal-nueva-cita.blade.php`).

## 4. Flujo de la funcionalidad (sin backend nuevo)

1. El admin/recepcionista ve las tarjetas de doctores y pulsa **"Disponibilidad"**
   en un doctor validado y activo.
2. Se abre el modal con la mini-ficha del doctor.
3. Se llena día, horas y duración y se pulsa **"Guardar Horario"**.
4. El formulario hace `POST` clásico (con CSRF) a la ruta **ya existente**
   `horarios.store` (`POST /doctores/{id}/horarios`, middleware
   `role:admin,recepcionista`).
5. `DoctoresWebController::storeHorario` valida con `StoreHorarioRequest` y
   persiste vía `HorariosRepository`; redirige `back()` con flash
   "Horario registrado correctamente." que muestra `components/flash-message`.
6. Para ver/eliminar horarios registrados, el enlace del modal lleva a la página
   completa `doctores.horarios` (grid semanal + bloqueos), que ya existía.

## 5. Decisiones y limitaciones

- **Sin listado de horarios dentro del modal**: la vista `doctores.index` no
  recibe los horarios del doctor del backend (requeriría tocar el controlador),
  y el endpoint JSON `obtenerHorarios` exige token Sanctum. Por eso el modal es
  de **registro rápido** y enlaza a la página de administración completa.
- **Sin React/hooks**: el proyecto es Blade SSR; se usó el patrón vanilla JS ya
  establecido para no introducir dependencias ni romper convenciones.
- **El botón respeta los permisos visuales existentes**: solo doctores con
  `estado_validacion === 'validado'` y cuenta activa.
- **No se duplicó lógica de validación**: se reutiliza `StoreHorarioRequest`
  (reglas de día/horas/duración y cruce de solapamientos en el repository).

## 6. Verificación realizada

- `php artisan view:cache` — **todas** las plantillas Blade compilan sin errores
  (incluida la vista modificada).
- Revisión visual/estructural del HTML generado contra los modales existentes de
  la misma vista (mismas clases, mismos tokens, misma estructura de capas).
- No se requirió `npm run build` (los cambios son Blade + Tailwind CDN).

## 7. Posibles extensiones futuras (fuera de alcance)

- Mostrar los horarios existentes del doctor dentro del modal (requiere pasar los
  horarios desde el controlador o un endpoint web autenticado).
- Añadir el registro de "bloqueos de agenda" al mismo modal (segundo formulario
  apuntando a la ruta ya existente `bloqueos.store`).
- Extraer el modal a un componente `components/modal-disponibilidad.blade.php`
  si se quiere reutilizar desde otras vistas.
