# 🏥 Módulo 3: Catálogo de Especialidades (Unificado)

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**  
> Última actualización: Julio 2026

---

> [!NOTE]  
> **Documento Unificado**: La documentación correspondiente al **Catálogo de Especialidades** ha sido formalmente unificada con el módulo de facultativos en:  
> 🔗 **[Módulo 2: Gestión de Doctores y Especialidades](./02-Gestion-de-Doctores.md)**

---

## Resumen de Contenido Integrado en el Módulo 2

Toda la arquitectura, código, modelos, repositorios, controladores, vistas Blade y flujos de datos de especialidades están ahora documentados de forma centralizada en el **Módulo 2**, incluyendo:

1. **Visión General y Estrategia de Acceso Dual:**
   - Catálogo maestro de especialidades y soft-toggle con el flag `activa`.
   - Consulta pública vía API REST (`GET /api/obtenerEspecialidades`) y gestión web administrativa (`GET /especialidades`).

2. **Capa de Base de Datos y Modelado:**
   - Migración de la tabla `especialidades` (`2026_01_01_000002_crear_tabla_especialidades.php`).
   - Migración de la tabla pivote `doctor_especialidad` (`2026_01_01_000006_crear_tabla_doctor_especialidad.php`).
   - Modelo Eloquent `Especialidad` (`app/Models/Especialidad.php`) con relaciones `doctores()` (M:N) y `citas()` (1:N).

3. **Lógica de Negocio y Controladores:**
   - `EspecialidadesRepository` (`app/Http/Repository/EspecialidadesRepository.php`) con métodos `obtenerEspecialidades()`, `registrarEspecialidad()`, `obtenerEspecialidad()`.
   - `EspecialidadesController` (API JSON) y `EspecialidadesWebController` (Blade SSR con validación inline).

4. **Interfaz de Usuario y Seeders:**
   - Vista Blade con Tailwind CSS y modal interactivo (`resources/views/especialidades/index.blade.php`).
   - Seeder con 15 ramas médicas estándar (`database/seeders/EspecialidadesSeeder.php`).

5. **Flujos de Operación y Relaciones:**
   - Diagramas de secuencia para consulta pública y registro administrativo.
   - Matriz de relaciones con Horarios, Citas y Reportes Estadísticos.

---

### Navegación

- 👈 **Ir al documento unificado:** [02 - Gestión de Doctores y Especialidades](./02-Gestion-de-Doctores.md)
- 👉 **Ir al siguiente módulo funcional:** [04 - Horarios y Bloqueos](./04-Horarios-y-Bloqueos.md)
