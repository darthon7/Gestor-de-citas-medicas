# Filtro de Doctores en Ventanas de Citas: Solo Validados + Mapeo Corregido

> Estado: **implementado y verificado** (agosto 2026).
> Alcance: **100% frontend** (Blade SSR + Tailwind CDN + vanilla JS).
> Regla cumplida: **cero cambios en backend, rutas, controladores o base de datos.**

## 1. Objetivo

Dos mejoras sobre la lista de doctores en las ventanas de citas:

1. **Corregir el renderizado del nombre**: los selects mostraban "Dr. " vacío
   porque se leía `$doc['nombre']`, clave que **no existe** en la estructura real
   devuelta por `DoctoresRepository::obtenerDoctores()`. El nombre vive en
   `usuario.nombre` (relación `usuario`).
2. **Filtrar solo doctores validados en el estado local**: ocultar del select a
   los doctores cuyo `estado_validacion` no sea `'validado'`, aplicando el filtro
   **antes de pintar** las opciones, sin consultas ni filtros backend.

## 2. Estructura de datos real (verificada en el repositorio)

`DoctoresRepository::obtenerDoctores()` retorna

```php
PerfilDoctor::with(['usuario', 'especialidades'])->...->get()
```

por lo que cada doctor tiene:

- `id`
- `usuario.nombre` (relación `usuario` — aquí está el nombre real)
- `especialidades` (relación — colección; `.first().nombre` = especialidad principal)
- `estado_validacion` — string: `'validado' | 'pendiente' | 'rechazado'`
- **NO existe** `nombre`, `especialidad`, `especialidad_id` ni booleano `validado`

## 3. Archivos modificados

- `resources/views/citas/index.blade.php` — filtro "Todos los doctores" (línea ~85)
- `resources/views/citas/agendar.blade.php` — select de doctor del formulario
- `resources/views/components/modal-nueva-cita.blade.php` — select de doctor del
  wizard (incluido en `dashboard/index.blade.php` y `citas/index.blade.php`)

## 4. Patrón implementado (3 vistas)

### 4.1 Proyección saneada en Blade

En cada vista se mapea `$doctores` a un arreglo mínimo con las claves que el
cliente necesita (evita exponer email/teléfono/CURP/cédula en el HTML):

```php
$doctoresJson = collect($doctores)->map(fn($d) => [
    'id'                  => $d['id'],
    'nombre'              => $d['usuario']['nombre'] ?? 'Médico',
    'especialidad_nombre' => $d['especialidades']->first()['nombre'] ?? 'General',
    'especialidades'      => $d['especialidades']->pluck('id'),
    'estado_validacion'   => $d['estado_validacion'] ?? 'pendiente',
])->values();
```

- En `citas/index.blade.php` y `citas/agendar.blade.php` se declara dentro de
  `@section('content')`; en el modal se declara al inicio del partial (recibe
  `$doctores` desde la página que lo incluye).
- Se serializa al cliente con `@json($doctoresJson)` (patrón Blade estándar).

### 4.2 Renderizado de opciones por JS vanilla

Los `<select>` quedaron con solo la opción vacía y se pueblan en un IIFE:

```js
const visibles = doctoresAgendar.filter(d => d.estado_validacion === 'validado');
select.insertAdjacentHTML('beforeend', visibles.map(d =>
    '<option value="' + d.id + '" data-especialidad="' + d.especialidades.join(',') + '">Dr. ' +
    d.nombre + ' (' + d.especialidad_nombre + ')</option>'
).join(''));
```

Compatibilidad con funciones existentes:

- `agendar`: opciones con `data-especialidad="<ids CSV>"` → `filtrarDoctores()`
  (antes el atributo quedaba vacío porque leía `especialidad_id`, inexistente).
- `modal-nueva-cita`: opciones con `data-especialidades="<ids CSV>"` →
  `filtrarDoctoresCita()`.
- `citas/index`: el filtro conserva `onchange` que reenvía el formulario con
  `doctor_id` (ruta GET existente, sin cambios).

## 5. Casos borde cubiertos

- **Preselección `$doctorId` en `citas/index`**: si el doctor filtrado por la URL
  ya no está en la lista visible (no validado), se agrega **solo su opción** al
  final del select para que el filtro siga siendo coherente y no desaparezca.
- **Doctores sin nombre**: `usuario.nombre ?? 'Médico'` evita "Dr. " vacío.
- **Sin especialidades**: `'General'` como fallback, igual que antes.

## 6. Verificación

- `php artisan view:cache` → compila sin errores.
- `php artisan test` no ejecutable en este entorno (fallo preexistente de
  conexión MySQL), no relacionado con estos cambios.

## 7. No se tocó

- Backend: controladores, repositorios, rutas, modelos, migraciones, BD.
- El servidor/queue (`composer run dev`) sigue igual; los cambios son solo de
  vista.
