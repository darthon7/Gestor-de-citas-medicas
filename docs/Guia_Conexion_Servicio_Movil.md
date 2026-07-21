# 📱 Conexión de Aplicación Android Kotlin a Backend REST API (Sistema de Citas Médicas)

## 🎯 Objetivo
Aprenderás a conectar una aplicación móvil desarrollada en **Android (Kotlin)** con una **API REST Backend (Laravel 11)** para autenticar usuarios (pacientes/médicos), gestionar tokens de sesión y consumir endpoints de servicios médicos en tiempo real.

---

## 🧠 Conceptos clave

- **API REST (Representational State Transfer)** — *Como el menú y los meseros de un restaurante:* La aplicación móvil es el cliente en la mesa, el servidor backend es la cocina y la API REST es el mesero que lleva los pedidos (peticiones HTTP) y trae los platillos (respuestas JSON).
- **Cliente HTTP (Volley)** — *Como una oficina postal en tu teléfono:* Se encarga de empaquetar tus mensajes, enviarlos por internet a una dirección específica (URL) y recibir los datos de vuelta sin congelar la pantalla.
- **Patrón Singleton** — *Como la credencial de identidad única de una persona:* Un objeto global que garantiza que solo exista una instancia de una clase en toda la aplicación para compartir datos de sesión o colas de red.
- **Inflado Dinámico (LayoutInflater)** — *Como usar un molde de repostería varias veces:* Tomas un diseño visual base en XML y lo duplicas repetidamente en pantalla llenando cada copia con datos diferentes obtenidos del servidor.
- **JSON (JavaScript Object Notation)** — *Como un formulario estructurado en papel:* Un formato de texto ligero y estandarizado mediante llaves `{}` y corchetes `[]` para intercambiar información entre la App y la API.

---

## 🗺️ Mapa del proyecto

Estructura de archivos recomendada en Android Studio para mantener una separación limpia de responsabilidades:

```
app/
├── src/
│   └── main/
│       ├── AndroidManifest.xml          <-- Permisos de red e Internet
│       ├── java/com/ejemplo/citasmedicas/
│       │   ├── model/
│       │   │   └── Singleton.kt         <-- Estado global y caché de datos
│       │   ├── network/
│       │   │   └── VolleySingleton.kt   <-- Cliente HTTP desacoplado
│       │   └── ui/
│       │       ├── LoginActivity.kt     <-- Pantalla de inicio de sesión
│       │       └── CitasActivity.kt     <-- Lista y consulta de citas/especialidades
│       └── res/
│           └── layout/
│               ├── activity_login.xml   <-- Layout de inicio de sesión
│               ├── activity_citas.xml   <-- Layout principal con contenedor lyt_lista
│               └── item_especialidad.xml<-- Plantilla para inflado dinámico
```

---

## 🔨 Paso a paso

---

### Paso 1: Configurar permisos de red en `AndroidManifest.xml`

**🤔 ¿Por qué este paso?**
Por seguridad, las aplicaciones de Android nacen completamente aisladas y no pueden acceder a Internet por defecto. Debes declarar explícitamente el permiso de red. Además, si estás probando tu servidor Laravel en un entorno local de desarrollo (HTTP sin SSL), Android bloqueará el tráfico no cifrado a menos que lo autorices.

**🛠️ ¿Cómo?**
Añade el permiso `android.permission.INTERNET` fuera de la etiqueta `<application>` y habilita `android:usesCleartextTraffic="true"` dentro de la etiqueta `<application>`.

**Código de referencia:**

