# 🌐 Conexión y Consumo de Servicios Web REST API (Backend Laravel 11 & Cliente Web JS)

## 🎯 Objetivo
Aprenderás a construir servicios web seguros y desacoplados utilizando la arquitectura **Repository-Controller-Request (Estilo Proyectu3)** en Laravel 11, y a consumirlos desde un cliente o panel web mediante JavaScript moderno (`Fetch API` / `async-await`) enviando encabezados de autenticación con **Laravel Sanctum**.

---

## 🧠 Conceptos clave

- **Servicio Web REST API** — *Como la ventanilla única de atención al cliente de un banco:* Una interfaz estandarizada que recibe solicitudes HTTP desde cualquier cliente (panel web, app móvil, sistema externo) y devuelve respuestas estructuradas en formato JSON.
- **Patrón Repositorio (Repository Pattern)** — *Como el encargado de un almacén:* La capa intermedia encargada exclusivamente de buscar, guardar o eliminar datos en la base de datos. Evita que el gerente (Controlador) tenga que ir personalmente a mover cajas.
- **Inyección de Dependencias** — *Como entregarle las herramientas de trabajo a un técnico en su mano:* En lugar de que el controlador cree su propio repositorio desde cero con `new`, el framework Laravel se lo entrega listo en el constructor.
- **Form Request (Capa de Validación)** — *Como el oficial de seguridad en la entrada:* Revisa que la petición contenga todos los campos obligatorios y con el formato correcto antes de permitirle llegar al controlador. Si falla, rebota la petición de inmediato con un código HTTP 422.
- **Sanctum Bearer Token** — *Como un brazalete VIP de acceso a un evento:* Una clave criptográfica enviada en la cabecera HTTP de cada petición que le demuestra al servidor quién eres sin necesidad de ingresar tu usuario y contraseña repetidamente.

---

## 🗺️ Mapa del proyecto

Estructura de archivos backend y cliente web bajo la arquitectura `Proyectu3` en Laravel 11:

```
sistema-de-gestion-de-citas-medicas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── CitasController.php        <-- Controlador REST (Inyecta el Repositorio)
│   │   ├── Repository/
│   │   │   └── CitasRepository.php       <-- Consultas Eloquent y Lógica de BD
│   │   └── Requests/
│   │       └── StoreCitaRequest.php      <-- Validación de entrada con HTTP 422
│   └── Models/
│       └── Cita.php                      <-- Modelo Eloquent ($table y $fillable)
├── routes/
│   └── api.php                           <-- Definición de endpoints REST y Sanctum
└── public/
    ├── js/
    │   └── citas-api.js                  <-- Cliente JavaScript (Fetch API + async/await)
    └── index.html                        <-- Interfaz de usuario web
```

---

## 🔨 Paso a paso

---

### Paso 1: Definir el Modelo Eloquent (`Cita.php`)

**🤔 ¿Por qué este paso?**
El modelo representa la tabla de la base de datos en código orientado a objetos. En Laravel, especificar `$table` y la propiedad `$fillable` protege a tu base de datos contra vulnerabilidades de asignación masiva (*Mass Assignment Vulnerability*).

**🛠️ ¿Cómo?**
Crea el archivo `app/Models/Cita.php` importando `HasFactory` e indicando los campos permitidos para inserción.

