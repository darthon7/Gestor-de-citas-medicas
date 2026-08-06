# 🏥 Landing Page — Agenda Médica

> Documento técnico explicativo del módulo recién agregado: la página de inicio pública del **Sistema de Gestión de Citas Médicas**.

---

## 1. ¿Qué es y para qué sirve?

La **Landing Page** es la "puerta de entrada" pública de la plataforma. Es la primera página que ve una persona cuando visita el sistema **sin haber iniciado sesión**.

Su objetivo es **convertir visitantes en usuarios registrados**: por eso toda su estructura gira alrededor de dos botones principales (llamados **CTA**, del inglés *Call To Action*):

| Botón CTA | A dónde lleva | Para quién |
|---|---|---|
| **Acceso a Personal Médico** | `/login` | Médicos, recepcionistas, administradores |
| **Portal del Paciente / Regístrate** | `/registro` | Pacientes que crean su cuenta |

**Comportamiento de la URL raíz (`/`):**
- **Visitante sin sesión** → ve la landing page.
- **Usuario autenticado** → ve su dashboard (panel correspondiente a su rol), sin cambios respecto al funcionamiento anterior.

**URLs de la landing:** `http://localhost:8000/` (raíz) y `http://localhost:8000/inicio` (ambas muestran la misma página).

---

## 2. Arquitectura: cómo fluye una petición (patrón MVC)

El sistema usa el framework **Laravel**, que implementa el patrón **MVC** (*Model-View-Controller*). El flujo al abrir `/inicio` es:

```
  Navegador
     │  1. GET /  (sin sesión iniciada)
     ▼
┌─────────────────┐    2. Busca la ruta    ┌────────────────────────┐
│  routes/web.php │ ─────────────────────► │ LandingWebController  │
└─────────────────┘                        │  (Controlador)        │
                                           │  home()               │
                                           └──────────┬─────────────┘
                                                      │ 3. ¿Hay sesión?
                                          ┌───────────┴───────────┐
                                          ▼                       ▼
                               Sí (autenticado)        No (visitante)
                                          │                       │
                                          ▼                       ▼
                              DashboardWebController     return view('landing')
                              (dashboard de su rol)      (HTML de la landing)
```

**Nota técnica:** este módulo **no usa Modelos** (no consulta la base de datos) cuando se muestra la landing: es una vista 100% estática renderizada por el servidor. Solo al delegar al dashboard se ejecuta la lógica existente de consultas.

---

## 3. Los archivos nuevos (todo lo que se agregó)

La implementación se hizo con el principio **aditivo**: se crearon archivos nuevos **sin modificar el comportamiento existente**.

| # | Archivo | Ruta completa | Rol |
|---|---|---|---|
| 1 | `LandingWebController.php` | `app/Http/Controllers/Web/` | Controlador: renderiza la landing y enruta la raíz (landing/dashboard) |
| 2 | `landing.blade.php` | `resources/views/` | Vista principal (HTML + Blade) |
| 3 | `landing.css` | `public/css/pages/` | Estilos que carga la página en producción |
| 4 | `landing.css` | `resources/css/pages/` | Copia de estilos para el compilador **Vite** |
| 5 | (2 líneas) | `routes/web.php` | Registro de las rutas públicas de la landing |

---

## 4. Explicación técnica por archivo

### 4.1 `routes/web.php` — Registro de la ruta

Las **rutas** son el "mapa de direcciones" del sistema: le dicen a Laravel qué responder según la URL que el visitante pide.

```php
// Landing Page Pública (no requiere autenticación)
Route::get('/inicio', [LandingWebController::class, 'index'])->name('landing');

// Ruta raíz: landing pública para visitantes / dashboard para usuarios autenticados
Route::get('/', [LandingWebController::class, 'home'])->name('dashboard');
```

Desglose de las líneas:

| Parte | Significado |
|---|---|
| `Route::get('/inicio', ...)` | Responde solo a peticiones **GET** de la URL `/inicio` |
| `[LandingWebController::class, 'index']` | Ejecuta el método `index()` de ese controlador (landing pura) |
| `Route::get('/', ...)` | **Reemplaza** al antiguo dashboard en la raíz, sin cambiar su nombre de ruta |
| `->name('dashboard')` | El alias `dashboard` sigue apuntando a `/`, así todos los `route('dashboard')` existentes (sidebar, redirecciones post-login) funcionan sin tocar nada |
| `->name('landing')` | Alias para referenciar la landing en el código |