```xml
<!-- AndroidManifest.xml -->
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns:android="http://schemas.android.com/apk/res/android"
    package="com.ejemplo.citasmedicas">

    <!-- Permiso obligatorio para realizar peticiones HTTP/HTTPS -->
    <uses-permission android:name="android.permission.INTERNET" />

    <application
        android:allowBackup="true"
        android:icon="@mipmap/ic_launcher"
        android:label="@string/app_name"
        android:roundIcon="@mipmap/ic_launcher_round"
        android:supportsRtl="true"
        android:theme="@style/Theme.AgendaMedica"
        android:usesCleartextTraffic="true"> <!-- Permite HTTP local (10.0.2.2 / IP local) -->

        <activity
            android:name=".ui.LoginActivity"
            android:exported="true">
            <intent-filter>
                <action android:name="android.intent.action.MAIN" />
                <category android:name="android.intent.category.LAUNCHER" />
            </intent-filter>
        </activity>
        
        <activity android:name=".ui.CitasActivity" />

    </application>

</manifest>
```

> 💡 **Qué hace este fragmento:** Habilita el chip de comunicación de red del dispositivo para la App y otorga permiso para conectarse a servidores locales de desarrollo HTTP sin certificados SSL obligatorios.

> ⚠️ **Error común:** Olvidar `usesCleartextTraffic="true"`. Al probar con `http://10.0.2.2:8000` o IPs locales, Android lanzará una excepción `java.io.IOException: Cleartext HTTP traffic to 10.0.2.2 not permitted`.

---

### Paso 2: Crear el Cliente de Red `VolleySingleton.kt`

**🤔 ¿Por me este paso?**
Crear una nueva cola de peticiones HTTP (`RequestQueue`) cada vez que el usuario presiona un botón consume memoria de forma excesiva y puede provocar fugas de memoria (memory leaks). Un Singleton garantiza que toda la App reutilice una única cola de red y un sistema de caché de imágenes eficiente.

**🛠️ ¿Cómo?**
Crea la clase `VolleySingleton` implementando el patrón Singleton con la anotación `@Volatile` para garantizar seguridad entre hilos (*thread-safety*).

**Código de referencia:**

```kotlin
// network/VolleySingleton.kt
package com.ejemplo.citasmedicas.network

import android.content.Context
import android.graphics.Bitmap
import androidx.collection.LruCache
import com.android.volley.RequestQueue
import com.android.volley.toolbox.ImageLoader
import com.android.volley.toolbox.Volley

class VolleySingleton private constructor(context: Context) {

    // Cola única de peticiones de Volley para toda la aplicación
    val requestQueue: RequestQueue = Volley.newRequestQueue(context.applicationContext)

    // Cargador de imágenes con caché en memoria LRU (Least Recently Used)
    val imageLoader: ImageLoader = ImageLoader(requestQueue, object : ImageLoader.ImageCache {
        private val cache = LruCache<String, Bitmap>(20) // Guarda hasta 20 imágenes

        override fun getBitmap(url: String): Bitmap? = cache.get(url)

        override fun putBitmap(url: String, bitmap: Bitmap) {
            cache.put(url, bitmap)
        }
    })

    companion object {
        @Volatile
        private var INSTANCE: VolleySingleton? = null

        // Obtiene la instancia única de forma segura entre hilos concurrentes
        fun getInstance(context: Context): VolleySingleton =
            INSTANCE ?: synchronized(this) {
                INSTANCE ?: VolleySingleton(context).also { INSTANCE = it }
            }
    }
}
```

> 💡 **Qué hace este fragmento:** Administra de forma centralizada todas las llamadas HTTP y descargas de imágenes de la app utilizando un diseño Singleton seguro y optimizado con caché LRU.

> ⚠️ **Error común:** Pasar el `Context` de una `Activity` a `Volley.newRequestQueue(context)` en lugar de `context.applicationContext`. Esto evita que la memoria de la Activity sea liberada cuando se destruye.

---

### Paso 3: Crear el Singleton de Estado Global `Singleton.kt`

**🤔 ¿Por qué este paso?**
Cuando el backend responde a un inicio de sesión exitoso, entrega un token de acceso (Bearer Token) y datos del usuario. Necesitamos un almacén global accesible desde cualquier pantalla para consultar ese token, guardar respuestas de la API y centralizar validaciones de datos (Regex).

