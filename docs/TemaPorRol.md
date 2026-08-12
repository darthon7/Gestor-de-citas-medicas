# Tema por Rol — Color principal según el usuario autenticado

> Estado: **implementado y verificado** (agosto 2026).
> Alcance: frontend web Blade SSR (`sistema-de-gestion-de-citas-medicas`).

## 1. Objetivo

Cambiar el color principal de la interfaz web según el rol del usuario autenticado,
para diferenciar visualmente cada tipo de usuario:

| Rol            | Color         | Paleta (familia `primary*`) |
|----------------|---------------|-----------------------------|
| **admin**      | Verde oscuro  | `#1B5E20` → `#0D3B12` (sidebar) |
| **recepcionista** | Verde médico | `#059669` → `#065F46` (sidebar) |
| **doctor**     | Azul oscuro   | `#1E40AF` → `#172554` (sidebar) |
| **paciente**   | Azul médico   | `#0284C7` → `#075985` (sidebar) |
| sin sesión / desconocido | Azul institucional (default) | `#005275` → `#0F4C6B` |

## 2. Estrategia arquitectónica

- **Single source of truth**: toda la lógica de color vive en una única clase PHP
  (`App\Support\PaletaRol`). Las vistas **no** contienen `@if` por rol para colores
  ni hex code de marca.
- **Tokens semánticos**: las vistas usan clases como `bg-primary`, `text-primary-dark`,
  `border-primary-container`, etc. Como el `tailwind.config` (CDN) se genera por rol,
  **cambiar 7 hex values re-tematiza toda la app**, incluido el sidebar (`bg-primary-dark`).
- **Resolución server-side**: con Blade SSR el servidor ya conoce el rol, así que la
  respuesta HTML llega con el color correcto. No hay estado global, providers de temas
  ni JS de cliente.
- **CSS variables para lo que Tailwind no cubre**: gradientes inline, sombras,
  `.sidebar-item-active` y estilos propios en `<style>` usan variables CSS
  (`--primary`, `--primary-container-rgb`, …) definidas en `:root` del layout.
- **Sin romper semántica de estados**: la familia `secondary*` (estado de cita
  "Confirmada/Completada"), `danger`, `warning-gold` y las escalas de grises quedan
  **fijas** para todos los roles.

## 3. Archivos creados

### `app/Support/PaletaRol.php` (nuevo)

Clase estática con:

- `BASE` — tokens compartidos por todos los roles (superficies, texto, estados,
  alertas). Incluye tokens que antes faltaban en el config y las vistas ya usaban:
  `secondary-container`, `on-secondary-container`, `surface-dim`, `surface-variant`,
  `surface-container-low`, `surface-container-high` (antes eran clases "muertas" que
  el CDN de Tailwind ignoraba).
- `DEFAULT` — paleta de marca por defecto (la original `#005275`, ahora también con
  `primary-fixed` y `on-primary-fixed-variant`).
- `PALETAS` — sobrescrituras de la familia `primary*` por rol (7 tokens por rol:
  `primary`, `primary-dark`, `primary-light`, `primary-container`, `primary-fixed`,
  `on-primary`, `on-primary-fixed-variant`).
- `para(?string $rol): array` — devuelve `BASE + DEFAULT` fusionado con la paleta del
  rol (o la default si el rol no existe/no hay sesión).
- `rgb(string $hex): string` — convierte hex → `"r, g, b"` para `rgba()`.
- `cssVars(array $paleta): string` — genera el bloque de variables CSS de marca,
  incluyendo `--primary-container-rgb`.

## 4. Archivos modificados

### `app/Providers/AppServiceProvider.php`

Se registró un **View Composer** para la vista `layouts.app`:

```php
View::composer('layouts.app', function (\Illuminate\View\View $view) {
    $paleta = PaletaRol::para(auth()->user()?->rol);

    $view->with([
        'paleta' => $paleta,
        'paletaCssVars' => PaletaRol::cssVars($paleta),
    ]);
});
```

El compositor corre cada vez que se renderiza el layout (la única vista que envuelve
a todo el backend autenticado), así que ninguna vista hija necesita saber nada de temas.

### `resources/views/layouts/app.blade.php`

1. **`tailwind.config` dinámico**: el bloque `colors: {...}` hardcodeado se reemplazó
   por `"colors": @json($paleta)` (JSON generado por Blade con la paleta del rol).
   El resto del config (bordes, spacing, fuentes) se mantiene igual.