**Puntos clave de diseño:**
- Ambas rutas están declaradas **fuera de cualquier grupo de middleware**, así que son de acceso **público** (no exigen sesión iniciada).
- El alias `dashboard` **conserva su nombre**: la ruta `/` simplemente pasó de estar dentro del grupo `['auth', 'check.status']` a ser pública, y la lógica de verificación de cuenta se ejecuta manualmente dentro del controlador (ver 4.2). Resultado: el flujo post-login, la sidebar y todos los redireccionamientos existentes se comportan idéntico.
- `/inicio` es una ruta auxiliar que siempre muestra la landing, útil para compartir el enlace directo.

### 4.2 `LandingWebController.php` — El controlador

Un **controlador** es la clase que orquesta la respuesta. Tiene dos métodos:

```php
public function index()
{
    return view('landing');
}

public function home(Request $request)
{
    if (!Auth::check()) {
        return view('landing');
    }

    return app(CheckAccountStatus::class)->handle($request, function () {
        return app(DashboardWebController::class)->index();
    });
}
```

- `index()`: devuelve la landing pura (usada por `/inicio`).
- `home()`: la lógica del **enrutamiento inteligente de la raíz**:
  1. Si **no hay sesión** (`Auth::check()` es `false`) → renderiza la landing.
  2. Si **hay sesión** → delega en el middleware `CheckAccountStatus` (el mismo que protegía la ruta antes), que valida que la cuenta no esté inactiva ni bloqueada; si todo está bien, ejecuta `DashboardWebController::index()` — **exactamente el mismo controlador y método que usaba la ruta original**, por lo que el dashboard se comporta sin cambios.

`view('landing')` le dice al motor de plantillas **Blade** (el sistema de vistas de Laravel) que busque el archivo `resources/views/landing.blade.php` y lo convierta en HTML.

### 4.3 `landing.blade.php` — La vista

Es un documento HTML estándar con **extensiones de Blade**, el lenguaje de plantillas de Laravel. Lo más importante técnicamente:

**a) Detección de sesión con `@auth` / `@else`**

La navbar cambia según si el visitante ya inició sesión o no:

```blade
@auth
    <a href="{{ route('dashboard') }}">Ir al Dashboard</a>
@else
    <a href="{{ route('login') }}">Iniciar Sesión</a>
    <a href="{{ route('registro') }}">Regístrate</a>
@endauth
```

- `@auth`: solo se renderiza si hay una **sesión activa** (usuario autenticado). Le muestra el botón "Ir al Dashboard" para que no tenga que buscar su panel.
- `@else` / `@endauth`: si es visitante anónimo, muestra los CTAs de conversión.
- `route('login')` y `route('registro')` generan automáticamente las URLs de los módulos de autenticación **ya existentes** — la landing no duplica lógica, solo los enlaza.

**b) Carga de recursos (assets)**

```blade
<link rel="stylesheet" href="{{ asset('css/pages/landing.css') }}">
```

`asset()` genera la URL pública correcta del archivo CSS (incluyendo el puerto del servidor en desarrollo).

**c) Iconos y estilos**

- **Bootstrap 5.3** (CDN): framework CSS que provee el sistema de rejilla (`row`, `col-lg-6`) y la navbar colapsable en móvil.
- **Lucide Icons**: librería de iconos con estilo outline, la misma que usa el resto del sistema (`<i data-lucide="calendar-check"></i>` se transforma en SVG mediante `lucide.createIcons()`).
- **Inter**: tipografía oficial del sistema de diseño (familia cargada desde Google Fonts).

### 4.4 `landing.css` — Los estilos

Sigue el **patrón de estilos por página** que ya usa el proyecto (igual que `citas.css` o `doctores.css`): existe en `public/css/pages/` (se sirve directamente con `asset()`) y se duplica en `resources/css/pages/` para que el compilador de **Vite** lo incluya si en algún momento se compilan los assets.

Los colores usan **exactamente la paleta médica del proyecto** definida en `resources/css/variables.css`:

