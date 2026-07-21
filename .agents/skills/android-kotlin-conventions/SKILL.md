---
name: android-kotlin-conventions
description: Directivas y convenciones de desarrollo para proyectos Android Kotlin (estilo LoginCalculadora y 3er unidad). Define patrones de declaración de variables lateinit, nombrado de IDs de vistas (etx_, btn_, lyt_, etc.), uso de Singletons (compartidos y de red/Volley), consumo de APIs backend con JSON y dinámicos de UI.
---

# Guía de Estilo y Convenciones Android Kotlin (`LoginCalculadora` & `3er unidad`)

Esta Skill establece las normas obligatorias de desarrollo para proyectos Android en Kotlin. **Toda la IA debe adherirse estrictamente a este comportamiento y esquema de declaraciones al crear o modificar actividades, layouts XML, singletons y consumo de APIs.**

---

## 1. Convención Estricta de Nombres de IDs de Vistas (XML & Kotlin)

Todos los IDs de componentes visuales en archivos de layout XML (`res/layout/`) y sus correspondientes referencias en Kotlin deben seguir los siguientes prefijos descriptivos en snake_case:

| Tipo de Componente / Vista | Prefijo Obligatorio | Ejemplo de ID |
| :--- | :--- | :--- |
| `EditText` | `etx_` | `etx_usuario`, `etx_nombre`, `etx_edad`, `etx_rfc`, `etx_fecha`, `etx_telefono` |
| `TextInputEditText` | `input_etx_` | `input_etx_username`, `input_etx_password` |
| `TextInputLayout` | `input_layout_` | `input_layout_username`, `input_layout_password` |
| `Button` / `ImageButton` | `btn_` | `btn_login`, `btn_enviar`, `btn_1`, `btn_2` |
| `TextView` | `txt_` o `[entidad]_[campo]` | `txt_titulo`, `video_titulo`, `lista_nombre`, `lista_url` |
| `Spinner` | `spr_` | `spr_lenguajes`, `spr_ciudades` |
| `LinearLayout` / Contenedores | `lyt_` | `lyt_lista`, `lyt_lista2`, `lyt_contenedor` |
| `ImageView` / `NetworkImageView` | `img_` o `[entidad]_img` | `img_perfil`, `video_img`, `video_canal_img` |

---

## 2. Declaración de Variables de Vistas (`lateinit var`) y Enlace

* **Ubicación de Declaración:** Las referencias a vistas se declaran como `lateinit var` a nivel de archivo o de clase (Activity), fuera del método `onCreate()`.
* **Inicialización:** Se realiza dentro de `onCreate()` usando `findViewById(R.id.[id])`.

```kotlin
// Declaración de variables a nivel top-level o Activity
lateinit var etx_usuario: EditText
lateinit var etx_password: EditText
lateinit var btn_login: Button
lateinit var lyt_lista: LinearLayout
lateinit var spr_lenguajes: Spinner

class LoginActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        // Inicialización en onCreate
        etx_usuario = findViewById(R.id.etx_usuario)
        etx_password = findViewById(R.id.etx_password)
        btn_login = findViewById(R.id.btn_login)
        lyt_lista = findViewById(R.id.lyt_lista)
        spr_lenguajes = findViewById(R.id.spr_lenguajes)
        
        btn_login.setOnClickListener {
            val usuario = etx_usuario.text.toString().trim()
            val password = etx_password.text.toString().trim()
            // Lógica de validación
        }
    }
}
```

---

## 3. Patrón Singleton (`object Singleton` y Singletons de Red/Volley)

### 3.1. Singleton de Estado Global (`object Singleton`)
Utilizado para compartir datos entre actividades, almacenar respuestas de red en memoria, patrones de expresión regular (Regex) para validaciones y constantes de la aplicación.

```kotlin
package com.example.app

object Singleton {
    // Datos globales transferibles entre actividades
    var usuario_actual: String = ""
    var response_api: String? = null

    // Arrays para Spinners o Adaptadores
    val array_lenguajes = listOf("Java", "Kotlin", "React", "Springboot")

    // Listas de validaciones (Regex) y mensajes de alerta
    var arrayListValidaciones: ArrayList<String> = ArrayList()
    var arrayListMensajes: ArrayList<String> = ArrayList()

    init {
        // Inicialización de patrones Regex
        arrayListValidaciones.add("^[A-Za-z0-9+_.-]+@[A-Za-z0-9_.-]+$") // Email [0]
        arrayListValidaciones.add("^1?[0-9]{2}$")                          // Edad [1]
        arrayListValidaciones.add("^[A-Z]{4}[0-9]{6}[A-Z0-9]{3}$")        // RFC [2]
        arrayListValidaciones.add("^871[0-9]{7}$")                         // Teléfono [3]

        // Mensajes de alerta estandarizados
        arrayListMensajes.add("Los campos no pueden estar vacíos")
        arrayListMensajes.add("El formato del correo no es válido")
    }
}
```