2. **Bloque `<style>` con variables CSS**: se agrega `:root { ... }` con las variables
   generadas por `PaletaRol::cssVars()` y se reescribieron las reglas que tenían
   rgba/hex fijos:
   - `.sidebar-item-active` → `rgba(var(--primary-container-rgb), 0.3)` +
     `border-left: var(--primary-light)` + `color: var(--on-primary)`.
   - `.card-shadow` / `.card-shadow-hover` → `rgba(var(--primary-container-rgb), 0.08/0.14)`.

### `resources/views/doctor/agenda.blade.php`

El banner de bienvenida usaba un gradiente inline con hex fijos
(`#0F4C6B → #1B6B93`). Ahora usa las variables de marca:

```
style="background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-container) 100%);"
```

### `resources/views/components/modal-nueva-cita.blade.php`

Los chips de horarios (`.slot-chip-cita`) usaban `#1B6B93` y `#e6f2f8` fijos.
Ahora usan `var(--primary-container)`, `var(--primary-light)` y
`rgba(var(--primary-container-rgb), 0.25)`.

## 5. Decisión importante: por qué `text-primary` NO se toca

En el config original conviven dos tokens casi homónimos:

| Token | Valor | Uso |
|---|---|---|
| `primary` | `#005275` | **Color de marca** (botones, sidebar, acentos) → se retema |
| `text-primary` | `#1A1A2E` | **Gris oscuro de texto de cuerpo** (no es azul) → fijo |

El swap de rol solo sobrescribe la familia `primary*`. Si se tocara `text-primary`,
el texto de las tarjetas se teñiría del color del rol. Por eso `text-primary` vive en
`PaletaRol::BASE` y nunca en `PALETAS`.

## 6. Cómo agregar un rol nuevo (escalabilidad)

1. Agregar una entrada en `PaletaRol::PALETAS` con los 7 tokens de la familia
   `primary*` (y, si aplica, tokens de marca adicionales).
2. **Nada más.** Las vistas y el layout toman la paleta automáticamente.

Si en el futuro se quisieran temas configurables por el admin (sin deploy), el mapa
PHP de `PALETAS` se puede reemplazar por una tabla `temas` sin tocar el resto:
`PaletaRol::para()` sería la única función que cambiaría de fuente.

## 7. Fuera de alcance (intencional)

- **`layouts/auth.blade.php`** (login, registro, recuperación): no hay usuario
  autenticado; conserva su paleta azul institucional propia.
- **`reportes/pdf.blade.php`**: documento descargable, mantiene el color institucional
  `#005275` (identidad corporativa en exportaciones).
- **Gráficas Chart.js** (`reportes/index.blade.php`): colores de datos propios;
  si se desea, pueden derivarse de la paleta del rol en una mejora futura.
- **`welcome.blade.php` / `landing.blade.php`**: páginas públicas sin sesión.

## 8. Verificación realizada

- `php -l` sin errores en los archivos PHP modificados.
- `php artisan view:cache` — todas las plantillas Blade compilan.
- Prueba de render del layout con usuario fake de rol `doctor`:
  - el `tailwind.config` generado contiene `#1E40AF / #172554 / #2563EB / #DBEAFE`;
  - `:root` contiene `--primary: #1E40AF` y `--primary-container-rgb: 37, 99, 235`.
- Nota: `php artisan test` no pudo ejecutarse en este entorno porque MySQL no está
  disponible para la BD de tests (`citas_medicas_test`, acceso denegado para
  `root@localhost`) — fallo de entorno preexistente, no relacionado con este cambio.

## 9. Paletas finales por rol

| Token | Default | admin | recepcionista | doctor | paciente |
|---|---|---|---|---|---|
| `primary` | `#005275` | `#1B5E20` | `#059669` | `#1E40AF` | `#0284C7` |
| `primary-dark` | `#0F4C6B` | `#0D3B12` | `#065F46` | `#172554` | `#075985` |
| `primary-light` | `#A8D5E2` | `#A5D6A7` | `#A7F3D0` | `#BFDBFE` | `#BAE6FD` |
| `primary-container` | `#1b6b93` | `#2E7D32` | `#10B981` | `#2563EB` | `#0EA5E9` |
| `primary-fixed` | `#CBE7F0` | `#C8E6C9` | `#D1FAE5` | `#DBEAFE` | `#E0F2FE` |
| `on-primary` | `#ffffff` | `#ffffff` | `#ffffff` | `#ffffff` | `#ffffff` |
| `on-primary-fixed-variant` | `#0F4C6B` | `#1B5E20` | `#064E3B` | `#172554` | `#0C4A6E` |
