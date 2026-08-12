# Cambios Recientes — Formulario de Registro de Pacientes

Documento de los cambios más recientes aplicados al formulario de registro de pacientes
(`sistema-de-gestion-de-citas-medicas/resources/views/auth/registro.blade.php`) y su validación backend.

## 1. Validación de NSS (Número de Seguridad Social)

**Archivo:** `resources/views/auth/registro.blade.php` (línea ~82)

El campo `nss` sigue siendo **opcional**, pero si el usuario lo llena debe contener
exactamente **11 dígitos numéricos**.

Cambios en el `<input>`:

| Atributo | Valor | Efecto |
|---|---|---|
| `pattern` | `[0-9]{11}` | El navegador rechaza el envío si no son 11 dígitos |
| `maxlength` | `11` | Limita la captura a 11 caracteres |
| `inputmode` | `numeric` | Muestra teclado numérico en dispositivos móviles |
| `title` | Mensaje de ayuda | Texto que el navegador muestra si falla el `pattern` |

**Nota:** no se modificó el backend (`StoreRegistroPacienteRequest`) ni la app móvil
(`Movil-citasmedicas/`), a petición del usuario.

## 2. Restricción de mayoría de edad (18 años) en Fecha de Nacimiento

El campo `fecha_nacimiento` ahora es **obligatorio** y solo acepta personas de **18 años o más**.

### Frontend — `resources/views/auth/registro.blade.php` (línea ~65)

- `required` agregado: el navegador bloquea el envío si el campo está vacío.
- `max="{{ now()->subYears(18)->toDateString() }}"`: el calendario del navegador
  deshabilita cualquier fecha posterior a hace 18 años (calculada dinámicamente con el
  helper `now()` de Carbon, siempre vigente).
- Etiqueta actualizada a `Fecha de Nacimiento *`, siguiendo el patrón de los demás campos obligatorios.

### Backend — `app/Http/Requests/StoreRegistroPacienteRequest.php`

Regla antes/después:

```php
// Antes
'fecha_nacimiento' => 'nullable|date',

// Después
'fecha_nacimiento' => 'required|date|before_or_equal:' . now()->subYears(18)->toDateString(),
```

Nuevos mensajes de error agregados en `messages()`:

```php
'fecha_nacimiento.required'        => 'La fecha de nacimiento es requerida.',
'fecha_nacimiento.before_or_equal' => 'Debe tener al menos 18 años cumplidos para registrarse.',
```

La regla `before_or_equal` se calcula en cada request con `now()`, por lo que la fecha
límite (hace 18 años) siempre está actualizada.

## Alcance

- Solo se modificaron 2 archivos:
  - `resources/views/auth/registro.blade.php`
  - `app/Http/Requests/StoreRegistroPacienteRequest.php`
- Nada del backend se tocó en el punto 1 (NSS).
- No se tocó la app móvil (`Movil-citasmedicas/`).