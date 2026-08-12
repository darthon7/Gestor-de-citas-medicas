# Motivo de la Consulta: Select con Estado Local + Bloqueo de Registro sin Asunto

> Estado: **implementado y verificado** (agosto 2026).
> Alcance: **100% frontend** (Blade SSR + Tailwind CDN + vanilla JS).
> Regla cumplida: **cero cambios en backend, rutas, controladores o base de datos.**

## 1. Objetivo

Dos mejoras sobre el campo **"Motivo de la Consulta"** (el "asunto" de la cita,
`motivo_consulta`) en los formularios de registro de citas:

1. **Corregir la renderización del campo**: reemplazar el `<textarea>` por un
   **select con opciones predefinidas** que se muestren correctamente en la UI,
   con captura del valor seleccionado en el estado local del cliente.
2. **Bloquear el registro sin asunto**: deshabilitar el botón de avanzar/guardar
   mientras el motivo esté vacío o nulo, con una validación visual sutil
   (borde + mensaje) alineada con el sistema de estilos.

> Nota de interpretación (confirmada): no existe un campo "asunto" en el código;
> **"asunto" = "Motivo de la Consulta"** (`motivo_consulta`), el único campo
> descriptivo del formulario de citas.

## 2. Archivos

### Creado
- `resources/views/components/motivo-consulta.blade.php`

### Modificados
- `resources/views/citas/agendar.blade.php` — formulario único de agendar cita
- `resources/views/components/modal-nueva-cita.blade.php` — wizard de 3 pasos
  (incluido en `dashboard/index.blade.php` y `citas/index.blade.php`)

## 3. Nuevo componente `components/motivo-consulta.blade.php`

Partial reutilizable mediante `@include` (tecnología ya usada en el proyecto),
parametrizado con un sufijo de IDs para poder incluirse varias veces en la misma
página sin colisiones:

```blade
@include('components.motivo-consulta', ['suf' => '_cita'])
```

Contiene:

| Elemento | ID | Función |
|---|---|---|
| `<select>` (sin `name`) | `sel_motivo{suf}` | Lista de ~11 motivos médicos legibles + opción `__otro__` ("Otro (especificar)") |
| `<input type="text">` | `inp_motivo_otro{suf}` | Visible solo si se elige "Otro" (toggle `hidden`) |
| `<input type="hidden">` | `inp_motivo_consulta{suf}` | **Lleva `name="motivo_consulta"`** → es el valor que viaja al backend |
| `<p>` de error | `msg_motivo{suf}` | Mensaje sutil `text-[11px] text-danger` (oculto por defecto) |

**Estado local (JS vanilla, en el propio componente):**

- `syncMotivo(suf)` — sincroniza el hidden con el valor efectivo:
  `hidden.value = (select === '__otro__') ? otro.value.trim() : select.value`.
  Hace toggle del input "Otro" y limpia el error. Es la única fuente de verdad
  del estado del campo.
- `motivoTieneValor(suf)` — devuelve `true/false` si el asunto está capturado.
- `marcarErrorMotivo(suf, mostrar)` — aplica/remueve `border-danger` al select y
  muestra/oculta el mensaje.
- `limpiarErrorMotivo(suf)` — quita el estado de error.
- `initMotivo(suf, valorInicial)` — al cargar, preselecciona la opción que
  coincida con el valor inicial (p. ej. `old('motivo_consulta')` tras un error de
  validación) o activa el modo "Otro" con el texto, y sincroniza el estado.

Los estilos del componente usan tokens semánticos del tema por rol
(`bg-white`, `border-border`, `focus:border-primary focus:ring-primary/10`,
`text-danger`, `border-danger`), idénticos a los demás campos del formulario.

## 4. `citas/agendar.blade.php` (formulario único)

1. El bloque del textarea se reemplaza por
   `@include('components.motivo-consulta', ['suf' => '', 'valorInicial' => old('motivo_consulta')])`.
2. El botón **"Confirmar Cita"** (`#btn_confirmar_cita`) ahora nace
   `disabled` y con estilos de estado: `disabled:opacity-40
   disabled:cursor-not-allowed disabled:hover:bg-primary`.
