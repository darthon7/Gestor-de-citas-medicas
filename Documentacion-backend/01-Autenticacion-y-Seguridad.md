# 🔐 Módulo 1: Autenticación y Seguridad

> **Sistema de Gestión de Citas Médicas — Documentación Técnica Backend**
> Última actualización: Julio 2026

---

## Índice

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Diagrama de Arquitectura](#2-diagrama-de-arquitectura)
3. [Capa de Base de Datos — Migraciones](#3-capa-de-base-de-datos--migraciones)
4. [Capa de Modelos (Eloquent ORM)](#4-capa-de-modelos-eloquent-orm)
5. [Capa de Repositorios (Lógica de Negocio)](#5-capa-de-repositorios-lógica-de-negocio)
6. [Capa de Form Requests (Validación)](#6-capa-de-form-requests-validación)
7. [Capa de Controladores](#7-capa-de-controladores)
8. [Middleware de Seguridad](#8-middleware-de-seguridad)
9. [Sistema de Excepciones](#9-sistema-de-excepciones)
10. [Sistema de Correo Electrónico](#10-sistema-de-correo-electrónico)
11. [Rutas (API y Web)](#11-rutas-api-y-web)
12. [Configuración del Framework](#12-configuración-del-framework)
13. [Seeders (Datos Iniciales)](#13-seeders-datos-iniciales)
14. [Flujos Completos de Operación](#14-flujos-completos-de-operación)

---

## 1. Visión General del Módulo

El módulo de **Autenticación y Seguridad** es el pilar fundamental del sistema. Es responsable de controlar **quién accede** al sistema, **qué puede hacer** cada usuario según su rol, y **cómo se protege** la integridad de las cuentas ante ataques de fuerza bruta o accesos no autorizados.

### Responsabilidades principales

| Responsabilidad | Descripción |
|---|---|
| **Autenticación** | Verificar la identidad del usuario mediante credenciales (email + contraseña) |
| **Autorización** | Restringir el acceso a recursos según el rol del usuario (`admin`, `doctor`, `recepcionista`, `paciente`) |
| **Registro diferenciado** | Cada tipo de usuario tiene un flujo de registro distinto con validaciones específicas |
| **Recuperación de contraseña** | Flujo completo de recuperación mediante código de 6 dígitos enviado por correo |
| **Protección contra fuerza bruta** | Bloqueo temporal de cuenta tras 5 intentos fallidos consecutivos |
| **Verificación de cédula profesional** | Validación mock de cédulas médicas contra un registro interno (simulación SEP) |
| **Auditoría de acceso** | Registro de cada intento de login (exitoso o fallido) con IP y timestamp |

### Estrategia de autenticación dual

El sistema implementa **dos mecanismos de autenticación** simultáneos:

- **API (Sanctum Token):** Para la aplicación móvil. Se genera un token Bearer en cada login exitoso.
- **Web (Session):** Para el panel de administración. Usa cookies de sesión con el driver `session` de Laravel.

Ambos mecanismos comparten el **mismo repositorio de lógica** (`AuthRepository`), lo que garantiza consistencia en las reglas de negocio sin importar el canal de acceso.

---

## 2. Diagrama de Arquitectura

```
┌──────────────────────────────────────────────────────────────────────┐
│                        CLIENTE (Petición HTTP)                       │
│                   Móvil (API REST) │ Navegador (Web SSR)             │
└────────────────────────┬─────────────────────┬───────────────────────┘
                         │                     │
                         ▼                     ▼
              ┌──────────────────┐  ┌──────────────────────┐
              │   routes/api.php │  │    routes/web.php     │
              │  Prefijo: /api   │  │   Sin prefijo         │
              └────────┬─────────┘  └──────────┬────────────┘
                       │                       │
         ┌─────────────┼───────────────────────┼──────────────┐
         │      MIDDLEWARE PIPELINE (Bootstrap app.php)        │
         │  ┌─────────────────────────────────────────────┐   │
         │  │  auth:sanctum (API) │ auth (Web/Session)    │   │
         │  │  check.status  ──── CheckAccountStatus      │   │
         │  │  role:X        ──── RoleMiddleware           │   │
         │  └─────────────────────────────────────────────┘   │
         └─────────────┬───────────────────────┬──────────────┘
                       │                       │
                       ▼                       ▼
              ┌────────────────┐    ┌──────────────────────┐
              │ AuthController │    │  AuthWebController   │
              │   (API JSON)   │    │  (Blade + Redirect)  │
              └───────┬────────┘    └──────────┬───────────┘
                      │                        │
                      │   ┌────────────────┐   │
                      └──►│ Form Requests  │◄──┘
                          │  (Validación)  │
                          └───────┬────────┘
                                  │
                                  ▼
                      ┌───────────────────────┐
                      │    AuthRepository     │
                      │  (Lógica de Negocio)  │
                      └───────────┬───────────┘
                                  │
            ┌─────────────────────┼─────────────────────────┐
            │                     │                         │
            ▼                     ▼                         ▼
   ┌──────────────┐    ┌──────────────────┐     ┌───────────────────┐
   │   Modelos    │    │      Mail        │     │    Exceptions     │
   │  Eloquent    │    │ CodigoRecuperac. │     │  AuthException    │
   │  - Usuario   │    └──────────────────┘     └───────────────────┘
   │  - IntentoL. │
   │  - Verific.  │
   └──────┬───────┘
          │
          ▼
   ┌──────────────┐
   │   Database   │
   │   (SQLite)   │
   └──────────────┘
```

---

## 3. Capa de Base de Datos — Migraciones

Las migraciones definen la estructura de las tablas que soportan la autenticación. Son la **primera pieza** que se ejecuta al instalar el proyecto (`php artisan migrate`).

### 3.1 Tabla `usuarios`

**Archivo:** `database/migrations/2026_01_01_000001_crear_tabla_usuarios.php`

Esta es la tabla central de todo el sistema. Almacena las credenciales y metadatos de seguridad de **todos** los usuarios, sin importar su rol.

```php
Schema::create('usuarios', function (Blueprint $table) {
    $table->id();                                                          // PK autoincremental
    $table->string('nombre');                                              // Nombre completo
    $table->string('email')->unique();                                     // Email (login identifier), índice UNIQUE
    $table->string('password');                                            // Hash bcrypt de la contraseña
    $table->string('curp', 18)->unique()->nullable();                      // CURP mexicana, 18 chars exactos
    $table->string('telefono', 20)->nullable();                            // Teléfono de contacto
    $table->enum('rol', ['admin', 'doctor', 'recepcionista', 'paciente']); // Rol del usuario (RBAC)
    $table->enum('estado', ['activo', 'inactivo', 'bloqueado'])->default('activo'); // Estado de la cuenta
    $table->string('foto_perfil')->nullable();                             // Ruta a la imagen de perfil
    $table->integer('intentos_fallidos')->default(0);                      // Contador de brute-force
    $table->timestamp('bloqueado_hasta')->nullable();                      // Expiración del bloqueo temporal
    $table->rememberToken();                                               // Token para "Recuérdame"
    $table->timestamps();                                                  // created_at, updated_at
});
```

**Aspectos técnicos clave:**

- **`email` con `unique()`:** Garantiza a nivel de base de datos que no haya correos duplicados. Esto actúa como **segunda línea de defensa** después de la validación del Form Request.
- **`curp` con `unique()->nullable()`:** La CURP es única cuando se proporciona, pero es `nullable` porque el admin y recepcionistas podrían no requerirla.
- **`rol` como `enum`:** Restringe a nivel de BD los valores posibles. Esto es más eficiente que una tabla de roles para un sistema con roles fijos.
- **`estado` como `enum` con default `activo`:** Los tres estados posibles son:
  - `activo` → El usuario puede operar normalmente
  - `inactivo` → El administrador desactivó la cuenta
  - `bloqueado` → Bloqueo automático por intentos fallidos
- **`intentos_fallidos` + `bloqueado_hasta`:** Estas dos columnas trabajan juntas para implementar la protección contra fuerza bruta. El campo `bloqueado_hasta` almacena un timestamp futuro que indica cuándo expira el bloqueo.

---

### 3.2 Tabla `intentos_login`

**Archivo:** `database/migrations/2026_01_01_000013_crear_tabla_intentos_login.php`

Tabla de auditoría que registra **cada intento de inicio de sesión**, exitoso o fallido.

```php
Schema::create('intentos_login', function (Blueprint $table) {
    $table->id();
    $table->string('email');                          // Email intentado (puede no existir en BD)
    $table->string('direccion_ip', 45)->nullable();   // IP del cliente (soporta IPv6)
    $table->boolean('exitoso')->default(false);       // ¿Login exitoso?
    $table->timestamps();                             // Timestamp del intento
});
```

**¿Por qué `email` y no `usuario_id`?** Porque se necesita registrar intentos con emails que **no existen** en el sistema. Si usáramos una FK a `usuarios`, no podríamos loguear intentos con correos inexistentes, perdiendo información de seguridad valiosa.

**¿Por qué `direccion_ip` tiene 45 caracteres?** Porque las direcciones IPv6 pueden medir hasta 45 caracteres (e.g., `2001:0db8:85a3:0000:0000:8a2e:0370:7334`).

---

### 3.3 Tabla `verificaciones_cedula`

**Archivo:** `database/migrations/2026_01_01_000012_crear_tabla_verificaciones_cedula.php`

Simula un registro externo de cédulas profesionales (como el del sistema SEP real). Se usa para validar que un médico registrado tenga una cédula profesional legítima.

```php
Schema::create('verificaciones_cedula', function (Blueprint $table) {
    $table->id();
    $table->string('numero_cedula')->unique();   // Número de cédula profesional
    $table->string('nombre_titular');            // Nombre del profesionista registrado
    $table->string('profesion');                 // Profesión (e.g., "Médico Cirujano")
    $table->string('institucion')->nullable();   // Universidad emisora
    $table->boolean('es_valida')->default(true); // ¿La cédula está vigente?
    $table->timestamps();
});
```

**Diseño como Mock:** En un sistema productivo, esta validación se haría contra la API real de la Secretaría de Educación Pública (SEP). Aquí se simula con una tabla local que se llena con el seeder `VerificacionesCedulaSeeder`.

---

### 3.4 Tabla `password_resets`

**Archivo:** `database/migrations/2026_07_24_000001_crear_tabla_password_resets.php`

Almacena los códigos de verificación de 6 dígitos para el flujo de recuperación de contraseña.

```php
Schema::create('password_resets', function (Blueprint $table) {
    $table->string('email')->index();           // Email del usuario que solicitó recuperación
    $table->string('codigo', 6);                // Código de 6 dígitos
    $table->timestamp('created_at')->nullable(); // Para calcular expiración (30 min)
});
```

**Nota importante:** Esta tabla **no tiene `id`** ni `updated_at`. Es una tabla ligera y efímera: los registros se eliminan después de usarse exitosamente. El `index` sobre `email` optimiza las consultas frecuentes por correo.

---

### 3.5 Tabla `personal_access_tokens`

**Archivo:** `database/migrations/2026_07_20_011024_create_personal_access_tokens_table.php`

Tabla gestionada automáticamente por **Laravel Sanctum**. Almacena los tokens de acceso de la API.

```php
Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable');         // Relación polimórfica (tokenable_type + tokenable_id)
    $table->text('name');                // Nombre identificador del token (e.g., "auth")
    $table->string('token', 64)->unique(); // Hash SHA-256 del token
    $table->text('abilities')->nullable(); // Habilidades/permisos del token (JSON)
    $table->timestamp('last_used_at')->nullable(); // Último uso del token
    $table->timestamp('expires_at')->nullable()->index(); // Expiración
    $table->timestamps();
});
```

**¿Qué es `morphs('tokenable')`?** Crea dos columnas: `tokenable_type` (el FQCN del modelo, e.g., `App\Models\Usuario`) y `tokenable_id` (el ID del usuario). Esto permite que Sanctum trabaje con **cualquier modelo autenticable**, no solo `User`.

---

## 4. Capa de Modelos (Eloquent ORM)

Los modelos son la representación en PHP de las tablas de la base de datos. Definen cómo se interactúa con los datos, qué campos son asignables, cuáles están ocultos y cómo se relacionan entre sí.

### 4.1 Modelo `Usuario`

**Archivo:** `app/Models/Usuario.php`

```php
class Usuario extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'usuarios';
```

**Herencia y Traits — Explicación detallada:**

| Elemento | Propósito |
|---|---|
| `extends Authenticatable` | Hereda de `Illuminate\Foundation\Auth\User`, que implementa las interfaces `AuthenticatableContract`, `CanResetPassword` y `Authorizable`. Esto le permite al modelo funcionar con los guards de autenticación de Laravel (tanto `session` como `sanctum`). |
| `use HasFactory` | Habilita el uso de factories para testing (`Usuario::factory()->create()`). |
| `use HasApiTokens` | Trait de Sanctum que agrega la capacidad de crear, revocar y verificar tokens de API. Añade métodos como `createToken()`, `currentAccessToken()` y `tokens()`. |
| `$table = 'usuarios'` | Sobreescribe la convención de Laravel (que buscaría una tabla `usuarios` automáticamente por el nombre del modelo en plural, pero como el modelo es `Usuario`, Laravel buscaría `usuarios` de todas formas — sin embargo, es buena práctica explicitarlo para claridad). |

**Propiedades protegidas:**

```php
protected $fillable = [
    'nombre', 'email', 'password', 'curp',
    'telefono', 'rol', 'estado', 'foto_perfil',
    'intentos_fallidos', 'bloqueado_hasta',
];
```

`$fillable` define los campos que pueden ser asignados **masivamente** mediante `Usuario::create([...])` o `$usuario->update([...])`. Esto es una protección contra **Mass Assignment Attacks**: si un atacante enviara un campo como `rol=admin` en un request, Laravel lo ignoraría a menos que esté en `$fillable`.

```php
protected $hidden = ['password', 'remember_token'];
```

`$hidden` excluye estos campos cuando el modelo se serializa a JSON/array. Esto significa que al hacer `return response()->json($usuario)`, **nunca** se expondrá el hash de la contraseña ni el remember token en la respuesta API.

```php
protected $casts = [
    'password'        => 'hashed',
    'bloqueado_hasta' => 'datetime',
];
```

`$casts` transforma automáticamente los valores al leer/escribir:
- **`password => 'hashed'`:** El cast `hashed` (disponible desde Laravel 10) aplica `Hash::make()` automáticamente cuando se asigna un valor a `password`. Esto significa que `$usuario->password = 'texto_plano'` lo encriptará automáticamente. **Sin embargo**, en el `AuthRepository` se usa `Hash::make()` explícitamente, lo que resulta en un doble hash si no se tiene cuidado. Esto funciona correctamente porque el modelo solo aplica `hashed` cuando el valor cambia.
- **`bloqueado_hasta => 'datetime'`:** Convierte el timestamp a una instancia de `Carbon`, lo que permite usar métodos como `->isFuture()`, `->isPast()`, `->diffForHumans()`.

**Relaciones Eloquent:**

```php
public function perfilDoctor()      { return $this->hasOne(PerfilDoctor::class, 'usuario_id'); }
public function perfilPaciente()    { return $this->hasOne(PerfilPaciente::class, 'usuario_id'); }
public function perfilRecepcionista(){ return $this->hasOne(PerfilRecepcionista::class, 'usuario_id'); }
public function registrosAuditoria(){ return $this->hasMany(RegistroAuditoria::class, 'usuario_id'); }
```

Cada usuario tiene **exactamente un** perfil según su rol. Se usa `hasOne` porque la relación es 1:1. La relación con `RegistroAuditoria` es `hasMany` porque un usuario puede tener múltiples registros de auditoría.

> **Patrón de diseño:** Se usa un modelo base `Usuario` con perfiles específicos en tablas separadas. Esto es conocido como **Table-per-Type (TPT) Inheritance** — una alternativa a la **Single Table Inheritance (STI)** que evita tener una tabla con decenas de columnas nullable.

---

### 4.2 Modelo `IntentoLogin`

**Archivo:** `app/Models/IntentoLogin.php`

```php
class IntentoLogin extends Model
{
    use HasFactory;

    protected $table = 'intentos_login';

    protected $fillable = ['email', 'direccion_ip', 'exitoso'];

    protected $casts = ['exitoso' => 'boolean'];
}
```

Modelo simple de auditoría. El cast de `exitoso` a `boolean` garantiza que al leer el valor de la BD (que almacena `0` o `1`), PHP lo interprete como `true`/`false`.

> **¿Por qué no tiene relación con `Usuario`?** Como se explicó en la sección de migraciones, el campo `email` es un string libre que puede contener emails inexistentes. Esto permite auditar incluso intentos de acceso con correos que no pertenecen a ningún usuario registrado.

---

### 4.3 Modelo `VerificacionCedula`

**Archivo:** `app/Models/VerificacionCedula.php`

```php
class VerificacionCedula extends Model
{
    use HasFactory;

    protected $table = 'verificaciones_cedula';

    protected $fillable = ['numero_cedula', 'nombre_titular', 'profesion', 'institucion', 'es_valida'];

    protected $casts = ['es_valida' => 'boolean'];
}
```

Modelo que representa el registro de cédulas profesionales. El campo `es_valida` como booleano permite tener cédulas revocadas en el sistema (útil para testing con la cédula `9999999` que tiene `es_valida = false`).

---

## 5. Capa de Repositorios (Lógica de Negocio)

Los repositorios encapsulan **toda la lógica de negocio** del módulo. Los controladores nunca interactúan directamente con los modelos para operaciones complejas; siempre delegan al repositorio correspondiente.

### 5.1 `AuthRepository`

**Archivo:** `app/Http/Repository/AuthRepository.php`

Este es el repositorio más complejo del módulo. Contiene 7 métodos que cubren el ciclo completo de autenticación.

---

#### 5.1.1 Método `login(array $credenciales, string $ip = null)`

**Propósito:** Autenticar un usuario y retornar un token de acceso.

**Flujo completo paso a paso:**

```
1. Buscar usuario por email
2. Registrar intento de login (inicialmente como fallido)
3. ¿Usuario no existe? → AuthException 401
4. ¿Cuenta bloqueada con bloqueo vigente? → AuthException 403
5. ¿Bloqueo expirado? → Resetear estado a "activo"
6. ¿Cuenta inactiva? → AuthException 403
7. ¿Contraseña incorrecta?
   7a. Incrementar intentos_fallidos
   7b. ¿Alcanzó 5 intentos? → Bloquear por 15 minutos
   7c. Lanzar AuthException con intentos restantes
8. Login exitoso:
   8a. Resetear intentos_fallidos a 0
   8b. Marcar el último intento como exitoso
   8c. ¿Es doctor? → Verificar que esté validado
   8d. Generar token Sanctum
   8e. Retornar datos del usuario + token
```

**Código con análisis línea por línea:**

```php
public function login(array $credenciales, string $ip = null)
{
    try {
        // Paso 1: Busca al usuario por email. Si no existe, $usuario será null.
        $usuario = Usuario::where('email', $credenciales['email'])->first();

        // Paso 2: Se registra SIEMPRE el intento, inicialmente como fallido.
        // Esto garantiza auditoría incluso si el proceso falla después.
        IntentoLogin::create([
            'email'        => $credenciales['email'],
            'direccion_ip' => $ip,
            'exitoso'      => false,
        ]);

        // Paso 3: Usuario no existe
        if (!$usuario) {
            throw new AuthException('Las credenciales ingresadas son incorrectas', 401);
            // NOTA DE SEGURIDAD: El mensaje no dice "email no encontrado" para no
            // revelar si un email está registrado o no (prevención de user enumeration).
        }

        // Paso 4: Verificar bloqueo temporal activo
        if ($usuario->estado === 'bloqueado' && $usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture()) {
            throw new AuthException(
                'Cuenta bloqueada temporalmente. Intenta en ' . $usuario->bloqueado_hasta->diffForHumans(),
                403
            );
            // diffForHumans() genera mensajes como "en 12 minutos"
        }

        // Paso 5: Si el bloqueo ya expiró, restaurar la cuenta automáticamente
        if ($usuario->estado === 'bloqueado' && $usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isPast()) {
            $usuario->update([
                'estado'            => 'activo',
                'intentos_fallidos' => 0,
                'bloqueado_hasta'   => null,
            ]);
        }

        // Paso 6: Cuenta desactivada manualmente por admin
        if ($usuario->estado === 'inactivo') {
            throw new AuthException('Tu cuenta está desactivada. Contacta al administrador.', 403);
        }

        // Paso 7: Verificar contraseña
        if (!Hash::check($credenciales['password'], $usuario->password)) {
            $intentos = $usuario->intentos_fallidos + 1;

            // Paso 7b: Alcanzó el límite → bloquear
            if ($intentos >= 5) {
                $usuario->update([
                    'intentos_fallidos' => $intentos,
                    'estado'            => 'bloqueado',
                    'bloqueado_hasta'   => Carbon::now()->addMinutes(15),
                ]);
                throw new AuthException('Cuenta bloqueada por 15 minutos tras 5 intentos fallidos.', 403);
            }

            // Paso 7c: Aún tiene intentos
            $usuario->update(['intentos_fallidos' => $intentos]);
            throw new AuthException(
                'Las credenciales ingresadas son incorrectas. Intentos restantes: ' . (5 - $intentos),
                401
            );
        }

        // Paso 8a: Login exitoso — limpiar contador
        $usuario->update(['intentos_fallidos' => 0, 'bloqueado_hasta' => null]);

        // Paso 8b: Actualizar el registro de intento a exitoso
        IntentoLogin::latest()
            ->where('email', $credenciales['email'])
            ->first()
            ?->update(['exitoso' => true]);
        // El operador ?-> (nullsafe) evita error si por alguna razón no se encuentra el registro

        // Paso 8c: Validación especial para médicos
        if ($usuario->rol === 'doctor') {
            $perfil = PerfilDoctor::where('usuario_id', $usuario->id)->first();
            if ($perfil && $perfil->estado_validacion !== 'validado') {
                throw new AuthException(
                    'Tu cuenta de médico está pendiente de validación por el administrador.',
                    403
                );
            }
        }

        // Paso 8d: Generar token Sanctum
        $token = $usuario->createToken('auth')->plainTextToken;
        // createToken() genera un registro en personal_access_tokens
        // plainTextToken es el token en texto plano (solo disponible en el momento de creación)

        // Paso 8e: Retornar respuesta exitosa
        return [
            'mensaje'  => 'Login correcto',
            'usuario'  => $usuario,
            'rol'      => $usuario->rol,
            'token'    => $token,
        ];
    } catch (AuthException $e) {
        throw $e; // Re-lanza para que el controlador la maneje
    } catch (Exception $e) {
        return ['mensaje' => $e->getMessage()]; // Errores inesperados
    }
}
```

---

#### 5.1.2 Método `solicitarRecuperacion(array $data)`

**Propósito:** Generar y enviar por correo un código de 6 dígitos para recuperar contraseña.

```php
public function solicitarRecuperacion(array $data)
{
    $email  = $data['email'];
    // Genera un número aleatorio entre 0 y 999999, y lo rellena con ceros a la izquierda
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Elimina códigos previos del mismo email (solo se permite un código activo)
    DB::table('password_resets')->where('email', $email)->delete();

    // Inserta el nuevo código con el timestamp actual
    DB::table('password_resets')->insert([
        'email'      => $email,
        'codigo'     => $codigo,
        'created_at' => Carbon::now(),
    ]);

    // Envía el correo electrónico con el código
    Mail::to($email)->send(new CodigoRecuperacionMail($codigo));

    return ['mensaje' => 'Código de recuperación enviado a tu correo electrónico.'];
}
```

> **¿Por qué usa `DB::table()` en lugar del modelo?** Porque la tabla `password_resets` no tiene un modelo Eloquent dedicado. Es una tabla auxiliar simple que no necesita la complejidad de un modelo ORM. Usar el Query Builder es más eficiente para operaciones CRUD simples sobre tablas sin relaciones.

---

#### 5.1.3 Método `verificarCodigo(array $data)`

**Propósito:** Verificar que el código de 6 dígitos sea válido y no haya expirado.

```php
public function verificarCodigo(array $data)
{
    $reset = DB::table('password_resets')
        ->where('email', $data['email'])
        ->where('codigo', $data['codigo'])
        ->first();

    if (!$reset) {
        return ['valido' => false, 'mensaje' => 'El código de verificación es incorrecto.'];
    }

    // Verificar expiración: el código es válido por 30 minutos
    if (Carbon::parse($reset->created_at)->addMinutes(30)->isPast()) {
        return ['valido' => false, 'mensaje' => 'El código de verificación ha expirado. Solicita uno nuevo.'];
    }

    return ['valido' => true, 'mensaje' => 'Código verificado correctamente.'];
}
```

**Lógica de expiración:** Se toma el `created_at` del registro, se le suman 30 minutos con `addMinutes(30)`, y se verifica si esa fecha ya pasó con `isPast()`. Si es así, el código expiró.

---

#### 5.1.4 Método `restablecerPassword(array $data)`

**Propósito:** Cambiar la contraseña del usuario después de verificar el código.

```php
public function restablecerPassword(array $data)
{
    // Reutiliza la verificación del código
    $verificacion = $this->verificarCodigo($data);
    if (isset($verificacion['valido']) && !$verificacion['valido']) {
        return ['mensaje' => $verificacion['mensaje']];
    }

    $usuario = Usuario::where('email', $data['email'])->first();
    if (!$usuario) {
        return ['mensaje' => 'Usuario no encontrado.'];
    }

    // Actualiza la contraseña (Hash::make genera un nuevo hash bcrypt)
    $usuario->update(['password' => Hash::make($data['password'])]);

    // Elimina el código usado (one-time use)
    DB::table('password_resets')->where('email', $data['email'])->delete();

    return ['mensaje' => 'Contraseña restablecida correctamente.'];
}
```

> **Seguridad:** El código se elimina después de usarse, impidiendo que se reutilice. Además, se re-verifica el código antes de cambiar la contraseña como doble comprobación.

---

#### 5.1.5 Método `registrarPaciente(array $data)`

**Propósito:** Crear un nuevo usuario con rol `paciente` y su perfil asociado.

```php
public function registrarPaciente(array $data)
{
    // 1. Crear el usuario base
    $usuario = Usuario::create([
        'nombre'   => $data['nombre'],
        'email'    => $data['email'],
        'password' => Hash::make($data['password']),
        'curp'     => strtoupper($data['curp']),  // Normaliza a mayúsculas
        'telefono' => $data['telefono'] ?? null,
        'rol'      => 'paciente',                  // Rol fijo
        'estado'   => 'activo',                    // Activo inmediatamente
    ]);

    // 2. Generar número de expediente único
    // Formato: EXP-20260729-0001
    $numeroExpediente = 'EXP-' . now()->format('Ymd') . '-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT);

    // 3. Crear el perfil del paciente
    PerfilPaciente::create([
        'usuario_id'        => $usuario->id,
        'numero_expediente' => $numeroExpediente,
        'fecha_nacimiento'  => $data['fecha_nacimiento'] ?? null,
        'sexo'              => $data['sexo'] ?? null,
        'direccion'         => $data['direccion'] ?? null,
        'nss'               => $data['nss'] ?? null,
    ]);

    // 4. Generar token de acceso (auto-login al registrarse)
    $token = $usuario->createToken('auth')->plainTextToken;

    return [
        'mensaje'  => 'Paciente registrado correctamente',
        'usuario'  => $usuario->load('perfilPaciente'),  // Eager loading del perfil
        'token'    => $token,
    ];
}
```

**`load('perfilPaciente')`:** Carga la relación `perfilPaciente` después de crear el usuario. Esto es **Lazy Eager Loading** — útil cuando ya tienes la instancia del modelo y quieres cargar una relación que no se cargó inicialmente.

---

#### 5.1.6 Método `registrarMedico(array $data)`

**Propósito:** Crear un nuevo usuario con rol `doctor`, verificando su cédula profesional.

```php
public function registrarMedico(array $data)
{
    // 1. Verificar cédula contra el "registro SEP" (mock)
    $cedula = VerificacionCedula::where('numero_cedula', $data['cedula_profesional'])->first();
    if (!$cedula || !$cedula->es_valida) {
        return ['mensaje' => 'La cédula profesional no se encuentra registrada en el sistema de verificación.'];
    }

    // 2. Crear usuario
    $usuario = Usuario::create([...]);  // Similar al paciente pero con rol 'doctor'

    // 3. Crear perfil de doctor
    $perfilDoctor = PerfilDoctor::create([
        'usuario_id'          => $usuario->id,
        'cedula_profesional'  => $data['cedula_profesional'],
        'cedula_especialidad' => $data['cedula_especialidad'] ?? null,
        'estado_validacion'   => 'pendiente',  // ← REQUIERE validación del admin
    ]);

    // 4. Asignar especialidades médicas (relación Many-to-Many)
    if (!empty($data['especialidades'])) {
        $perfilDoctor->especialidades()->sync($data['especialidades']);
        // sync() reemplaza todas las relaciones existentes con las nuevas
    }

    return [
        'mensaje'  => 'Médico registrado. Tu cuenta está pendiente de validación por el administrador.',
        'usuario'  => $usuario->load('perfilDoctor'),
    ];
    // NOTA: No se genera token. El médico NO puede loguearse hasta ser validado.
}
```

**Diferencias clave con el registro de paciente:**
1. Se verifica la cédula profesional antes de crear al usuario.
2. El `estado_validacion` se establece como `pendiente` — el médico no puede acceder al sistema hasta que el administrador lo valide.
3. No se genera token ni se hace auto-login.
4. Se pueden asignar especialidades usando `sync()` en la tabla pivot `doctor_especialidad`.

---

#### 5.1.7 Método `registrarRecepcionista(array $data, int $adminId)`

**Propósito:** Crear un usuario recepcionista. Solo puede ser invocado por un administrador.

```php
public function registrarRecepcionista(array $data, int $adminId)
{
    $usuario = Usuario::create([
        // ... datos básicos con rol 'recepcionista'
    ]);

    PerfilRecepcionista::create([
        'usuario_id'          => $usuario->id,
        'numero_empleado'     => $data['numero_empleado'] ?? null,
        'unidad_asignada'     => $data['unidad_asignada'] ?? null,
        'turno'               => $data['turno'] ?? null,
        'creado_por_admin_id' => $adminId,  // Trazabilidad de quién creó la cuenta
    ]);

    return [
        'mensaje'  => 'Recepcionista registrada correctamente',
        'usuario'  => $usuario->load('perfilRecepcionista'),
    ];
}
```

**`$adminId`:** Este parámetro se inyecta desde el controlador usando `$request->user()->id`. Registra qué administrador creó la cuenta de la recepcionista — un requisito de trazabilidad importante.

---

#### 5.1.8 Método `cerrarSesion(Usuario $usuario)`

```php
public function cerrarSesion(Usuario $usuario)
{
    $usuario->currentAccessToken()->delete();
    // Elimina SOLO el token actual, no todos los tokens del usuario
    // Esto permite mantener sesiones activas en otros dispositivos
    return ['mensaje' => 'Sesión cerrada correctamente'];
}
```

---

### 5.2 `UsuariosRepository`

**Archivo:** `app/Http/Repository/UsuariosRepository.php`

Gestiona operaciones de perfil del usuario autenticado: consulta, actualización, cambio de contraseña y foto.

| Método | Descripción |
|---|---|
| `obtenerPerfil($id)` | Carga el usuario con todas sus relaciones de perfil (`perfilDoctor.especialidades`, `perfilPaciente`, `perfilRecepcionista`) usando eager loading |
| `actualizarPerfil($id, $data)` | Actualiza datos básicos (nombre, teléfono) y datos específicos del rol (e.g., dirección del paciente) |
| `cambiarPassword($id, $data)` | Verifica la contraseña actual antes de permitir el cambio |
| `actualizarFoto($id, $rutaFoto)` | Almacena la ruta de la foto de perfil |

---

### 5.3 `VerificacionCedulaRepository`

**Archivo:** `app/Http/Repository/VerificacionCedulaRepository.php`

```php
public function verificarCedula(string $numeroCedula, string $nombreTitular = null)
{
    $cedula = VerificacionCedula::where('numero_cedula', $numeroCedula)->first();

    if (!$cedula) {
        return ['mensaje' => 'Cédula no encontrada...', 'es_valida' => false];
    }

    if (!$cedula->es_valida) {
        return ['mensaje' => 'La cédula está registrada como inválida o revocada.', 'es_valida' => false];
    }

    return [
        'mensaje'        => 'Cédula verificada correctamente',
        'es_valida'      => true,
        'nombre_titular' => $cedula->nombre_titular,
        'profesion'      => $cedula->profesion,
        'institucion'    => $cedula->institucion,
    ];
}
```

> **Nota:** Este repositorio se usa internamente durante el registro de médicos. Podría expandirse para consultar una API externa real de la SEP.

---

## 6. Capa de Form Requests (Validación)

Los Form Requests son clases dedicadas a **validar datos de entrada** antes de que lleguen al controlador. Cada uno define reglas de validación, mensajes personalizados y el comportamiento ante fallos.

### Patrón común de todos los Form Requests

Todos los Form Requests del módulo comparten esta estructura:

```php
class StoreXxxRequest extends FormRequest
{
    // 1. Autorización: ¿El usuario puede hacer esta petición?
    public function authorize(): bool { return true; }

    // 2. Reglas de validación
    public function rules(): array { return [...]; }

    // 3. Mensajes personalizados en español
    public function messages(): array { return [...]; }

    // 4. Manejo de fallo: devuelve JSON en vez de redirect
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'mensaje' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
```

**¿Por qué se sobreescribe `failedValidation()`?** Por defecto, Laravel redirige al formulario anterior cuando la validación falla en una petición web. Al sobreescribir este método, **siempre** retornamos una respuesta JSON con código 422 (Unprocessable Entity). Esto es necesario para que la API REST funcione correctamente, ya que una redirección HTTP no tiene sentido para un cliente móvil.

---

### 6.1 `StoreLoginRequest`

```php
public function rules(): array
{
    return [
        'email'    => 'required|email',      // Obligatorio + formato email
        'password' => 'required|string',     // Obligatorio + tipo string
    ];
}
```

Validación mínima: solo verifica formato, no existencia. La verificación de existencia y contraseña se hace en el repositorio.

---

### 6.2 `StoreRegistroPacienteRequest`

```php
public function rules(): array
{
    return [
        'nombre'           => 'required|string|max:255',
        'email'            => 'required|email|unique:usuarios,email',
        'password'         => 'required|string|min:8|confirmed',
        'curp'             => [
            'required', 'string', 'size:18', 'unique:usuarios,curp',
            'regex:/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/'
        ],
        'telefono'         => 'nullable|string|max:20',
        'fecha_nacimiento' => 'nullable|date',
        'sexo'             => 'nullable|in:M,F',
        'direccion'        => 'nullable|string|max:500',
        'nss'              => 'nullable|string|max:20',
    ];
}
```

**Reglas destacadas:**

| Regla | Explicación técnica |
|---|---|
| `unique:usuarios,email` | Consulta la tabla `usuarios`, columna `email`, para verificar que no exista |
| `min:8\|confirmed` | La contraseña debe tener ≥8 caracteres y debe existir un campo `password_confirmation` que coincida |
| `regex` de CURP | Valida el formato oficial de la CURP mexicana: 4 letras + 6 dígitos + H/M (sexo) + 5 letras + 1 alfanumérico + 1 dígito |
| `size:18` | Exactamente 18 caracteres (longitud fija de la CURP) |
| `in:M,F` | Solo acepta los valores literales "M" o "F" |

---

### 6.3 `StoreRegistroMedicoRequest`

Hereda las validaciones del paciente y agrega:

```php
'cedula_profesional'  => 'required|string|unique:perfiles_doctor,cedula_profesional',
'cedula_especialidad' => 'nullable|string',
'especialidades'      => 'nullable|array',
'especialidades.*'    => 'exists:especialidades,id',
```

**`especialidades.*`:** La notación `.*` valida **cada elemento** del array. `exists:especialidades,id` verifica que cada ID proporcionado exista realmente en la tabla `especialidades`. Esto previene que se asignen especialidades inexistentes.

---

### 6.4 `StoreRegistroRecepcionistaRequest`

Validaciones más flexibles (la CURP es opcional, no requiere cédula):

```php
'curp'            => 'nullable|string|size:18',
'numero_empleado' => 'nullable|string|max:50',
'unidad_asignada' => 'nullable|string|max:255',
'turno'           => 'nullable|string|max:50',
```

---

### 6.5 `StoreRecuperacionRequest`

```php
'email' => 'required|email|exists:usuarios,email',
```

**`exists:usuarios,email`:** A diferencia del login (donde no verificamos existencia en la validación), aquí sí lo hacemos. Esto es porque necesitamos enviar un correo, y tiene sentido informar al usuario si su email no está registrado.

---

### 6.6 `StoreVerificarCodigoRequest`

```php
'email'  => 'required|email|exists:usuarios,email',
'codigo' => 'required|string|size:6',
```

El código debe ser exactamente 6 caracteres (`size:6`).

---

### 6.7 `StoreRestablecerPasswordRequest`

```php
'email'    => 'required|email|exists:usuarios,email',
'codigo'   => 'required|string|size:6',
'password' => 'required|string|min:8|confirmed',
```

Combina las validaciones del código con las de contraseña nueva.

---

## 7. Capa de Controladores

Los controladores son **orquestadores delgados** (thin controllers). Su responsabilidad es:
1. Recibir la petición (ya validada por el Form Request)
2. Delegar al repositorio
3. Formatear la respuesta (JSON para API, redirect/view para Web)

### 7.1 `AuthController` (API)

**Archivo:** `app/Http/Controllers/AuthController.php`

Atiende las peticiones de la **API REST** (aplicación móvil).

```php
class AuthController extends Controller
{
    protected $authRepository;

    // Inyección de dependencias via constructor
    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }
```

**Inyección de dependencias:** Laravel resuelve automáticamente `AuthRepository` del **Service Container**. Esto significa que no necesitas hacer `new AuthRepository()` manualmente — Laravel crea la instancia y la inyecta en el constructor.

**Ejemplo de método — `login()`:**

```php
public function login(StoreLoginRequest $request)
{
    try {
        // StoreLoginRequest ya validó el email y password antes de llegar aquí
        $resultado = $this->authRepository->login($request->all(), $request->ip());
        return response()->json($resultado, 200);
    } catch (AuthException $e) {
        // Captura excepciones de autenticación con su código HTTP personalizado
        return response()->json(['mensaje' => $e->getMessage()], $e->getHttpCode());
    } catch (\Exception $e) {
        // Errores inesperados → 500
        return response()->json(['mensaje' => $e->getMessage()], 500);
    }
}
```

**Cada método del controlador sigue el mismo patrón:**
1. Recibe un `StoreXxxRequest` (validación automática)
2. Invoca el método correspondiente del repositorio
3. Retorna `response()->json()` con el resultado
4. Captura excepciones en dos niveles: `AuthException` (esperadas) y `Exception` (inesperadas)

**Tabla de métodos del AuthController:**

| Método | Form Request | Método del Repositorio | Descripción |
|---|---|---|---|
| `login()` | `StoreLoginRequest` | `login()` | Iniciar sesión |
| `solicitarRecuperacion()` | `StoreRecuperacionRequest` | `solicitarRecuperacion()` | Enviar código al email |
| `verificarCodigo()` | `StoreVerificarCodigoRequest` | `verificarCodigo()` | Validar código de 6 dígitos |
| `restablecerPassword()` | `StoreRestablecerPasswordRequest` | `restablecerPassword()` | Cambiar contraseña |
| `registrarPaciente()` | `StoreRegistroPacienteRequest` | `registrarPaciente()` | Auto-registro de paciente |
| `registrarMedico()` | `StoreRegistroMedicoRequest` | `registrarMedico()` | Auto-registro de médico |
| `registrarRecepcionista()` | `StoreRegistroRecepcionistaRequest` | `registrarRecepcionista()` | Registro (solo admin) |
| `cerrarSesion()` | `Request` (sin validación) | `cerrarSesion()` | Revocar token actual |

---

### 7.2 `AuthWebController` (Web/Blade)

**Archivo:** `app/Http/Controllers/Web/AuthWebController.php`

Atiende las peticiones del **panel web** (Blade SSR). Usa el **mismo `AuthRepository`** que el API controller.

**Diferencias clave con el API controller:**

| Aspecto | AuthController (API) | AuthWebController (Web) |
|---|---|---|
| **Respuesta exitosa** | `response()->json(...)` | `redirect()->route(...)` |
| **Respuesta error** | JSON con código HTTP | `back()->with('error', ...)` |
| **Autenticación** | Token Sanctum | Session (`Auth::login()`) |
| **Tiene vistas** | No | Sí (`showLogin`, `showRegistro`, etc.) |

**Método `login()` del Web controller:**

```php
public function login(StoreLoginRequest $request)
{
    try {
        $resultado = $this->authRepository->login($request->all(), $request->ip());
        if (isset($resultado['usuario'])) {
            // Inicia sesión web (cookie de sesión)
            Auth::login($resultado['usuario']);
            // Regenera el ID de sesión para prevenir session fixation attacks
            $request->session()->regenerate();

            // Redirige según el rol
            if ($resultado['usuario']->rol === 'doctor') {
                return redirect()->route('doctor.agenda');
            }
            return redirect()->intended(route('dashboard'));
            // intended() redirige a la URL que el usuario intentó visitar antes del login
        }
        return back()->withInput()->with('error', $resultado['mensaje'] ?? 'Error al iniciar sesión.');
    } catch (AuthException $e) {
        return back()->withInput()->with('error', $e->getMessage());
    }
}
```

**`session()->regenerate()`:** Previene ataques de **Session Fixation**. Después de autenticar al usuario, se genera un nuevo ID de sesión, invalidando cualquier ID previo que un atacante pudiera haber capturado.

**Método `logout()`:**

```php
public function logout(Request $request)
{
    if (Auth::check()) {
        $this->authRepository->cerrarSesion($request->user()); // Elimina token API si existe
        Auth::logout();                     // Destruye la sesión web
        $request->session()->invalidate();  // Invalida todos los datos de sesión
        $request->session()->regenerateToken(); // Regenera el token CSRF
    }
    return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
}
```

**Métodos de renderizado de vistas:**

```php
public function showLogin()
{
    if (Auth::check()) {
        return redirect()->route('dashboard'); // Si ya está logueado, redirige
    }
    return view('auth.login');
}
```

Cada método `show*` verifica si el usuario ya está autenticado para evitar mostrar formularios innecesarios.

---

## 8. Middleware de Seguridad

Los middlewares son **filtros** que se ejecutan **antes** (o después) de que una petición llegue al controlador. Son la primera línea de defensa del sistema.

### 8.1 Registro de Middlewares

**Archivo:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->statefulApi();  // Habilita autenticación stateful para SPA
    $middleware->alias([
        'role'         => \App\Http\Middleware\RoleMiddleware::class,
        'check.status' => \App\Http\Middleware\CheckAccountStatus::class,
    ]);
})
```

**`statefulApi()`:** Configura Sanctum para que las peticiones API desde dominios de primera parte (SPA) puedan usar cookies de sesión en lugar de tokens Bearer. Los dominios permitidos se configuran en `config/sanctum.php`.

---

### 8.2 `CheckAccountStatus` — Verificación de Estado de Cuenta

**Archivo:** `app/Http/Middleware/CheckAccountStatus.php`

Se ejecuta en **cada petición autenticada**. Verifica que la cuenta del usuario siga activa.

```php
public function handle(Request $request, Closure $next): Response
{
    $usuario = $request->user();

    // 1. Sin usuario autenticado
    if (!$usuario) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['mensaje' => 'No autenticado.'], 401);
        }
        return redirect()->route('login');
    }

    // 2. Cuenta desactivada por admin
    if ($usuario->estado === 'inactivo') {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['mensaje' => 'Tu cuenta está desactivada...'], 403);
        }
        Auth::logout();
        return redirect()->route('login')->with('error', 'Tu cuenta está desactivada...');
    }

    // 3. Cuenta bloqueada temporalmente
    if ($usuario->estado === 'bloqueado') {
        if ($usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture()) {
            // Bloqueo vigente
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([...], 403);
            }
            Auth::logout();
            return redirect()->route('login')->with('error', '...');
        }
        // Bloqueo expirado: auto-rehabilitar
        $usuario->update(['estado' => 'activo', 'intentos_fallidos' => 0, 'bloqueado_hasta' => null]);
    }

    // 4. Todo OK → continuar al controlador
    return $next($request);
}
```

**Detección inteligente del tipo de respuesta:** El middleware usa `$request->expectsJson() || $request->is('api/*')` para determinar si debe responder con JSON (API) o con un redirect (Web). Esto permite usar el **mismo middleware** para ambos canales.

**Auto-rehabilitación:** Si un usuario bloqueado hace una petición después de que su bloqueo haya expirado, el middleware automáticamente lo rehabilita sin intervención del admin.

---

### 8.3 `RoleMiddleware` — Control de Acceso por Rol (RBAC)

**Archivo:** `app/Http/Middleware/RoleMiddleware.php`

Implementa **Role-Based Access Control**. Verifica que el usuario tenga uno de los roles permitidos para la ruta.

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    $usuario = $request->user();

    if (!$usuario) {
        // Sin autenticación
        return response()->json(['mensaje' => 'No autenticado.'], 401);
    }

    // Verifica si el rol del usuario está en la lista de roles permitidos
    if (!in_array($usuario->rol, $roles)) {
        return response()->json([
            'mensaje' => 'No tienes permisos... Rol requerido: ' . implode(' o ', $roles) . '.',
        ], 403);
    }

    return $next($request);
}
```

**`string ...$roles`:** El operador splat (`...`) permite recibir **múltiples roles** como argumentos. Cuando la ruta dice `middleware('role:admin,recepcionista')`, Laravel parsea la string después de `:` y separa por `,`, pasando cada rol como un argumento individual.

**Ejemplo de uso en rutas:**
```php
Route::middleware(['role:admin,recepcionista'])->group(function () {...}); // Admin O Recepcionista
Route::middleware(['role:admin'])->group(function () {...});              // Solo Admin
Route::middleware(['role:doctor'])->group(function () {...});             // Solo Doctor
Route::middleware(['role:paciente'])->group(function () {...});           // Solo Paciente
```

---

## 9. Sistema de Excepciones

### `AuthException`

**Archivo:** `app/Exceptions/AuthException.php`

```php
class AuthException extends Exception
{
    protected $httpCode;

    public function __construct(string $message, int $httpCode = 401)
    {
        parent::__construct($message);
        $this->httpCode = $httpCode;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
```

**¿Por qué una excepción personalizada?** Las excepciones estándar de PHP (`Exception`) no tienen un concepto de "código HTTP". `AuthException` agrega la propiedad `$httpCode` que permite al controlador responder con el código HTTP adecuado:

| Código | Significado | Uso en el sistema |
|---|---|---|
| `401` | Unauthorized | Credenciales incorrectas |
| `403` | Forbidden | Cuenta bloqueada, inactiva o sin permisos |

**Flujo de la excepción:**
```
AuthRepository lanza AuthException(mensaje, código_http)
    → AuthController la captura con catch(AuthException $e)
        → Responde response()->json(['mensaje' => $e->getMessage()], $e->getHttpCode())
```

### Configuración de excepciones globales

**Archivo:** `bootstrap/app.php`

```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(
        fn (Request $request) => $request->is('api/*'),
    );
})
```

Esto le dice a Laravel que renderice **todas** las excepciones no capturadas como JSON cuando la petición proviene de la API. Esto incluye errores de validación, errores 404, errores 500, etc.

---

## 10. Sistema de Correo Electrónico

### `CodigoRecuperacionMail`

**Archivo:** `app/Mail/CodigoRecuperacionMail.php`

```php
class CodigoRecuperacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $codigo;

    public function __construct(string $codigo)
    {
        $this->codigo = $codigo;
    }

    public function build()
    {
        return $this->subject('Código de recuperación de contraseña')
                    ->html("
                        <h2>Recuperación de Contraseña</h2>
                        <p>Has solicitado restablecer tu contraseña en el Sistema de Citas Médicas.</p>
                        <p>Tu código de verificación de 6 dígitos es:</p>
                        <h1 style='color: #007bff; letter-spacing: 5px;'>{$this->codigo}</h1>
                        <p>Este código expira en 30 minutos.</p>
                        <p>Si no solicitaste este cambio, ignora este correo.</p>
                    ");
    }
}
```

**Traits utilizados:**

| Trait | Propósito |
|---|---|
| `Queueable` | Permite enviar el correo de forma asíncrona usando colas de Laravel. Con `Mail::to()->queue()` en vez de `send()`, el correo se encolaría sin bloquear la respuesta HTTP. |
| `SerializesModels` | Serializa automáticamente los modelos Eloquent cuando el mail se encola. Almacena solo el ID y la clase del modelo, y lo re-consulta al procesarlo. |

**`->html()`:** Genera el contenido del correo directamente desde HTML inline. En producción, es recomendable usar una vista Blade con `->view('emails.codigo-recuperacion')` para mayor mantenibilidad.

**Invocación en el repositorio:**
```php
Mail::to($email)->send(new CodigoRecuperacionMail($codigo));
```

La clase `Mail` de Laravel se encarga del transporte configurado en `config/mail.php` (SMTP, Mailgun, SES, etc.).

---

## 11. Rutas (API y Web)

### 11.1 Rutas API (`routes/api.php`)

Las rutas API se registran con el prefijo automático `/api` (configurado en `bootstrap/app.php`).

```php
// ═══════════════════════════════════════════════
// RUTAS PÚBLICAS (sin autenticación)
// ═══════════════════════════════════════════════

Route::prefix('auth')->group(function () {
    Route::post('/login',                [AuthController::class, 'login']);
    Route::post('/registrarPaciente',    [AuthController::class, 'registrarPaciente']);
    Route::post('/registrarMedico',      [AuthController::class, 'registrarMedico']);
    Route::post('/solicitarRecuperacion',[AuthController::class, 'solicitarRecuperacion']);
    Route::post('/verificarCodigo',      [AuthController::class, 'verificarCodigo']);
    Route::post('/restablecerPassword',  [AuthController::class, 'restablecerPassword']);
});

// ═══════════════════════════════════════════════
// RUTAS PROTEGIDAS (auth:sanctum + check.status)
// ═══════════════════════════════════════════════

Route::middleware(['auth:sanctum', 'check.status'])->group(function () {

    // Cerrar sesión (cualquier usuario autenticado)
    Route::post('/auth/cerrarSesion', [AuthController::class, 'cerrarSesion']);

    // Solo admin puede registrar recepcionistas
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/auth/registrarRecepcionista', [AuthController::class, 'registrarRecepcionista']);
    });
});
```

**Estructura de URLs resultantes:**

| Método HTTP | URL Completa | Autenticación | Rol |
|---|---|---|---|
| `POST` | `/api/auth/login` | Pública | — |
| `POST` | `/api/auth/registrarPaciente` | Pública | — |
| `POST` | `/api/auth/registrarMedico` | Pública | — |
| `POST` | `/api/auth/solicitarRecuperacion` | Pública | — |
| `POST` | `/api/auth/verificarCodigo` | Pública | — |
| `POST` | `/api/auth/restablecerPassword` | Pública | — |
| `POST` | `/api/auth/cerrarSesion` | `auth:sanctum` | Cualquiera |
| `POST` | `/api/auth/registrarRecepcionista` | `auth:sanctum` | `admin` |

---

### 11.2 Rutas Web (`routes/web.php`)

```php
// ═══════════════════════════════════════════════
// RUTAS DE INVITADO (middleware 'guest')
// ═══════════════════════════════════════════════

Route::middleware('guest')->group(function () {
    Route::get('/login',               [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login',              [AuthWebController::class, 'login']);

    Route::get('/registro',            [AuthWebController::class, 'showRegistro'])->name('registro');
    Route::post('/registro',           [AuthWebController::class, 'registrar']);

    Route::get('/recuperar-password',  [AuthWebController::class, 'showRecuperar'])->name('recuperar');
    Route::post('/recuperar-password', [AuthWebController::class, 'solicitarRecuperacion']);

    Route::get('/verificar-codigo',    [AuthWebController::class, 'showVerificarCodigo'])->name('verificar.codigo');
    Route::post('/verificar-codigo',   [AuthWebController::class, 'verificarCodigo']);

    Route::get('/restablecer-password',[AuthWebController::class, 'showRestablecer'])->name('restablecer');
    Route::post('/restablecer-password',[AuthWebController::class, 'restablecerPassword']);
});

// ═══════════════════════════════════════════════
// RUTAS PROTEGIDAS (auth + check.status)
// ═══════════════════════════════════════════════

Route::middleware(['auth', 'check.status'])->group(function () {
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
});
```

**Patrón GET/POST doble:**

Cada ruta de autenticación web usa el patrón de **dos rutas con la misma URL**:
- `GET /login` → Muestra el formulario (vista Blade)
- `POST /login` → Procesa el formulario (lógica de autenticación)

Este es el patrón estándar **PRG (Post-Redirect-Get)** de Laravel.

**Middleware `guest`:** Este middleware de Laravel redirige a los usuarios **ya autenticados** al dashboard. Previene que un usuario logueado vea los formularios de login/registro.

**Named routes (`->name('login')`):** Permiten referirse a las rutas por nombre en vez de por URL. Ejemplo: `redirect()->route('login')` en vez de `redirect('/login')`. Esto desacopla el código de las URLs concretas.

---

## 12. Configuración del Framework

### 12.1 `config/auth.php`

```php
'defaults' => [
    'guard'     => env('AUTH_GUARD', 'web'),        // Guard por defecto: sesiones
    'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
],

'guards' => [
    'web' => [
        'driver'   => 'session',   // Usa cookies de sesión
        'provider' => 'users',     // Busca usuarios con el provider 'users'
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => env('AUTH_MODEL', Usuario::class),  // ← Apunta al modelo PERSONALIZADO
    ],
],
```

**Punto crítico:** El provider usa `Usuario::class` en vez del `User::class` predeterminado de Laravel. Esto conecta toda la infraestructura de autenticación (guards, middlewares, helpers como `auth()->user()`) con nuestro modelo personalizado.

---

### 12.2 `config/sanctum.php`

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort(),
))),

'guard'      => ['web'],     // Sanctum usa el guard 'web' para autenticación stateful
'expiration' => null,        // Los tokens no expiran automáticamente
```

**Dominios stateful:** Son los dominios desde los cuales Sanctum permite autenticación basada en cookies (en vez de tokens Bearer). Esto es útil para SPAs que se sirven desde el mismo dominio que la API.

---

## 13. Seeders (Datos Iniciales)

### 13.1 `AdminUserSeeder`

**Archivo:** `database/seeders/AdminUserSeeder.php`

```php
Usuario::firstOrCreate(
    ['email' => 'admin@citasmedicas.com'],  // Criterio de búsqueda
    [
        'nombre'   => 'Administrador Principal',
        'password' => Hash::make('Admin1234!'),
        'curp'     => 'ADMP900101HDFXXX00',
        'telefono' => '5500000000',
        'rol'      => 'admin',
        'estado'   => 'activo',
    ]
);
```

**`firstOrCreate()`:** Busca un usuario con el email especificado. Si existe, lo retorna sin modificarlo. Si no existe, lo crea con los datos proporcionados. Esto hace que el seeder sea **idempotente** (se puede ejecutar múltiples veces sin efectos secundarios).

**Credenciales del administrador por defecto:**
- **Email:** `admin@citasmedicas.com`
- **Password:** `Admin1234!`

> ⚠️ **Seguridad:** En producción, estas credenciales deben cambiarse inmediatamente después del primer login.

---

### 13.2 `VerificacionesCedulaSeeder`

**Archivo:** `database/seeders/VerificacionesCedulaSeeder.php`

Precarga 6 cédulas profesionales para testing:

| Cédula | Titular | Institución | Válida |
|---|---|---|---|
| `1234567` | Juan Carlos López Martínez | UNAM | ✅ |
| `2345678` | María Elena Rodríguez García | IPN | ✅ |
| `3456789` | Roberto Sánchez Pérez | UAM | ✅ |
| `4567890` | Ana Patricia Flores Hernández | UNAM | ✅ |
| `5678901` | Carlos Mendoza Torres | Anáhuac | ✅ |
| `9999999` | Cédula Inválida Test | Test | ❌ |

La cédula `9999999` con `es_valida = false` permite probar el escenario de rechazo de registro médico con cédula revocada.

---

## 14. Flujos Completos de Operación

### 14.1 Flujo de Login (API — Aplicación Móvil)

```
Cliente Móvil                     Servidor Laravel
     │                                  │
     │  POST /api/auth/login            │
     │  { email, password }             │
     │─────────────────────────────────►│
     │                                  │
     │                    ┌─────────────┤
     │                    │ StoreLoginRequest
     │                    │ • email: required|email
     │                    │ • password: required|string
     │                    │ ¿Válido? ──► Sí → continúa
     │                    │           └► No → 422 JSON
     │                    └─────────────┤
     │                                  │
     │                    ┌─────────────┤
     │                    │ AuthController::login()
     │                    │ → AuthRepository::login()
     │                    │   1. Buscar usuario
     │                    │   2. Registrar intento
     │                    │   3. Verificar estado
     │                    │   4. Verificar contraseña
     │                    │   5. Generar token Sanctum
     │                    └─────────────┤
     │                                  │
     │  200 OK                          │
     │  { mensaje, usuario, rol, token }│
     │◄─────────────────────────────────│
     │                                  │
     │  (Subsecuentes peticiones)       │
     │  Authorization: Bearer {token}   │
     │─────────────────────────────────►│
     │                    ┌─────────────┤
     │                    │ auth:sanctum middleware
     │                    │ → verifica token en personal_access_tokens
     │                    │ check.status middleware
     │                    │ → verifica estado de la cuenta
     │                    │ role:X middleware
     │                    │ → verifica rol del usuario
     │                    └─────────────┤
```

---

### 14.2 Flujo de Login (Web — Panel de Administración)

```
Navegador                         Servidor Laravel
     │                                  │
     │  GET /login                      │
     │─────────────────────────────────►│
     │                                  │
     │  200 OK (Vista auth.login)       │
     │◄─────────────────────────────────│
     │                                  │
     │  POST /login                     │
     │  { email, password, _token }     │
     │─────────────────────────────────►│
     │                                  │
     │              ┌───────────────────┤
     │              │ AuthWebController::login()
     │              │ → AuthRepository::login()
     │              │ → Auth::login($usuario)
     │              │ → session()->regenerate()
     │              └───────────────────┤
     │                                  │
     │  302 Redirect → /dashboard       │
     │  Set-Cookie: laravel_session=... │
     │◄─────────────────────────────────│
     │                                  │
     │  GET /dashboard                  │
     │  Cookie: laravel_session=...     │
     │─────────────────────────────────►│
     │              ┌───────────────────┤
     │              │ auth middleware (sesión)
     │              │ check.status middleware
     │              └───────────────────┤
```

---

### 14.3 Flujo de Recuperación de Contraseña

```
Paso 1: Solicitar código
══════════════════════════
POST /api/auth/solicitarRecuperacion  { email }
  → StoreRecuperacionRequest valida email + exists
  → AuthRepository::solicitarRecuperacion()
    → Genera código aleatorio de 6 dígitos
    → Inserta en tabla password_resets
    → Envía CodigoRecuperacionMail
  ← { mensaje: "Código enviado" }

Paso 2: Verificar código
══════════════════════════
POST /api/auth/verificarCodigo  { email, codigo }
  → StoreVerificarCodigoRequest valida email + codigo(size:6)
  → AuthRepository::verificarCodigo()
    → Busca en password_resets por email + codigo
    → Verifica que no hayan pasado 30 minutos
  ← { valido: true/false, mensaje: "..." }

Paso 3: Restablecer contraseña
══════════════════════════════════
POST /api/auth/restablecerPassword  { email, codigo, password, password_confirmation }
  → StoreRestablecerPasswordRequest valida todo
  → AuthRepository::restablecerPassword()
    → Re-verifica el código (doble seguridad)
    → Actualiza contraseña con Hash::make()
    → Elimina registro de password_resets
  ← { mensaje: "Contraseña restablecida" }
```

---

### 14.4 Flujo de Registro de Médico

```
POST /api/auth/registrarMedico
{
    nombre, email, password, password_confirmation,
    curp, telefono, cedula_profesional,
    cedula_especialidad, especialidades: [1, 3]
}

    ┌─────────────────────────────────────────────────┐
    │ StoreRegistroMedicoRequest                      │
    │ • Valida CURP con regex mexicana                │
    │ • Verifica unicidad de email, curp y cédula     │
    │ • Verifica que especialidades[*] existan en BD  │
    └────────────────────┬────────────────────────────┘
                         │
    ┌────────────────────▼────────────────────────────┐
    │ AuthRepository::registrarMedico()               │
    │                                                 │
    │ 1. VerificacionCedula::where(cedula)->first()   │
    │    ¿Cédula válida en mock SEP? ──► No → Error   │
    │                                                 │
    │ 2. Usuario::create([rol => 'doctor'])            │
    │                                                 │
    │ 3. PerfilDoctor::create([                       │
    │        estado_validacion => 'pendiente'          │
    │    ])                                           │
    │                                                 │
    │ 4. $perfilDoctor->especialidades()->sync([1,3]) │
    │    (tabla pivot doctor_especialidad)             │
    │                                                 │
    │ ⚠️ NO genera token (no puede loguearse aún)     │
    └────────────────────┬────────────────────────────┘
                         │
                         ▼
    Respuesta: "Tu cuenta está pendiente de validación"

    ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─

    (Más tarde, el administrador valida al médico
     desde el módulo de Gestión de Doctores)
    PATCH /api/validarDoctor/{id}
    → PerfilDoctor::update(['estado_validacion' => 'validado'])
    → Ahora el médico puede loguearse
```

---

## Mapa de Archivos del Módulo

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Exceptions/
│   │   └── AuthException.php                          # Excepción personalizada con HTTP code
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php                     # Controller API (JSON responses)
│   │   │   └── Web/
│   │   │       └── AuthWebController.php              # Controller Web (Blade + redirects)
│   │   ├── Middleware/
│   │   │   ├── CheckAccountStatus.php                 # Verifica estado activo/bloqueado/inactivo
│   │   │   └── RoleMiddleware.php                     # RBAC por rol (admin, doctor, etc.)
│   │   ├── Repository/
│   │   │   ├── AuthRepository.php                     # Lógica central de autenticación
│   │   │   ├── UsuariosRepository.php                 # Gestión de perfil de usuario
│   │   │   └── VerificacionCedulaRepository.php       # Mock de verificación SEP
│   │   └── Requests/
│   │       ├── StoreLoginRequest.php                  # Validación de login
│   │       ├── StoreRegistroPacienteRequest.php       # Validación registro paciente
│   │       ├── StoreRegistroMedicoRequest.php         # Validación registro médico
│   │       ├── StoreRegistroRecepcionistaRequest.php  # Validación registro recepcionista
│   │       ├── StoreRecuperacionRequest.php           # Validación solicitud recuperación
│   │       ├── StoreVerificarCodigoRequest.php        # Validación verificación de código
│   │       └── StoreRestablecerPasswordRequest.php    # Validación restablecimiento password
│   ├── Mail/
│   │   └── CodigoRecuperacionMail.php                 # Mailable con código de 6 dígitos
│   └── Models/
│       ├── Usuario.php                                # Modelo central (Authenticatable + Sanctum)
│       ├── IntentoLogin.php                           # Registro de auditoría de accesos
│       └── VerificacionCedula.php                     # Mock de registro SEP
├── bootstrap/
│   └── app.php                                        # Registro de middlewares y excepciones
├── config/
│   ├── auth.php                                       # Guards, providers y modelo de usuario
│   └── sanctum.php                                    # Configuración de tokens API
├── database/
│   ├── migrations/
│   │   ├── ..._crear_tabla_usuarios.php               # Tabla principal de usuarios
│   │   ├── ..._crear_tabla_intentos_login.php         # Auditoría de login
│   │   ├── ..._crear_tabla_verificaciones_cedula.php  # Mock SEP
│   │   ├── ..._crear_tabla_password_resets.php        # Códigos de recuperación
│   │   └── ..._create_personal_access_tokens.php      # Tokens Sanctum
│   └── seeders/
│       ├── AdminUserSeeder.php                        # Admin por defecto
│       └── VerificacionesCedulaSeeder.php             # Cédulas de prueba
└── routes/
    ├── api.php                                        # Rutas REST para app móvil
    └── web.php                                        # Rutas para panel web (Blade)
```

---

> **Siguiente módulo:** [02 - Gestión de Doctores](./02-Gestion-de-Doctores.md)