**🛠️ ¿Cómo?**
Crea un objeto Kotlin `object Singleton` con variables globales para el token, usuario, respuestas y un arreglo de expresiones regulares para validar formularios.

**Código de referencia:**

```kotlin
// model/Singleton.kt
package com.ejemplo.citasmedicas.model

object Singleton {
    // URL Base para conexión con la API Laravel
    // Nota: 10.0.2.2 apunta al localhost de la máquina host desde el emulador Android
    const val BASE_URL: String = "http://10.0.2.2:8000/api"

    // Datos de sesión del usuario autenticado
    var token_acceso: String? = null
    var usuario_actual: String = ""
    var rol_usuario: String = ""
    
    // Almacenamiento temporal de respuestas API para transferencia entre pantallas
    var response_api: String? = null

    // Listas estandarizadas de validación por Expresiones Regulares (Regex)
    val arrayListValidaciones: ArrayList<String> = ArrayList()
    val arrayListMensajes: ArrayList<String> = ArrayList()

    init {
        // [0] Formato válido de correo electrónico
        arrayListValidaciones.add("^[A-Za-z0-9+_.-]+@[A-Za-z0-9_.-]+$")
        // [1] Contraseña de mínimo 6 caracteres
        arrayListValidaciones.add("^.{6,}$")

        // Mensajes de error reutilizables
        arrayListMensajes.add("Los campos no pueden estar vacíos")
        arrayListMensajes.add("El correo electrónico no tiene un formato válido")
        arrayListMensajes.add("La contraseña debe tener al menos 6 caracteres")
    }
}
```

> 💡 **Qué hace este fragmento:** Funciona como la memoria global de la App, conservando la URL base de la API, el token de sesión Bearer del paciente y reglas de validación en Regex.

> ⚠️ **Error común:** Usar `localhost` o `127.0.0.1` dentro del emulador Android. El emulador considera `localhost` a sí mismo; para conectarse a tu computadora debes usar `10.0.2.2`.

---

### Paso 4: Diseñar el Layout XML de Inicio de Sesión (`activity_login.xml`)

**🤔 ¿Por qué este paso?**
La interfaz visual define dónde ingresa los datos el usuario. Siguiendo las convenciones de nombrado del proyecto, asignaremos prefijos claros a los IDs (`etx_`, `btn_`, `lyt_`, `txt_`) para mantener un código limpio y legible.

**🛠️ ¿Cómo?**
Crea el archivo `res/layout/activity_login.xml` utilizando elementos `EditText`, `Button` y contenedores `LinearLayout`.

**Código de referencia:**

```xml
<!-- res/layout/activity_login.xml -->
<?xml version="1.0" encoding="utf-8"?>
<LinearLayout xmlns:android="http://schemas.android.com/apk/res/android"
    android:id="@+id/lyt_contenedor_login"
    android:layout_width="match_parent"
    android:layout_height="match_parent"
    android:orientation="vertical"
    android:padding="24dp"
    android:gravity="center">

    <TextView
        android:id="@+id/txt_titulo_login"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="Agenda Médica - Iniciar Sesión"
        android:textSize="22sp"
        android:textStyle="bold"
        android:layout_marginBottom="32dp" />

    <EditText
        android:id="@+id/etx_email"
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:hint="Correo Electrónico"
        android:inputType="textEmailAddress"
        android:layout_marginBottom="16dp" />

    <EditText
        android:id="@+id/etx_password"
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:hint="Contraseña"
        android:inputType="textPassword"
        android:layout_marginBottom="24dp" />

    <Button
        android:id="@+id/btn_login"
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:text="Ingresar" />

</LinearLayout>
```

> 💡 **Qué hace este fragmento:** Define la interfaz gráfica del Login con campos estandarizados para correo (`etx_email`), contraseña (`etx_password`) y el botón de acción (`btn_login`).

> ⚠️ **Error común:** Nombrar IDs con nombres genéricos como `editText1` o `button2`. Usar prefijos obligatorios (`etx_`, `btn_`) previene confusión en proyectos reales.

---

