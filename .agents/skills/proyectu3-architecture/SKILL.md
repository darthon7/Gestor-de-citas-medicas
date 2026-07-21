---
name: proyectu3-architecture
description: Directivas de arquitectura y patrón Repository-Controller-Request para el desarrollo de proyectos Laravel estilo Proyectu3. Debe aplicarse al crear modelos, repositorios, controladores, form requests o rutas en este entorno.
---

# Guía de Estilo y Arquitectura "Proyectu3" (Laravel REST API)

Esta Skill define las reglas obligatorias de desarrollo para mantener la coherencia y el patrón arquitectónico del proyecto `Proyectu3` en Laravel. **Toda la IA debe seguir estrictamente estas reglas al generar, modificar o refactorizar código.**

---

## 1. Arquitectura General y Capas de Responsabilidad

El proyecto sigue un patrón desacoplado basado en **Repository Pattern**:

```
[ HTTP Request / Route ]
          │
          ▼
[ Form Request (Validation) ] ──(Falla)──> Respuesta JSON 422
          │
       (Pasa)
          ▼
[ Controller (App\Http\Controllers) ]
          │
          ▼
[ Repository (App\Http\Repository) ]
          │
          ▼
[ Model Eloquent (App\Models) ] <──> [ Base de Datos ]
```

---

## 2. Definición de Capas y Reglas Sintácticas

### 2.1. Modelos (`app/Models/`)
* **Ubicación:** `app/Models/[Entidad].php`
* **Definición Obligatoria:**
  * Uso del trait `HasFactory`.
  * Definición explícita de `$table` (ej. `protected $table = 'alumnos';`).
  * Definición explícita de `$fillable` con todos los campos asignables de forma masiva.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class alumnos extends Model
{
    use HasFactory;
    
    protected $table = 'alumnos';

    protected $fillable = [
        'nombre',
        'matricula',
    ];
}
```

---

### 2.2. Repositorios (`app/Http/Repository/`)
* **Ubicación:** `app/Http/Repository/[Entidad]Repository.php` (Nota: El namespace es `App\Http\Repository`).
* **Responsabilidad:** Toda consulta o interacción con la base de datos (Eloquent) debe ejecutarse EXCLUSIVAMENTE dentro del Repositorio. LOS CONTROLADORES NO LLAMAN A ELOQUENT DIRECTAMENTE.
* **Convención de Nombres de Métodos (en Español):**
  * `obtener[Entidades]()` -> Lista todos los registros.
  * `registrar[Entidad](array $data)` -> Crea un nuevo registro.
  * `obtener[Entidad](int $id)` -> Busca un registro por ID.
  * `actualizar[Entidad](int $id, array $data)` -> Actualiza campos.
  * `eliminar[Entidad](int $id)` -> Elimina un registro.
* **Manejo de Respuestas:** Retornan arrays asociativos formateados con estructura `["mensaje" => "...", "data" => ...]` o mensajes de error capturados mediante `try-catch`.

```php
<?php

namespace App\Http\Repository;

use App\Models\alumnos;

class AlumnosRepository
{
    public function obtenerAlumnos()
    {
        try {
            $alumnos = alumnos::all();
            return [
                "mensaje" => "Alumnos obtenidos correctamente",
                "data" => $alumnos
            ];
        } catch (\Exception $e) {
            return ["mensaje" => $e->getMessage()];
        }
    }

    public function registrarAlumno(array $data)
    {
        try {
            $alumno = alumnos::create([
                "nombre" => $data["nombre"],
                "matricula" => $data["matricula"],
            ]);
            return [
                "mensaje" => "Alumno registrado correctamente",
                "data" => $alumno
            ];
        } catch (\Exception $e) {
            return ["mensaje" => $e->getMessage()];
        }
    }