3. Script de wiring (patrón vanilla del proyecto):
   - `sincronizarEstadoBtnCita()` — habilita/deshabilita el botón según
     `motivoTieneValor('')`.
   - Listeners: `change` del select y `input` del campo "Otro" → `syncMotivo('')`
     + `sincronizarEstadoBtnCita()`.
   - `initMotivo('', @js(old('motivo_consulta')))` — restaura el valor si el
     servidor devolvió el formulario (validación fallida).
   - **Guard de submit** en `form_agendar_cita`: si por cualquier vía se
     intentara enviar sin asunto (p. ej. Enter en un input), `preventDefault()`,
     `marcarErrorMotivo('', true)` y foco en el select.

## 5. `components/modal-nueva-cita.blade.php` (wizard 3 pasos)

1. El textarea del **paso 2** se reemplaza por
   `@include('components.motivo-consulta', ['suf' => '_cita'])`.
2. El botón **"Siguiente"** (`#btn_siguiente`) nace `disabled` con los mismos
   estilos de estado.
3. `validarPasoCita(2)` ahora valida también el motivo: si está vacío →
   `marcarErrorMotivo('_cita', true)` + foco en el select + `return false`
   (bloqueo real del avance, aunque el botón estuviera habilitado).
4. `sincronizarBtnSiguiente()` — deshabilita "Siguiente" solo en el paso 2
   cuando no hay asunto; se llama al cambiar de paso y en cada cambio del campo.
5. `llenarResumenCita()` — el resumen del paso 3 ahora lee el valor del hidden
   (`inp_motivo_consulta_cita`) en lugar del textarea eliminado.

## 6. Flujo resultante

1. El usuario abre el formulario (página única o wizard) → el botón
   Confirmar/Siguiente está **deshabilitado** y atenuado (`opacity-40`).
2. Elige un motivo del select (o "Otro" + escribe texto) → el estado local
   sincroniza el hidden y el botón **se habilita** al instante.
3. Si vuelve a dejar el asunto vacío → el botón se deshabilita de nuevo.
4. Si intenta avanzar sin asunto por otra vía → bloqueo con borde
   `border-danger`, mensaje "Por favor selecciona el motivo de la consulta." en
   `text-danger` y foco en el select; el error se limpia solo al corregir.
5. El valor efectivo (motivo predefinido o texto de "Otro") viaja en
   `motivo_consulta` al backend existente, sin ningún cambio en validación
   servidor.

## 7. Decisiones de diseño

- **Select legible**: los `value` de las opciones son cadenas en español
  (p. ej. "Fiebre"), de modo que lo que se guarda en BD es texto directamente
  legible en listados y reportes (`detalle.blade.php`, `doctor/agenda.blade.php`).
- **`__otro__` + campo de texto**: preserva la flexibilidad del textarea
  original sin romper la UI del select.
- **Sin atributo `required`**: el bloqueo se maneja 100% en el estado local
  (botón deshabilitado + guard), evitando tooltips nativos del navegador que
  chocarían con la validación visual propia.
- **Un solo `name`**: el select no tiene `name`; solo el hidden lo tiene, por lo
  que el backend recibe exactamente un `motivo_consulta`.
- **Sufijo de IDs**: el mismo componente sirve a ambas vistas sin colisiones y
  sin duplicar markup.

## 8. Verificación realizada

- `php artisan view:cache` — todas las plantillas Blade compilan sin errores
  (incluidos el componente nuevo y las dos vistas modificadas).
- Búsqueda de referencias residuales al textarea eliminado (`txt_motivo`,
  `txt_motivo_cita`): ninguna en `agendar` ni en el modal (solo permanecen los
  "motivo del bloqueo" de la página de horarios, ajenos a este cambio).
- No se requirió `npm run build` (cambios en Blade + Tailwind CDN).

## 9. Posibles extensiones (fuera de alcance)

- Mover la lista de motivos a una constante compartida (PHP o JS) si se quiere
  centralizar fuera del componente.
- Permitir configuración de los motivos desde administración (requeriría backend).
- Aplicar el mismo patrón al campo "motivo_cancelacion" del detalle de cita si se
  desea consistencia.