| Token del sistema | Hex | Uso en la landing |
|---|---|---|
| Primary | `#1B6B93` | Navbar, botón "Acceso a Personal Médico", panel clínico |
| Primary Dark | `#0F4C6B` | Gradientes navbar/footer, hover de botones |
| Secondary | `#2A9D8F` | Tarjeta de Pacientes, iconos de éxito |
| Accent | `#E9A319` | CTA "Portal del Paciente", destacados |
| Danger | `#E76F51` | Iconos de advertencia (cero duplicados) |
| Background / Surface | `#F7F9FC` / `#FFFFFF` | Fondos de secciones y tarjetas |

---

## 5. Estructura de la página (secciones)

| Sección | `id` (ancla) | Contenido |
|---|---|---|
| Navbar fija | — | Logo, menú, botón **Iniciar Sesión** siempre visible |
| Hero | `#inicio` | Título magnético, subtítulo, 2 CTAs principales, tarjeta visual con mini-estadísticas |
| Elige tu Perfil | `#perfiles` | 2 tarjetas: **Pacientes** (app móvil, insignias Google Play/App Store) y **Personal Clínico** (panel web) |
| Beneficios | `#beneficios` | 4 viñetas: adiós a llamadas, cero filas, control en tiempo real, información centralizada |
| CTA Final | `#cta-final` | Cierre con "Crear mi cuenta ahora" + "Entrar al panel web" + descarga de la app |
| Footer | — | © 2026 Agenda Médica, enlaces a login/registro |

La navbar usa la clase `fixed-top` de Bootstrap: **queda pegada arriba y el botón de inicio de sesión acompaña al usuario durante todo el scroll**, tal como pide el objetivo de conversión.

---

## 6. Cómo se verificó que no se afectó lo existente

1. `php artisan route:list` → se confirmó que `/` mantiene el alias `dashboard` (ahora público, manejado por `LandingWebController@home`), `/login` y `/registro` intactos, y `/inicio` como ruta nueva.
2. `php artisan view:cache` → compila todas las vistas Blade sin errores de sintaxis.
3. `php artisan test` → los fallos del suite son **preexistentes y ambientales** (`Access denied for user 'root'@'localhost'` al conectar a la base `citas_medicas_test`); no están relacionados con la landing, que no toca la base de datos. El test de Unit (`ExampleTest`) pasa correctamente.
4. Smoke test HTTP (servidor `php artisan serve`):
   - `GET /` (visitante sin sesión) → **200 OK** con el contenido de la landing
   - `GET /inicio` → **200 OK**
   - `GET /login` → **200 OK**

---

## 7. Guía rápida para modificarla

**Cambiar textos:** editar `resources/views/landing.blade.php` (todo el copy está en español y marcado en secciones con comentarios `<!-- -->`).

**Cambiar colores/botones:** editar `public/css/pages/landing.css` (clases `btn-landing-*`, `.landing-*`).

**Apuntar la descarga de la app a una URL real:** en `landing.blade.php`, reemplazar los `href="#"` de las insignias Google Play / App Store por la URL del APK o de la tienda cuando exista.

**Cambiar la URL de la landing:** editar la línea de la ruta en `routes/web.php` y ajustar el alias `name('landing')` si se usa en otros lugares.

**Cambiar el comportamiento de la raíz (`/`):** editar el método `home()` de `LandingWebController` (decide qué ver según exista sesión).

---

## 8. Comandos útiles para probar en local

```bash
# Levantar el servidor de desarrollo
php artisan serve

# Abrir la landing en el navegador (abre la raíz del sitio)
# http://localhost:8000/

# Ruta auxiliar que siempre muestra la landing
# http://localhost:8000/inicio

# Ver las rutas registradas
php artisan route:list

# Recompilar las vistas Blade (detecta errores de sintaxis)
php artisan view:cache
```

---

## 9. Resumen en una frase

Se agregó una **landing page pública en la raíz (`/`) y en `/inicio`** compuesta por rutas, un controlador, una vista Blade y un CSS, diseñada para llevar al visitante a los módulos de **login y registro ya existentes**: quien no tiene sesión ve la landing, y quien ya está autenticado sigue entrando a su **dashboard sin ningún cambio en su funcionamiento**.
