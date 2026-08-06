# AGENTS.md

Monorepo para el Sistema de Gestión de Citas Médicas (Spanish UI). Consta de:
- `sistema-de-gestion-de-citas-medicas/` — **Laravel 13 + Blade SSR** (el trabajo principal)
- `Movil-citasmedicas/` — App Android (Kotlin + XML)
- `docs/` + `Example screens/` — Mockups y planes

## Stack truth (importante)
El frontend web es **Blade SSR + Tailwind 4**, NO Inertia/React.
`implementation_plan.md` describe un stack React/Inertia obsoleto — ignorar sus afirmaciones de frontend.
El plan vigente y confiable es `docs/PlanImplementacionVistas.md`.

## Frontend
- `resources/views/layouts/app.blade.php` carga Tailwind por CDN (`cdn.tailwindcss.com`), Inter y Material Symbols.
- Paleta "Clinical Clarity" se define inline vía `tailwind.config` dentro del layout (no hay `tailwind.config.js`).
- **Los cambios en Blade NO requieren `npm run build`/Vite** (Vite solo sirve la página `welcome` por defecto).
- Menús por rol con `@if(Auth::user()->rol === '...')` (string), sin spatie/permission.

## Backend
- Controladores web: `app/Http/Controllers/Web/*WebController.php` (devuelven vistas Blade).
- Controladores API: `app/Http/Controllers/*Controller.php` (raíz, sin `Web\`).
- Middleware registrados en `bootstrap/app.php`: `role` y `check.status` (ej. `['role:admin,recepcionista']`).
- Enums como strings sueltos, NO PHP enums: rol `admin|doctor|recepcionista|paciente`; estado cita `agendada|confirmada|en_consulta|completada|cancelada`.
- Columnas en español snake_case (`fecha_cita`, `perfil_paciente_id`); usar nomenclatura en español en código nuevo.

## Comandos (en `sistema-de-gestion-de-citas-medicas/`)
- `composer run dev` — servidor + queue + logs + keep-alive
- `composer run setup` — install + .env + key + migrate + npm build
- `composer run test` / `php artisan test` — suite de tests
- BD: MySQL, `DB_DATABASE=laravel`