### 3.2. Singleton de Servicio / Cliente HTTP (`VolleySingleton`)
Para peticiones a APIs y carga eficiente de imágenes mediante caché LRU y patrón `@Volatile` seguro entre hilos:

```kotlin
package com.example.app

import android.content.Context
import android.graphics.Bitmap
import androidx.collection.LruCache
import com.android.volley.RequestQueue
import com.android.volley.toolbox.ImageLoader
import com.android.volley.toolbox.Volley

class VolleySingleton private constructor(context: Context) {

    val requestQueue: RequestQueue = Volley.newRequestQueue(context.applicationContext)

    val imageLoader: ImageLoader = ImageLoader(requestQueue, object : ImageLoader.ImageCache {
        private val cache = LruCache<String, Bitmap>(20)

        override fun getBitmap(url: String): Bitmap? = cache.get(url)

        override fun putBitmap(url: String, bitmap: Bitmap) {
            cache.put(url, bitmap)
        }
    })

    companion object {
        @Volatile
        private var INSTANCE: VolleySingleton? = null

        fun getInstance(context: Context): VolleySingleton =
            INSTANCE ?: synchronized(this) {
                INSTANCE ?: VolleySingleton(context).also { INSTANCE = it }
            }
    }
}
```

---

## 4. Consumo de API REST, Manejo de Datos y Navegación

1. **Llamadas Asíncronas a Backend:**
   * Las peticiones se realizan usando el cliente HTTP/Volley.
   * La respuesta recibida (cadena JSON) se almacena temporalmente en `Singleton.response` si requiere ser consultada en otra actividad.
   * Se inicia la actividad de destino mediante `Intent`.

```kotlin
fun cargarServicio() {
    val url = "https://api.ejemplo.com/datos"
    val queue = Volley.newRequestQueue(this)

    val stringRequest = StringRequest(
        Request.Method.GET,
        url,
        { response ->
            Log.v("TAG", "Respuesta: $response")
            Singleton.response_api = response
            startActivity(Intent(this, DetalleActivity::class.java))
        },
        { error ->
            Log.e("TAG", "Error en la petición: ${error.message}")
        }
    )
    queue.add(stringRequest)
}
```

---

## 5. Inflado Dinámico de Vistas desde JSON

Para listas dinámicas agregadas dentro de contenedores `LinearLayout` (`lyt_lista`):

1. Parsear el string JSON con `JSONObject`.
2. Recorrer el arreglo usando `for (x in 0 until jsonArray.length())`.
3. Inflar la vista del elemento con `LayoutInflater.from(this).inflate(...)`.
4. Obtener las sub-vistas, asignar los valores y agregar al contenedor `lyt_lista.addView(list_element)`.

```kotlin
fun cargarLista() {
    val jsonString = Singleton.response_api ?: return
    val jsonObject = JSONObject(jsonString)
    val jsonArray = jsonObject.getJSONArray("data")

    for (x in 0 until jsonArray.length()) {
        val itemObject = jsonArray.getJSONObject(x)
        val layoutInflater = LayoutInflater.from(this)

        val list_element = layoutInflater.inflate(
            R.layout.item_detalle,
            null,
            false
        ) as LinearLayout

        val txt_titulo = list_element.findViewById<TextView>(R.id.txt_titulo)
        txt_titulo.text = itemObject.getString("nombre")

        val img_icono = list_element.findViewById<NetworkImageView>(R.id.img_icono)
        val imageUrl = itemObject.getString("url_imagen")
        val imageLoader = VolleySingleton.getInstance(this).imageLoader
        img_icono.setImageUrl(imageUrl, imageLoader)

        lyt_lista.addView(list_element)
    }
}
```

---

## 6. Reglas Inviolables para Asistentes de IA

1. **DECLARACIONES `lateinit var`:** Respetar la declaración de referencias a vistas visuales con `lateinit var` en el scope correspondiente del archivo o clase.
2. **NOMBRES DE IDs CON PREFIJO SINTÁCTICO:** Cumplir siempre los prefijos obligatorios (`etx_`, `input_etx_`, `btn_`, `txt_`, `spr_`, `lyt_`, `img_`). Nunca usar IDs genéricos como `editText1` o `button2`.
3. **PATRÓN SINGLETON:** Utilizar `object Singleton` para almacenar estados globales compartidos, Regex y listas de mensajes. Usar `VolleySingleton` con thread-safety para servicios de red.
4. **INFLADO DINÁMICO DE LAYOUTS:** Cuando no se use RecyclerView clásico, implementar inflado manual con `LayoutInflater` sobre `LinearLayout` (`lyt_...`) manteniendo la estructura documentada.