### Paso 5: Implementar la Lógica de Autenticación en `LoginActivity.kt`

**🤔 ¿Por qué este paso?**
Esta actividad captura lo que el usuario escribe, valida las entradas localmente usando las Regex del `Singleton`, envía una petición `POST` al endpoint `/api/login` de Laravel, procesa el JSON de respuesta y guarda el token Sanctu/Bearer para futuras peticiones.

**🛠️ ¿Cómo?**
Declara las vistas con `lateinit var`, vincúlalas en `onCreate()`, implementa la petición `JsonObjectRequest` con Volley y navega hacia `CitasActivity`.

**Código de referencia:**

```kotlin
// ui/LoginActivity.kt
package com.ejemplo.citasmedicas.ui

import android.content.Intent
import android.os.Bundle
import android.util.Log
import android.widget.Button
import android.widget.EditText
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.ejemplo.citasmedicas.R
import com.ejemplo.citasmedicas.model.Singleton
import com.ejemplo.citasmedicas.network.VolleySingleton
import org.json.JSONObject

// Declaración de variables de vista a nivel de clase usando lateinit var
lateinit var etx_email: EditText
lateinit var etx_password: EditText
lateinit var btn_login: Button

class LoginActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        // Inicialización de componentes visuales por ID
        etx_email = findViewById(R.id.etx_email)
        etx_password = findViewById(R.id.etx_password)
        btn_login = findViewById(R.id.btn_login)

        btn_login.setOnClickListener {
            val email = etx_email.text.toString().trim()
            val password = etx_password.text.toString().trim()

            if (validarCampos(email, password)) {
                ejecutarLoginAPI(email, password)
            }
        }
    }

    private fun validarCampos(email: String, pass: String): Boolean {
        if (email.isEmpty() || pass.isEmpty()) {
            Toast.makeText(this, Singleton.arrayListMensajes[0], Toast.LENGTH_SHORT).show()
            return false
        }
        val regexEmail = Regex(Singleton.arrayListValidaciones[0])
        if (!regexEmail.matches(email)) {
            Toast.makeText(this, Singleton.arrayListMensajes[1], Toast.LENGTH_SHORT).show()
            return false
        }
        return true
    }

    private fun ejecutarLoginAPI(email: String, pass: String) {
        val url = "${Singleton.BASE_URL}/login"

        // Creación del cuerpo de la petición en formato JSON
        val bodyParams = JSONObject().apply {
            put("email", email)
            put("password", pass)
        }

        val request = JsonObjectRequest(
            Request.Method.POST,
            url,
            bodyParams,
            { response ->
                Log.d("API_LOGIN", "Respuesta recibida: $response")
                // Extracción de datos entregados por la API Laravel
                val token = response.optString("token")
                val usuarioObj = response.optJSONObject("usuario")
                
                Singleton.token_acceso = token
                Singleton.usuario_actual = usuarioObj?.optString("nombre") ?: "Paciente"

                Toast.makeText(this, "Bienvenido, ${Singleton.usuario_actual}", Toast.LENGTH_SHORT).show()

                // Navegación a la pantalla de Citas
                val intent = Intent(this, CitasActivity::class.java)
                startActivity(intent)
                finish()
            },
            { error ->
                Log.e("API_LOGIN", "Error en login: ${error.message}")
                Toast.makeText(this, "Credenciales incorrectas o error de conexión", Toast.LENGTH_LONG).show()
            }
        )

        // Envío de la petición a través del Singleton de Red
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }
}
```

> 💡 **Qué hace este fragmento:** Contrata las vistas con `lateinit var`, valida el formulario localmente y envía credenciales mediante HTTP POST a la API REST, almacenando el token retornado en el `Singleton`.

> ⚠️ **Error común:** Olvidar agregar la petición a la cola con `VolleySingleton.getInstance(this).requestQueue.add(request)`. Si no ejecutas `.add()`, la petición jamás sale del dispositivo.

---