    public function obtenerAlumno(int $id)
    {
        try {
            $alumno = alumnos::find($id);
            if (!$alumno) {
                return ["mensaje" => "Alumno no encontrado"];
            }
            return $alumno;
        } catch (\Exception $e) {
            return ["mensaje" => $e->getMessage()];
        }
    }

    public function actualizarAlumno(int $id, array $data)
    {
        try {
            $alumno = $this->obtenerAlumno($id);
            if (!($alumno instanceof alumnos)) {
                return $alumno;
            }
            $alumno->update([
                "nombre" => $data["nombre"] ?? $alumno->nombre,
                "matricula" => $data["matricula"] ?? $alumno->matricula,
            ]);
            $alumno->save();
            return ["mensaje" => "Alumno actualizado correctamente", "Alumno" => $alumno];
        } catch (\Exception $e) {
            return ["mensaje" => $e->getMessage()];
        }
    }

    public function eliminarAlumno(int $id)
    {
        try {
            $alumno = $this->obtenerAlumno($id);
            if (!($alumno instanceof alumnos)) {
                return $alumno;
            }
            $alumno->delete();
            return ["mensaje" => "Alumno eliminado"];
        } catch (\Exception $e) {
            return ["mensaje" => $e->getMessage()];
        }
    }
}
```

---

### 2.3. Controladores (`app/Http/Controllers/`)
* **Ubicación:** `app/Http/Controllers/[Entidad]Controller.php`
* **Inyección de Dependencias:** El Repositorio se inyecta en el constructor.
* **Manejo de Errores y Formato:** Todos los métodos de respuesta REST se envuelven en bloques `try-catch (\Exception $e)` y retornan `response()->json(..., 200)` o `response()->json(["mensaje" => $e->getMessage()], 500)`.
* **Uso de Form Requests:** Inyectar `Store[Entidad]Request` y `Update[Entidad]Request` en los métodos `store` y `update`.

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorealumnosRequest;
use App\Http\Requests\UpdatealumnosRequest;
use App\Http\Repository\AlumnosRepository;

class AlumnosController extends Controller
{
    protected $alumnoRepository;

    public function __construct(AlumnosRepository $alumnoRepository)
    {
        $this->alumnoRepository = $alumnoRepository;
    }

    public function index()
    {
        try {
            $alumnos = $this->alumnoRepository->obtenerAlumnos();
            return response()->json($alumnos, 200);
        } catch (\Exception $e) {
            return response()->json(["mensaje" => $e->getMessage()], 500);
        }
    }

    public function store(StorealumnosRequest $request)
    {
        try {
            $alumno = $this->alumnoRepository->registrarAlumno($request->all());
            return response()->json($alumno, 200);
        } catch (\Exception $e) {
            return response()->json(["mensaje" => $e->getMessage()], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $alumno = $this->alumnoRepository->obtenerAlumno($id);
            return response()->json($alumno, 200);
        } catch (\Exception $e) {
            return response()->json(["mensaje" => $e->getMessage()], 500);
        }
    }

    public function update(UpdatealumnosRequest $request, int $id)
    {
        try {
            $alumno = $this->alumnoRepository->actualizarAlumno($id, $request->all());
            return response()->json($alumno, 200);
        } catch (\Exception $e) {
            return response()->json(["mensaje" => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $res = $this->alumnoRepository->eliminarAlumno($id);
            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(["mensaje" => $e->getMessage()], 500);
        }
    }
}
```

---

