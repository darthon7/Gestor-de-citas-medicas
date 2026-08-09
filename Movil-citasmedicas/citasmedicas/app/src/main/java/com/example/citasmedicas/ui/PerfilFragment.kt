package com.example.citasmedicas.ui

import android.app.AlertDialog
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.fragment.app.Fragment
import com.android.volley.Request
import com.android.volley.Response
import com.android.volley.toolbox.JsonObjectRequest
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Singleton
import com.example.citasmedicas.network.VolleySingleton
import com.google.android.material.textfield.TextInputEditText
import com.google.android.material.textfield.TextInputLayout
import org.json.JSONObject
import java.io.ByteArrayOutputStream

class PerfilFragment : Fragment(R.layout.fragment_perfil) {

    private lateinit var imgFoto: ImageView
    private lateinit var txtCambiarFoto: TextView
    private lateinit var txtNombre: TextView
    private lateinit var txtCurp: TextView
    private lateinit var txtFechaNacimiento: TextView
    private lateinit var txtCorreo: TextView
    private lateinit var etTelefono: TextInputEditText
    private lateinit var btnGuardarTelefono: View
    private lateinit var txtTotalCitas: TextView
    private lateinit var txtProximaCita: TextView
    private lateinit var btnCambiarPassword: View
    private lateinit var btnCerrarSesion: View
    private lateinit var progressBar: ProgressBar
    lateinit var input_cambiartelefono: TextInputLayout

    private var fotoUriSeleccionada: Uri? = null

    private val selectorImagen = registerForActivityResult(ActivityResultContracts.GetContent()) { uri ->
        if (uri != null) {
            fotoUriSeleccionada = uri
            imgFoto.setImageURI(uri)
            subirFoto(uri)
        }
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        imgFoto = view.findViewById(R.id.img_foto_perfil)
        txtCambiarFoto = view.findViewById(R.id.txt_cambiar_foto)
        txtNombre = view.findViewById(R.id.txt_nombre_perfil)
        txtCurp = view.findViewById(R.id.txt_curp_perfil)
        txtFechaNacimiento = view.findViewById(R.id.txt_fecha_nacimiento_perfil)
        txtCorreo = view.findViewById(R.id.txt_correo_perfil)
        etTelefono = view.findViewById(R.id.et_telefono_perfil)
        btnGuardarTelefono = view.findViewById(R.id.btn_guardar_telefono)
        txtTotalCitas = view.findViewById(R.id.txt_total_citas)
        txtProximaCita = view.findViewById(R.id.txt_proxima_cita)
        btnCambiarPassword = view.findViewById(R.id.btn_cambiar_password)
        btnCerrarSesion = view.findViewById(R.id.btn_cerrar_sesion)
        progressBar = view.findViewById(R.id.progress_perfil)
        input_cambiartelefono=view.findViewById(R.id.input_cambiartelefono)

        txtCambiarFoto.setOnClickListener { selectorImagen.launch("image/*") }
        btnGuardarTelefono.setOnClickListener { guardarTelefono() }
        btnCambiarPassword.setOnClickListener { mostrarDialogoPassword() }
        btnCerrarSesion.setOnClickListener { confirmarCerrarSesion() }

    }

    override fun onResume() {
        super.onResume()
        cargarPerfil()
        cargarResumenActividad()
    }