### Paso 6: Consumir Endpoints Protegidos e Inflar Vistas Dinámicamente en `CitasActivity.kt`

**🤔 ¿Por qué este paso?**
Una vez autenticado, el usuario necesita ver información obtenida de la base de datos (como la lista de especialidades médicas o citas agendadas). Puesto que la API devuelve una lista dinámica de objetos JSON, inflaremos dinámicamente layouts XML en un contenedor `LinearLayout` (`lyt_lista`).

**🛠️ ¿Cómo?**
Crea `activity_citas.xml` con un contenedor `lyt_lista`, un ítem plantilla `item_especialidad.xml`, y programa la petición en `CitasActivity.kt` incluyendo el encabezado `Authorization: Bearer <TOKEN>`.

**Código de referencia — Layout Plantilla (`item_especialidad.xml`):**

```xml
<!-- res/layout/item_especialidad.xml -->
<?xml version="1.0" encoding="utf-8"?>
<LinearLayout xmlns:android="http://schemas.android.com/apk/res/android"
    android:id="@+id/lyt_item_especialidad"
    android:layout_width="match_parent"
    android:layout_height="wrap_content"
    android:orientation="horizontal"
    android:padding="16dp"
    android:layout_marginBottom="8dp"
    android:background="#F0F4F8">

    <TextView
        android:id="@+id/txt_nombre_especialidad"
        android:layout_width="0dp"
        android:layout_height="wrap_content"
        android:layout_weight="1"
        android:textSize="16sp"
        android:textStyle="bold"
        android:text="Nombre de Especialidad" />

    <TextView
        android:id="@+id/txt_estado_especialidad"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:textColor="#2E7D32"
        android:text="Disponible" />

</LinearLayout>
```

> 💡 **Qué hace este fragmento:** Es la plantilla visual individual que representa una fila de la lista y que será clonada dinámicamente mediante código Kotlin por cada elemento recibido en el JSON.

**Código de referencia — Actividad Principal (`CitasActivity.kt`):**

```kotlin
// ui/CitasActivity.kt
package com.ejemplo.citasmedicas.ui

import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.android.volley.Request
import com.android.volley.toolbox.JsonArrayRequest
import com.ejemplo.citasmedicas.R
import com.ejemplo.citasmedicas.model.Singleton
import com.ejemplo.citasmedicas.network.VolleySingleton

lateinit var lyt_lista: LinearLayout
lateinit var txt_bienvenida: TextView

class CitasActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_citas)

        lyt_lista = findViewById(R.id.lyt_lista)
        txt_bienvenida = findViewById(R.id.txt_bienvenida)

        txt_bienvenida.text = "Paciente: ${Singleton.usuario_actual}"

        cargarEspecialidadesAPI()
    }

    private fun cargarEspecialidadesAPI() {
        val url = "${Singleton.BASE_URL}/especialidades"

        // JsonArrayRequest espera una respuesta en formato arreglo JSON [...]
        val request = object : JsonArrayRequest(
            Request.Method.GET,
            url,
            null,
            { responseArray ->
                Log.d("API_CITAS", "Respuesta lista: $responseArray")
                // Guardar la respuesta cruda en el Singleton si se desea reutilizar
                Singleton.response_api = responseArray.toString()
                
                // Limpiar contenedor antes de inflar nuevos elementos
                lyt_lista.removeAllViews()

                // Recorrer el arreglo JSON entregado por la API Laravel
                for (i in 0 until responseArray.length()) {
                    val itemObject = responseArray.getJSONObject(i)

                    // Inflar plantilla XML item_especialidad
                    val inflater = LayoutInflater.from(this)
                    val elementView = inflater.inflate(
                        R.layout.item_especialidad,
                        lyt_lista,
                        false
                    ) as LinearLayout

                    // Asignar datos de los campos JSON a los TextViews del ítem
                    val txtNombre = elementView.findViewById<TextView>(R.id.txt_nombre_especialidad)
                    val txtEstado = elementView.findViewById<TextView>(R.id.txt_estado_especialidad)

                    txtNombre.text = itemObject.optString("nombre", "Sin nombre")
                    val activo = itemObject.optBoolean("activo", true)
                    txtEstado.text = if (activo) "Disponible" else "No disponible"

                    // Agregar la vista inflada al contenedor lineal principal
                    lyt_lista.addView(elementView)
                }
            },
            { error ->
                Log.e("API_CITAS", "Error al cargar espec: ${error.message}")
                Toast.makeText(this, "Error al obtener datos del servidor", Toast.LENGTH_SHORT).show()
            }
        ) {
            // Sobrescribir getHeaders para enviar el Token de autenticación Bearer
            override fun getHeaders(): MutableMap<String, String> {
                val headers = HashMap<String, String>()
                headers["Content-Type"] = "application/json"
                headers["Accept"] = "application/json"
                Singleton.token_acceso?.let { token ->
                    headers["Authorization"] = "Bearer $token"
                }
                return headers
            }
        }

        VolleySingleton.getInstance(this).requestQueue.add(request)
    }
}
```

