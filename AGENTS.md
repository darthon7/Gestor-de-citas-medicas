# AGENTS.md

Monorepo para el Sistema de Gestión de Citas Médicas (UI en español). Consta de:
- `sistema-de-gestion-de-citas-medicas/` — **Laravel 13 + Blade SSR** (el trabajo principal)
- `Movil-citasmedicas/citasmedicas/` — App Android (Kotlin + XML + Volley). **La raíz Gradle está anidada un nivel abajo**, no en `Movil-citasmedicas/`.
- `docs/`, `Documentacion-backend/` (docs de API por módulo), `Example screens/` (mockups)

## Stack truth (importante)
El frontend web es **Blade SSR + Tailwind 4**, NO Inertia/React.
`implementation_plan.md` describe un stack React/Inertia obsoleto — ignorar sus afirmaciones de frontend.
El plan vigente y confiable es `docs/PlanImplementacionVistas.md`.

## Comandos (en `sistema-de-gestion-de-citas-medicas/`)
- `composer run dev` — concurrently: `artisan serve` + `queue:listen` + `pail` (logs) + `vite`
- `composer run setup` — install + .env + key + migrate + npm install/build
- `composer run test` / `php artisan test` — suite PHPUnit
- `npm run test:e2e` — Playwright (ver sección Tests)

## Base de datos y Tests
- **MySQL obligatorio** (no sqlite): dev usa `DB_DATABASE=citas_medicas`, user `root`, pass `1111`.
- **PHPUnit usa otra BD: `citas_medicas_test`** (ver `phpunit.xml`). Debe existir/ser creable en MySQL o los tests fallan. Los Feature tests usan `RefreshDatabase` + `$this->seed()`.
- **Playwright e2e** (`tests/e2e/`, `npm run test:e2e`): requiere **Brave Browser instalado** en Windows; corre **headed** (ventana visible) en incógnito con `slowMo: 2000` — es una suite de demostración visual, no headless CI. Auto-arranca `php artisan serve` en :8000 (reusa si ya corre) contra la **BD dev seedeada**.
- Credenciales seed (usadas por `tests/e2e/helpers/auth.js`): admin `admin@citasmedicas.com` / `Admin1234!`, doctor `gogo@doctor.com`, paciente `carlos.ramirez@paciente.com`.

## Frontend
- `resources/views/layouts/app.blade.php` y `auth.blade.php` cargan Tailwind por CDN (`cdn.tailwindcss.com`), Inter y Material Symbols.
- Paleta "Clinical Clarity" se define inline vía `tailwind.config` dentro de cada layout (no hay `tailwind.config.js`).
- **Los cambios en Blade NO requieren `npm run build`/Vite** — `@vite` solo aparece en la página `welcome` por defecto.
- Menús por rol con `@if(Auth::user()->rol === '...')` (string), sin spatie/permission.

## Backend
- Controladores web: `app/Http/Controllers/Web/*WebController.php` (devuelven vistas Blade).
- Controladores API: `app/Http/Controllers/*Controller.php` (raíz, sin `Web\`).
- Middleware en `bootstrap/app.php`: `role` y `check.status` (ej. `['role:admin,recepcionista']`); API con Sanctum `statefulApi()` y errores JSON forzados para `api/*`.
- Enums como strings sueltos, NO PHP enums: rol `admin|doctor|recepcionista|paciente`; estado cita `agendada|confirmada|en_consulta|completada|cancelada`.
- Columnas en español snake_case (`fecha_cita`, `perfil_paciente_id`); usar nomenclatura en español en código nuevo.

## Android
- Base URL del API **hardcodeada a producción** en `app/src/main/java/com/example/citasmedicas/model/Singleton.kt` (`BASE_URL` → Render). Cambiarla ahí para apuntar a un backend local.
- Convenciones de estilo del proyecto Android: skill repo-local `android-kotlin-conventions`.

## CI / Deploy
- `.github/workflows/build-apk.yml`: push a `master` que toque `Movil-citasmedicas/**` → compila APK debug, crea GitHub Release `v1.0.<run>` y hace POST a `<BACKEND_URL>/api/app-version` con header `X-Deploy-Token`.
- Backend desplegado en Render (`gestor-de-citas-medicas.onrender.com`) vía `Dockerfile` del proyecto Laravel.