**Código de referencia:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    // Definición explícita de la tabla en la base de datos MySQL
    protected $table = 'citas';

    // Lista blanca de campos que se pueden registrar de forma masiva
    protected $fillable = [
        'paciente_id',
        'doctor_id',
        'especialidad_id',
        'fecha_hora',
        'estado',
        'motivo_consulta'
    ];
}
```

> 💡 **Qué hace este fragmento:** Define el mapeo de la entidad Cita hacia la tabla `citas` especificando explícitamente qué columnas se pueden escribir desde la aplicación.

> ⚠️ **Error común:** Olvidar declarar la propiedad `$fillable`. Si intentas guardar un registro masivo con `Cita::create()`, Laravel lanzará una excepción `MassAssignmentException`.

---

### Paso 2: Crear la Capa de Validación `StoreCitaRequest.php`

**🤔 ¿Por qué este paso?**
Los controladores no deben lidiar con lógica de validación de entradas. Un `Form Request` aísla las reglas de negocio y, si la validación falla, sobrescribimos `failedValidation()` para garantizar que el servicio web responda con formato JSON estandarizado y código HTTP 422.

**🛠️ ¿Cómo?**
Crea `app/Http/Requests/StoreCitaRequest.php` definiendo las reglas en `rules()` y personalizando la respuesta de error en `failedValidation()`.

**Código de referencia:**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Permitir que la petición pase a la fase de validación
    }

    public function rules(): array
    {
        return [
            'doctor_id'       => 'required|exists:doctores,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'fecha_hora'      => 'required|date|after:now',
            'motivo_consulta' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required'       => 'El médico es obligatorio.',
            'fecha_hora.after'         => 'La cita debe agendarse para una fecha posterior a la actual.',
            'motivo_consulta.required' => 'Debe ingresar el motivo de la consulta.'
        ];
    }

    // Sobrescribir respuesta de falla para devolver formato JSON 422 estandarizado
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            "msj"    => "Error de validacion",
            "errors" => $validator->errors()
        ], 422));
    }
}
```

> 💡 **Qué hace este fragmento:** Valida los datos recibidos antes de tocar el controlador y, en caso de error, responde inmediatamente con un JSON estructurado de estado 422.

> ⚠️ **Error común:** No sobrescribir `failedValidation()`. Si no lo haces, Laravel redirigirá la petición como si fuera un formulario HTML tradicional en lugar de responder un JSON.

---

### Paso 3: Construir el Repositorio de Datos (`CitasRepository.php`)

**🤔 ¿Por qué este paso?**
La regla de oro de la arquitectura `Proyectu3` prohíbe invocar Eloquent en los controladores. Toda la interacción con MySQL se concentra en esta capa utilizando métodos nombrados en español (`obtener...`, `registrar...`, `cancelar...`) envueltos en bloques `try-catch`.

**🛠️ ¿Cómo?**
Crea `app/Http/Repository/CitasRepository.php` asegurando que pertenece al namespace `App\Http\Repository`.

**Código de referencia:**

```php
<?php

namespace App\Http\Repository;

use App\Models\Cita;
use Exception;

class CitasRepository
{
    // Obtener todas las citas registradas en el sistema
    public function obtenerCitas()
    {
        try {
            $citas = Cita::with(['paciente', 'doctor', 'especialidad'])->get();
            return [
                "mensaje" => "Citas obtenidas correctamente",
                "data"    => $citas
            ];
        } catch (Exception $e) {
            return ["mensaje" => $e->getMessage()];
        }
    }

    // Registrar una nueva cita médica en la BD
    public function registrarCita(array $data)
    {
        try {
            $cita = Cita::create([
                "paciente_id"     => $data["paciente_id"],
                "doctor_id"       => $data["doctor_id"],
                "especialidad_id" => $data["especialidad_id"],
                "fecha_hora"      => $data["fecha_hora"],
                "estado"          => 'pendiente',
                "motivo_consulta" => $data["motivo_consulta"],
            ]);

            return [
                "mensaje" => "Cita registrada correctamente",
                "data"    => $cita
            ];
        } catch (Exception $e) {
            return ["mensaje" => $e->getMessage()];
        }
    }

    // Cancelar cita por ID
    public function cancelarCita(int $id)
    {
        try {
            $cita = Cita::find($id);
            if (!$cita) {
                return ["mensaje" => "La cita no existe"];
            }
            $cita->estado = 'cancelada';
            $cita->save();

            return ["mensaje" => "Cita cancelada correctamente", "data" => $cita];
        } catch (Exception $e) {
            return ["mensaje" => $e->getMessage()];
        }
    }
}
```

> 💡 **Qué hace este fragmento:** Aísla las consultas de base de datos en funciones reutilizables con control de excepciones y estructura de respuesta homogénea.

> ⚠️ **Error común:** Escribir `namespace App\Repositories;`. El namespace obligatorio según las normas de arquitectura del proyecto es `App\Http\Repository`.

---