### 2.4. Validaciones / Form Requests (`app/Http/Requests/`)
* **Ubicación:** `app/Http/Requests/Store[entidad]Request.php` y `Update[entidad]Request.php`.
* **Personalización de Errores:** Sobrescribir el método `failedValidation` para devolver una estructura JSON estandarizada en caso de falla con código `422`.

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorealumnosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required',
            'matricula' => 'required|unique:alumnos,matricula'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es requerido.',
            'matricula.required' => 'El campo matricula es requerido.',
            'matricula.unique' => 'La matricula ya esta registrada.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            "msj" => "Error de validacion",
            "errors" => $validator->errors()
        ], 422));
    }
}
```

---

### 2.5. Rutas API (`routes/api.php`)
* Declarar rutas RESTful explícitas o de recurso (`Route::resource(...)`) manteniendo coherencia con las rutas semánticas en español:
  * `/obtener[Entidades]` (`GET`) -> `Controller@index`
  * `/registrar[Entidad]` (`POST`) -> `Controller@store`
  * `/obtener[Entidad]/{id}` (`GET`) -> `Controller@show`
  * `/actualizar[Entidad]/{id}` (`PUT`) -> `Controller@update`
  * `/eliminar[Entidad]/{id}` (`DELETE`) -> `Controller@destroy`

---

### 2.6. Autenticación y Tokens Sanctum (`AuthRepository`)
* **Modelo `User` (`app/Models/User.php`):** Debe incluir la utilización del trait `Laravel\Sanctum\HasApiTokens`, `HasFactory` y `Notifiable`. Las contraseñas deben estar casteadas con `'password' => 'hashed'`.
* **Lógica de Login (`app/Http/Repository/AuthRepository.php`):** La autenticación y registro de usuarios se gestionan mediante el repositorio `AuthRepository`.
  * `login(array $credenciales)`: Busca el usuario por `email`, valida la contraseña con `Hash::check()`, genera un token con `$user->createToken('auth')->plainTextToken()` y retorna el mensaje, usuario y token.
  * `registrarUsuario(array $data)`: Hashea la contraseña con `Hash::make()` y crea el registro con su rol asociado.
* **Protección de Rutas:** Usar el middleware `auth:sanctum` para asegurar endpoints privados.

```php
<?php

namespace App\Http\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthRepository
{
    public function login(array $credenciales)
    {
        $user = User::where('email', '=', $credenciales['email'])->first();

        if (!$user || !Hash::check($credenciales['password'], $user->password)) {
            return ['mensaje' => 'Las credenciales ingresadas son incorrectas'];
        }

        $token = $user->createToken('auth')->plainTextToken();

        return [
            'mensaje' => 'Login correcto',
            'usuario' => $user,
            'token'   => $token
        ];
    }

    public function registrarUsuario(array $data)
    {
        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'rol_id'   => $data['rol_id'],
            ]);

            return [
                'mensaje' => 'usuario registrado',
                'usuario' => $user
            ];
        } catch (Exception $e) {
            return [
                'mensaje' => $e->getMessage()
            ];
        }
    }
}
```

---

## 3. Reglas Inviolables para Asistentes de IA

1. **NO MEZCLAR LÓGICA:** No escribir consultas Eloquent o de Base de Datos directamente en los Controllers. Todo pasa por la capa `Repository`.
2. **NAMESPACE EXACTO DE REPOSITORIO:** Los repositorios se guardan bajo `namespace App\Http\Repository;` (carpeta `app/Http/Repository`).
3. **INYECCIÓN DE DEPENDENCIAS:** Los controladores reciben el repositorio vía constructor.
4. **TRY-CATCH OBLIGATORIO:** Métodos de controlador y repositorio deben estar siempre protegidos con bloques `try-catch`.
5. **RESPUESTAS JSON ESTÁNDAR:** Las respuestas exitosas devuelven HTTP 200 con JSON. Las fallas de validación devuelven HTTP 422. Excepciones devuelven HTTP 500 con `{"mensaje": $e->getMessage()}`.
6. **MANTENER EL ESTILO Y NOMBRES EN ESPAÑOL:** Para métodos de repositorios y rutas API personalizadas, seguir la semántica expresada en español (`obtener...`, `registrar...`, `actualizar...`, `eliminar...`).
7. **AUTENTICACIÓN CON SANCTUM:** El login y la emisión de tokens de autenticación API se deben implementar a través de `AuthRepository` utilizando `createToken('auth')->plainTextToken()` y `Hash::check()`.