> 💡 **Qué hace este fragmento:** Realiza un GET autenticado enviando el Token Bearer en las cabeceras HTTP, lee el arreglo JSON devuelto por Laravel e infla dinámicamente un elemento en `lyt_lista` por cada objeto.

> ⚠️ **Error común:** No limpiar la lista con `lyt_lista.removeAllViews()` antes de inflar. Si la función se llama varias veces, los elementos se duplicarán infinitamente en la interfaz.

---

## 🔍 Preguntas de comprensión

1. **¿Por qué utilizamos la IP `10.0.2.2` en lugar de `127.0.0.1` o `localhost` al realizar pruebas desde el emulador de Android?**
2. **¿Cuál es la función del método `LayoutInflater.from(context).inflate(...)` y por qué es fundamental al recibir arreglos de datos en JSON?**
3. **¿Por qué las cabeceras de autorización HTTP requieren la palabra clave `Bearer` seguida del Token devuelto por Laravel Sanctum?**
4. **¿Por qué declaramos las referencias de las vistas usando `lateinit var` a nivel de clase en lugar de declararlas dentro del método `onCreate()`?**

---

## ✅ Cómo saber que funciona

1. **Prueba de Autenticación Exitosa:**
   - Ingresa un correo y contraseña válidos registrados en la base de datos de Laravel.
   - Al presionar **Ingresar**, la app debe mostrar un mensaje `Toast` de bienvenida, guardar el Token en `Singleton.token_acceso` y cambiar a la pantalla `CitasActivity`.

2. **Prueba de Inflado Dinámico:**
   - La pantalla `CitasActivity` debe consultar la base de datos mediante la API y mostrar la lista de especialidades médicas (Cardiología, Pediatría, etc.) ordenadas verticalmente dentro del contenedor `lyt_lista`.

3. **Verificación en Logs:**
   - En la pestaña **Logcat** de Android Studio, filtra por `API_LOGIN` o `API_CITAS` para verificar el código de respuesta `200 OK` y el cuerpo JSON recibido.

---

## 🚀 Reto extra (opcional)

Implementa una función `agendarCita(idDoctor: Int, fechaHora: String)` en `CitasActivity.kt` que envíe una petición `POST` al endpoint `/api/citas` incluyendo el ID del doctor seleccionado y la fecha en el cuerpo JSON, capturando el error en caso de que el horario ya esté bloqueado o reservado por otro paciente.

---

## 📚 Para profundizar (opcional)

- **Laravel Sanctum Authentication** — Sistema de emisión e inspección de tokens API ligeros para aplicaciones móviles en Laravel.
- **Volley Custom Requests & Headers** — Personalización de clases heredadas de `Request<T>` para manejar respuestas JSON complejas y manejo automático de reintentos (*RetryPolicy*).
- **Patrón Repository en Android** — Separación de la capa de acceso a datos de la capa visual en Kotlin para migrar de Volley a Retrofit o Room de forma transparente.