### Paso 4: Programar el Controlador REST (`CitasController.php`)

**🤔 ¿Por qué este paso?**
El controlador actúa como un orquestador ligero. Recibe la petición ya validada por el `Form Request`, llama al repositorio inyectado en su constructor y transforma el resultado en una respuesta HTTP JSON adecuada (200 OK o 500 Error).

**🛠️ ¿Cómo?**
Crea `app/Http/Controllers/CitasController.php` inyectando `CitasRepository` en el constructor.

**Código de referencia:**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCitaRequest;
use App\Http\Repository\CitasRepository;
use Illuminate\Http\Request;

class CitasController extends Controller
{
    protected $citasRepository;

    // Inyección de dependencias del repositorio mediante constructor
    public function __construct(CitasRepository $citasRepository)
    {
        $this->citasRepository = $citasRepository;
    }

    public function index()
    {
        try {
            $respuesta = $this->citasRepository->obtenerCitas();
            return response()->json($respuesta, 200);
        } catch (\Exception $e) {
            return response()->json(["mensaje" => $e->getMessage()], 500);
        }
    }

    public function store(StoreCitaRequest $request)
    {
        try {
            // Asignar el ID del usuario autenticado vía Sanctum como paciente
            $datos = $request->all();
            $datos['paciente_id'] = $request->user()->id;

            $respuesta = $this->citasRepository->registrarCita($datos);
            return response()->json($respuesta, 200);
        } catch (\Exception $e) {
            return response()->json(["mensaje" => $e->getMessage()], 500);
        }
    }

    public function cancel(int $id)
    {
        try {
            $respuesta = $this->citasRepository->cancelarCita($id);
            return response()->json($respuesta, 200);
        } catch (\Exception $e) {
            return response()->json(["mensaje" => $e->getMessage()], 500);
        }
    }
}
```

> 💡 **Qué hace este fragmento:** Expone los endpoints de la API delegando la persistencia al repositorio y garantizando respuestas JSON envueltas en bloques `try-catch`.

> ⚠️ **Error común:** Escribir consultas Eloquent como `Cita::all()` dentro del controlador. Violaría la regla estricta de separación de capas.

---

### Paso 5: Registrar las Rutas API en `routes/api.php`

**🤔 ¿Por qué este paso?**
Define las direcciones URL (Endpoints) por las que los clientes web y móviles accederán al servicio web. Agrupamos las rutas protegidas dentro del middleware `auth:sanctum` para requerir token de sesión.

**🛠️ ¿Cómo?**
Edita `routes/api.php` agregando las rutas de autenticación y recursos médicos.

**Código de referencia:**

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitasController;

// Rutas Públicas (Login y Registro)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/registro', [AuthController::class, 'register']);

// Rutas Protegidas por Token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Obtener todas las citas
    Route::get('/citas', [CitasController.php, 'index']);
    
    // Agendar una nueva cita
    Route::post('/citas', [CitasController.php, 'store']);
    
    // Cancelar cita por ID
    Route::put('/citas/{id}/cancelar', [CitasController.php, 'cancel']);
    
});
```

> 💡 **Qué hace este fragmento:** Mapea las URLs del servidor hacia los métodos del controlador, exigiendo un token de autenticación para las rutas protegidas.

> ⚠️ **Error común:** Declarar rutas API en `routes/web.php`. Las rutas en `web.php` incluyen protección CSRF orientada a vistas de navegador y no a clientes API REST.

---

### Paso 6: Consumir el Servicio Web desde el Cliente Frontend JavaScript (`Fetch API`)

**🤔 ¿Por me este paso?**
Una vez construido el servicio web en Laravel, demostraremos cómo una aplicación web del lado del cliente (Frontend) se conecta enviando la cabecera `Authorization: Bearer <TOKEN>` y procesa la respuesta JSON asíncronamente con `async/await`.

**🛠️ ¿Cómo?**
Crea el archivo `public/js/citas-api.js` implementando funciones para realizar llamadas a la API REST.

**Código de referencia:**