    private fun cargarPerfil() {
        val url = "${Singleton.BASE_URL}/miPerfil"
        val request = object : JsonObjectRequest(
            Request.Method.GET, url, null,
            JsonObjectRequest@{ response ->
                if (!isAdded) return@JsonObjectRequest
                val data = response.getJSONObject("data")
                txtNombre.text = data.getString("nombre")
                txtCurp.text = data.getString("curp")
                txtCorreo.text = data.getString("email")
                etTelefono.setText(data.optString("telefono", ""))
                val fotorelativa = if (!data.isNull("foto_perfil")) data.optString("foto_perfil", "") else null
                Singleton.foto_perfil = fotorelativa
                val urlcompleta = Singleton.obtenerfoto(fotorelativa)
                if (urlcompleta.isNotEmpty()) {
                    VolleySingleton.getInstance(requireContext()).imageLoader.get(
                        urlcompleta,
                        com.android.volley.toolbox.ImageLoader.getImageListener(
                            imgFoto,
                            R.drawable.baseline_person_outline_24,
                            R.drawable.baseline_error_outline_24
                        )
                    )
                }
                if (data.has("perfil_paciente") && !data.isNull("perfil_paciente")) {
                    val perfilPaciente = data.getJSONObject("perfil_paciente")
                    val fechaCompleta = perfilPaciente.optString("fecha_nacimiento", "")
                    if (fechaCompleta.length >= 10) {
                        txtFechaNacimiento.text = fechaCompleta.substring(0, 10)
                    } else {
                        txtFechaNacimiento.text = "-"
                    }
                }
            },
            {
                if (isAdded) {
                    Toast.makeText(requireContext(), "Error al cargar el perfil", Toast.LENGTH_SHORT).show()
                }
            }
        ) {
            override fun getHeaders(): MutableMap<String, String> {
                val headers = HashMap<String, String>()
                headers["Authorization"] = "Bearer ${Singleton.token_actual}"
                headers["Accept"] = "application/json"
                return headers
            }
        }
        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            10000,
            2,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )
        VolleySingleton.getInstance(requireContext()).requestQueue.add(request)
    }

    private fun cargarResumenActividad() {
        val url = "${Singleton.BASE_URL}/misCitas"
        val request = object : JsonObjectRequest(
            Request.Method.GET, url, null,
            JsonObjectRequest@{ response ->
                if (!isAdded) return@JsonObjectRequest
                val dataObjeto = response.getJSONObject("data")
                val data = dataObjeto.getJSONArray("data")

                var totalCompletadas = 0
                var proximaFecha: String? = null
                var proximaHora: String? = null
                var proximaDoctor: String? = null

                for (i in 0 until data.length()) {
                    val citaJson = data.getJSONObject(i)
                    val estado = citaJson.getString("estado")
                    if (estado == "completada") {
                        totalCompletadas++
                    }
                    if (estado == "agendada" || estado == "confirmada") {
                        val fecha = citaJson.getString("fecha_cita").substring(0, 10)
                        val hora = citaJson.getString("hora_cita")
                        if (proximaFecha == null || fecha < proximaFecha!! || (fecha == proximaFecha && hora < proximaHora!!)) {
                            proximaFecha = fecha
                            proximaHora = hora
                            val perfilDoctor = citaJson.optJSONObject("perfil_doctor")
                            val usuarioDoctor = perfilDoctor?.optJSONObject("usuario")
                            proximaDoctor =usuarioDoctor?.optString("nombre","Doctor")?:"Doctor"
                        }
                    }
                }

                txtTotalCitas.text = "Citas realizadas: $totalCompletadas"
                txtProximaCita.text = if (proximaFecha != null) {
                    "Proxima cita: $proximaDoctor - $proximaFecha ${proximaHora?.substring(0, 5)}"
                } else {
                    "Proxima cita: no tienes citas agendadas"
                }
            },
            {
                if (isAdded) {
                    txtTotalCitas.text = "Citas realizadas: -"
                    txtProximaCita.text = "Proxima cita: -"
                }
            }
        ) {
            override fun getHeaders(): MutableMap<String, String> {
                val headers = HashMap<String, String>()
                headers["Authorization"] = "Bearer ${Singleton.token_actual}"
                headers["Accept"] = "application/json"
                return headers
            }
        }
        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            10000,
            2,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )
        VolleySingleton.getInstance(requireContext()).requestQueue.add(request)
    }

    private fun guardarTelefono() {
        val telefono = etTelefono.text.toString().trim()
        input_cambiartelefono.error=null
        if (telefono.isEmpty()) {
            input_cambiartelefono.error= Singleton.arraylist_mensajes[0]
            return
        }
        else if (telefono.length<10){
            input_cambiartelefono.error= Singleton.arraylist_mensajes[5]
            return
        }
        val url = "${Singleton.BASE_URL}/actualizarMiPerfil"
        val body = JSONObject()
        body.put("telefono", telefono)

        progressBar.visibility = View.VISIBLE
        val request = object : JsonObjectRequest(
            Request.Method.PUT, url, body,
            { response ->
                progressBar.visibility = View.GONE
                if (isAdded) {
                    Toast.makeText(requireContext(), response.optString("mensaje", "Telefono actualizado"), Toast.LENGTH_SHORT).show()
                }
            },
            { error ->
                progressBar.visibility = View.GONE
                if (isAdded) {
                    Toast.makeText(requireContext(), "No se pudo actualizar el telefono", Toast.LENGTH_LONG).show()
                }
            }
        ) {
            override fun getHeaders(): MutableMap<String, String> {
                val headers = HashMap<String, String>()
                headers["Authorization"] = "Bearer ${Singleton.token_actual}"
                headers["Accept"] = "application/json"
                return headers
            }
        }
        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            10000,
            2,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )
        VolleySingleton.getInstance(requireContext()).requestQueue.add(request)
    }

    private fun mostrarDialogoPassword() {
        val layout = LinearLayout(requireContext())
        layout.orientation = LinearLayout.VERTICAL
        layout.setPadding(50, 20, 50, 20)

        val inputActual = EditText(requireContext())
        inputActual.hint = "Contraseña actual"
        inputActual.inputType = android.text.InputType.TYPE_CLASS_TEXT or android.text.InputType.TYPE_TEXT_VARIATION_PASSWORD

        val inputNueva = EditText(requireContext())
        inputNueva.hint = "Contraseña nueva"
        inputNueva.inputType = android.text.InputType.TYPE_CLASS_TEXT or android.text.InputType.TYPE_TEXT_VARIATION_PASSWORD

        val inputConfirmar = EditText(requireContext())
        inputConfirmar.hint = "Confirmar contraseña nueva"
        inputConfirmar.inputType = android.text.InputType.TYPE_CLASS_TEXT or android.text.InputType.TYPE_TEXT_VARIATION_PASSWORD

        layout.addView(inputActual)
        layout.addView(inputNueva)
        layout.addView(inputConfirmar)

        val dialog= AlertDialog.Builder(requireContext())
            .setTitle("Cambiar contraseña")
            .setView(layout)
            .setNegativeButton("Cancelar", null)
            .setPositiveButton("Guardar",null)
            .create()
        dialog.show()
            dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener{
                val actual = inputActual.text.toString()
                val nueva = inputNueva.text.toString()
                val confirmar = inputConfirmar.text.toString()

                if (actual.isEmpty() || nueva.isEmpty() || confirmar.isEmpty()) {
                    Toast.makeText(requireContext(), "Todos los campos son obligatorios", Toast.LENGTH_SHORT).show()
                    return@setOnClickListener
                }
                if (nueva != confirmar) {
                    Toast.makeText(requireContext(), "Las contraseñas nuevas no coinciden", Toast.LENGTH_SHORT).show()
                    return@setOnClickListener
                }
                if (nueva.length < 8) {
                    Toast.makeText(requireContext(), "La contraseña debe tener al menos 8 caracteres", Toast.LENGTH_SHORT).show()
                    return@setOnClickListener
                }
                    cambiarPassword(actual, nueva, confirmar)
                dialog.dismiss()
            }
    }

    private fun cambiarPassword(actual: String, nueva: String, confirmar: String) {
        val url = "${Singleton.BASE_URL}/cambiarPassword"
        val body = JSONObject()
        body.put("password_actual", actual)
        body.put("password", nueva)
        body.put("password_confirmation", confirmar)

        progressBar.visibility = View.VISIBLE
        val request = object : JsonObjectRequest(
            Request.Method.POST, url, body,
            { response ->
                progressBar.visibility = View.GONE
                if (isAdded) {
                    Toast.makeText(requireContext(), response.optString("mensaje", "Contrasena actualizada"), Toast.LENGTH_SHORT).show()
                }
            },
            { error ->
                progressBar.visibility = View.GONE
                var mensaje = "No se pudo cambiar la contrasena"
                val networkresponse=error.networkResponse
                if (networkresponse!=null&&networkresponse.data!=null){
                    try {
                        val bodyStr = String(error.networkResponse.data)
                        val json = JSONObject(bodyStr)
                        mensaje = json.optString("mensaje", json.optString("msj", mensaje))
                    } catch (e: Exception) { }
                }
                if (isAdded) {
                    Toast.makeText(requireContext(), mensaje, Toast.LENGTH_LONG).show()
                }
            }
        ) {
            override fun getHeaders(): MutableMap<String, String> {
                val headers = HashMap<String, String>()
                headers["Authorization"] = "Bearer ${Singleton.token_actual}"
                headers["Accept"] = "application/json"
                return headers
            }
        }
        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            10000,
            2,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )
        VolleySingleton.getInstance(requireContext()).requestQueue.add(request)
    }

    private fun subirFoto(uri: Uri) {
        val inputStream = requireContext().contentResolver.openInputStream(uri) ?: return
        val bytes = inputStream.readBytes()
        inputStream.close()

        val url = "${Singleton.BASE_URL}/actualizarFoto"
        val boundary="boundary${System.currentTimeMillis()}"
        progressBar.visibility = View.VISIBLE

        val request = object : Request<NetworkResponseWrapper>(
            Method.POST, url,
            Response.ErrorListener { error ->
                progressBar.visibility = View.GONE
                if (isAdded) {
                    Toast.makeText(requireContext(), "No se pudo subir la foto", Toast.LENGTH_LONG).show()
                }
            }
        ) {
            override fun getHeaders(): MutableMap<String, String> {
                val headers = HashMap<String, String>()
                headers["Authorization"] = "Bearer ${Singleton.token_actual}"
                headers["Accept"] = "application/json"
                return headers
            }

            override fun getBodyContentType(): String {
                return "multipart/form-data; boundary=$boundary"
            }

            override fun getBody(): ByteArray {
                val output = ByteArrayOutputStream()
                output.write("--$boundary\r\n".toByteArray())
                output.write("Content-Disposition: form-data; name=\"foto\"; filename=\"foto.jpg\"\r\n".toByteArray())
                output.write("Content-Type: image/jpeg\r\n\r\n".toByteArray())
                output.write(bytes)
                output.write("\r\n--$boundary--\r\n".toByteArray())
                return output.toByteArray()
            }

            override fun parseNetworkResponse(response: com.android.volley.NetworkResponse): Response<NetworkResponseWrapper> {
                return Response.success(NetworkResponseWrapper(), null)
            }

            override fun deliverResponse(response: NetworkResponseWrapper) {
                progressBar.visibility = View.GONE
                if (isAdded) {
                    Toast.makeText(requireContext(), "Foto actualizada correctamente", Toast.LENGTH_SHORT).show()
                    cargarPerfil()
                }
            }
        }
        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            10000,
            2,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )
        VolleySingleton.getInstance(requireContext()).requestQueue.add(request)
    }

    private class NetworkResponseWrapper

    private fun confirmarCerrarSesion() {
        AlertDialog.Builder(requireContext())
            .setTitle("Cerrar sesion")
            .setMessage("Estas seguro que deseas cerrar sesion?")
            .setNegativeButton("No", null)
            .setPositiveButton("Si, cerrar sesion") { _, _ -> cerrarSesion() }
            .show()
    }

    private fun cerrarSesion() {
        val url = "${Singleton.BASE_URL}/auth/cerrarSesion"
        val request = object : JsonObjectRequest(
            Request.Method.POST, url, null,
            { irALogin() },
            { irALogin() }
        ) {
            override fun getHeaders(): MutableMap<String, String> {
                val headers = HashMap<String, String>()
                headers["Authorization"] = "Bearer ${Singleton.token_actual}"
                headers["Accept"] = "application/json"
                return headers
            }
        }
        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            10000,
            2,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )
        VolleySingleton.getInstance(requireContext()).requestQueue.add(request)
    }

    private fun irALogin() {
        if (!isAdded) return
        Singleton.token_actual = null
        Singleton.usuario_actual = ""
        Singleton.rol_usuario = ""
        Singleton.foto_perfil = null
        Singleton.doctor_seleccionado_id = null
        val preferences=requireContext().getSharedPreferences("sesion_citas",Context.MODE_PRIVATE)
        preferences.edit().clear().apply()

        val intent = Intent(requireContext(), LoginActivity::class.java)
        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        startActivity(intent)
    }
}