```javascript
// public/js/citas-api.js

const API_BASE_URL = 'http://localhost:8000/api';

// Función para obtener la lista de citas desde la Web API
async function obtenerCitasServicioWeb() {
    const token = localStorage.getItem('token_sanctum');

    try {
        const response = await fetch(`${API_BASE_URL}/citas`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}` // Envío del Token Sanctum
            }
        });

        const resultado = await response.json();

        if (!response.ok) {
            throw new Error(resultado.mensaje || 'Error al obtener citas');
        }

        console.log('Citas recibidas:', resultado.data);
        renderizarTablaCitas(resultado.data);

    } catch (error) {
        console.error('Error en la petición:', error.message);
        alert(`Error de conexión: ${error.message}`);
    }
}

// Función para agendar cita enviando datos JSON por POST
async function agendarCitaServicioWeb(datosCita) {
    const token = localStorage.getItem('token_sanctum');

    try {
        const response = await fetch(`${API_BASE_URL}/citas`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(datosCita)
        });

        const resultado = await response.json();

        if (response.status === 422) {
            // Manejo de errores de validación del Form Request
            console.warn('Errores de validación:', resultado.errors);
            alert('Por favor revise los datos ingresados.');
            return;
        }

        if (!response.ok) {
            throw new Error(resultado.mensaje);
        }

        alert('¡Cita agendada con éxito!');
        obtenerCitasServicioWeb(); // Recargar la lista

    } catch (error) {
        console.error('Error al agendar:', error.message);
    }
}
```

> 💡 **Qué hace este fragmento:** Consume la REST API desde el cliente web mediante `Fetch API`, inyectando el token Bearer en los encabezados HTTP y manejando respuestas HTTP 200 y 422.

> ⚠️ **Error común:** Olvidar la cabecera `'Accept': 'application/json'`. Sin esta cabecera, ante un error Laravel podría devolver una página de error en HTML completo en lugar de JSON.

---

## 🔍 Preguntas de comprensión

1. **¿Por qué la arquitectura `Proyectu3` exige que los Controladores no ejecuten consultas Eloquent directamente y se use la capa `Repository`?**
2. **¿Qué sucede cuando una petición falla la validación en `StoreCitaRequest` gracias al método `failedValidation()`?**
3. **¿Por qué es necesario incluir la cabecera `Authorization: Bearer <token>` en cada petición realizada a rutas protegidas por Sanctum?**
4. **¿Cuál es la diferencia de responsabilidad entre un `Form Request` y un `Repository` en Laravel?**

---

## ✅ Cómo saber que funciona

1. **Prueba de Servicio Web REST (Postman / Thunder Client / JS):**
   - Haz una petición `POST` a `http://localhost:8000/api/login` con email y password válidos. Verifica que responda con código `200 OK` y contenga una propiedad `"token"`.
2. **Prueba de Validación 422:**
   - Realiza un `POST` a `/api/citas` enviando un cuerpo vacío `{}`. El servicio debe responder HTTP `422` con la estructura:
     ```json
     {
       "msj": "Error de validacion",
       "errors": { "doctor_id": ["El médico es obligatorio."] }
     }
     ```
3. **Prueba de Consumo Exitoso:**
   - Llama a `obtenerCitasServicioWeb()` desde tu consola o navegador web y confirma que el objeto impreso en consola contiene la lista de citas almacenadas en la base de datos MySQL.

---

## 🚀 Reto extra (opcional)

Crea un repositorio `ReportesRepository` con un método `obtenerEstadisticasCitas()` que devuelva el total de citas agendadas, atendidas y canceladas del mes actual mediante consultas agregadas de Eloquent, y expón este servicio web en un endpoint `/api/reportes/estadisticas`.

---

## 📚 Para profundizar (opcional)

- **Laravel Middleware & Role Authorization** — Creación de middlewares personalizados para restringir el consumo de servicios web según el rol del usuario (`admin`, `doctor`, `paciente`).
- **CORS (Cross-Origin Resource Sharing)** — Configuración de `config/cors.php` en Laravel para permitir llamadas de servicios web desde dominios y puertos externos de forma segura.
- **RESTful API Rate Limiting** — Limitación de tasa de peticiones (*Throttle*) en Laravel para proteger los servicios web contra ataques de denegación de servicio (DDoS).
