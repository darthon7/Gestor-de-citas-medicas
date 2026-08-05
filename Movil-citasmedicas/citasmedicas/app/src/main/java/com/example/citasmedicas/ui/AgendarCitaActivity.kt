package com.example.citasmedicas.ui

import android.app.DatePickerDialog
import android.os.Bundle
import android.util.Log
import android.view.View
import android.widget.*
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Especialidad
import com.example.citasmedicas.model.Singleton
import com.example.citasmedicas.network.VolleySingleton
import org.json.JSONObject
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Locale

class AgendarCitaActivity : AppCompatActivity() {

    private lateinit var txtDoctorNombre: TextView
    private lateinit var txtFecha: TextView
    private lateinit var btnElegirFecha: View
    private lateinit var spnEspecialidad: AutoCompleteTextView
    private lateinit var contenedorHoras: LinearLayout
    private lateinit var txtSinHorario: TextView
    private lateinit var btnConfirmar: Button
    private lateinit var progressBar: ProgressBar

    private var doctorId: Int = -1
    private var especialidadesDoctor: List<Especialidad> = emptyList()
    private var especialidadSeleccionadaId: Int? = null
    private var fechaSeleccionada: String? = null
    private var horaSeleccionada: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_agendar_cita)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }

        doctorId = Singleton.doctor_seleccionado_id ?: -1
        if (doctorId == -1) {
            Toast.makeText(this, "No se seleccionó ningún doctor", Toast.LENGTH_SHORT).show()
            finish()
            return
        }

        txtDoctorNombre = findViewById(R.id.txt_doctor_nombre_agendar)
        txtFecha = findViewById(R.id.txt_fecha_seleccionada)
        btnElegirFecha = findViewById(R.id.btn_elegir_fecha)
        spnEspecialidad = findViewById(R.id.spn_especialidad_agendar)
        contenedorHoras = findViewById(R.id.contenedor_horas)
        txtSinHorario = findViewById(R.id.txt_sin_horario)
        btnConfirmar = findViewById(R.id.btn_confirmar_cita)
        progressBar = findViewById(R.id.progress_agendar)

        findViewById<ImageView>(R.id.btn_atras_agendar).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }

        btnConfirmar.isEnabled = false

        cargarDetalleDoctor()

        btnElegirFecha.setOnClickListener { mostrarCalendario() }
        btnConfirmar.setOnClickListener { mostrarResumen() }
    }

    private fun cargarDetalleDoctor() {
        val url = "${Singleton.BASE_URL}/obtenerDoctor/$doctorId"
        val request = JsonObjectRequest(
            Request.Method.GET, url, null,
            { response ->
                val data = response.getJSONObject("data")
                val usuarioJson = data.getJSONObject("usuario")
                txtDoctorNombre.text = usuarioJson.getString("nombre")

                val especialidadesArray = data.getJSONArray("especialidades")
                val lista = ArrayList<Especialidad>()
                for (i in 0 until especialidadesArray.length()) {
                    val e = especialidadesArray.getJSONObject(i)
                    lista.add(Especialidad(e.getInt("id"), e.getString("nombre")))
                }
                especialidadesDoctor = lista
                configurarSpinnerEspecialidad()
            },
            {
                Toast.makeText(this, "Error al cargar el doctor", Toast.LENGTH_SHORT).show()
            }
        )
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }


    private fun configurarSpinnerEspecialidad() {
        if (especialidadesDoctor.isEmpty()) {
            Toast.makeText(this, "Este doctor no tiene especialidades asignadas todavía", Toast.LENGTH_LONG).show()
            spnEspecialidad.isEnabled = false
            return
        }

        val nombres = especialidadesDoctor.map { it.nombre }
        val adapter = ArrayAdapter(this, android.R.layout.simple_list_item_1, nombres)
        spnEspecialidad.setAdapter(adapter)

        spnEspecialidad.setText(nombres[0], false)
        especialidadSeleccionadaId = especialidadesDoctor[0].id

        spnEspecialidad.setOnItemClickListener { _, _, position, _ ->
            especialidadSeleccionadaId = especialidadesDoctor[position].id
            actualizarBotonConfirmar()
        }
    }

    private fun mostrarCalendario() {
        val calendario = Calendar.getInstance()
        val dialog = DatePickerDialog(
            this,
            { _, year, month, day ->
                calendario.set(year, month, day)
                val formato = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
                val fechaString = formato.format(calendario.time)
                fechaSeleccionada = fechaString
                txtFecha.text = fechaString
                consultarDisponibilidad(fechaString)
            },
            calendario.get(Calendar.YEAR),
            calendario.get(Calendar.MONTH),
            calendario.get(Calendar.DAY_OF_MONTH)
        )
        dialog.datePicker.minDate = System.currentTimeMillis() - 1000
        dialog.show()
    }

    private fun consultarDisponibilidad(fecha: String) {
        contenedorHoras.removeAllViews()
        txtSinHorario.visibility = View.GONE
        horaSeleccionada = null
        actualizarBotonConfirmar()
        progressBar.visibility = View.VISIBLE

        val url = "${Singleton.BASE_URL}/obtenerDisponibilidad/$doctorId?fecha=$fecha"
        val request = JsonObjectRequest(
            Request.Method.GET, url, null,
            { response ->
                progressBar.visibility = View.GONE
                val horasArray = response.getJSONArray("data")
                if (horasArray.length() == 0) {
                    txtSinHorario.text = response.optString("mensaje", "No hay horarios disponibles para esta fecha")
                    txtSinHorario.visibility = View.VISIBLE
                    return@JsonObjectRequest
                }
                for (i in 0 until horasArray.length()) {
                    val horaJson = horasArray.getJSONObject(i)
                    val hora = horaJson.getString("hora")
                    val disponible = horaJson.getBoolean("disponible")
                    agregarBotonHora(hora, disponible)
                }
            },
            { error ->
                progressBar.visibility = View.GONE
                txtSinHorario.text = "No se pudo consultar la disponibilidad"
                txtSinHorario.visibility = View.VISIBLE
            }
        )
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }

    private fun agregarBotonHora(hora: String, disponible: Boolean) {
        val boton = Button(this)
        boton.text = if (hora.length>=5) hora.substring(0, 5) else hora// HH:MM
        boton.isEnabled = disponible
        boton.alpha = if (disponible) 1f else 0.4f
        boton.setOnClickListener {
            horaSeleccionada = hora
            marcarHoraSeleccionada(boton)
            actualizarBotonConfirmar()
        }
        val params = LinearLayout.LayoutParams(
            LinearLayout.LayoutParams.WRAP_CONTENT,
            LinearLayout.LayoutParams.WRAP_CONTENT
        )
        params.setMargins(8, 8, 8, 8)
        boton.layoutParams = params
        boton.tag = hora
        contenedorHoras.addView(boton)
    }

    private fun marcarHoraSeleccionada(botonSeleccionado: Button) {
        for (i in 0 until contenedorHoras.childCount) {
            val child = contenedorHoras.getChildAt(i)
            if (child is Button) {
                child.isSelected = (child == botonSeleccionado)
                child.alpha = if (child == botonSeleccionado) 1f else if (child.isEnabled) 0.7f else 0.4f
            }
        }
    }

    private fun actualizarBotonConfirmar() {
        btnConfirmar.isEnabled = fechaSeleccionada != null &&
                horaSeleccionada != null &&
                especialidadSeleccionadaId != null
    }

    private fun mostrarResumen() {
        val nombreDoctor = txtDoctorNombre.text.toString()
        val nombreEspecialidad = especialidadesDoctor.find { it.id == especialidadSeleccionadaId }?.nombre ?: "—"
        val fecha = fechaSeleccionada ?: "—"
        val hora = horaSeleccionada?.substring(0, 5) ?: "—"

        val mensajeResumen = "Doctor: $nombreDoctor\n" +
                "Especialidad: $nombreEspecialidad\n" +
                "Fecha: $fecha\n" +
                "Hora: $hora"

        android.app.AlertDialog.Builder(this)
            .setTitle("Resumen de tu cita")
            .setMessage(mensajeResumen)
            .setNegativeButton("Editar") { dialog, _ -> dialog.dismiss() }
            .setPositiveButton("Confirmar") { _, _ -> confirmarCita() }
            .show()
    }

    private fun confirmarCita() {
        val body = JSONObject()
        body.put("perfil_doctor_id", doctorId)
        body.put("especialidad_id", especialidadSeleccionadaId)
        body.put("fecha_cita", fechaSeleccionada)
        body.put("hora_cita", horaSeleccionada)

        btnConfirmar.isEnabled = false
        progressBar.visibility = View.VISIBLE

        val url = "${Singleton.BASE_URL}/agendarCita"
        val request = object : JsonObjectRequest(
            Request.Method.POST, url, body,
            { response ->
                progressBar.visibility = View.GONE
                if (response.has("data")){
                    val data = response.getJSONObject("data")
                    mostrarConfirmacionExitosa(data.getString("codigo_referencia"))
                }
                else{
                    btnConfirmar.isEnabled=true
                    Toast.makeText(this,response.optString("mensaje","No se pudo agendar la cita"),Toast.LENGTH_LONG).show()
                }
            },
            { error ->
                progressBar.visibility = View.GONE
                btnConfirmar.isEnabled = true
                var mensaje = "No se pudo agendar la cita"
                try {
                    val bodyStr = String(error.networkResponse.data)
                    Log.v("Error_agendar", bodyStr)
                    val json = JSONObject(bodyStr)
                    mensaje = json.optString("mensaje", json.optString("msj", mensaje))
                } catch (e: Exception) { }
                Toast.makeText(this, mensaje, Toast.LENGTH_LONG).show()
            }
        ) {
            override fun getHeaders(): MutableMap<String, String> {
                val headers = HashMap<String, String>()
                headers["Authorization"] = "Bearer ${Singleton.token_actual}"
                headers["Accept"] = "application/json"
                return headers
            }
        }
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }

    private fun mostrarConfirmacionExitosa(codigoReferencia: String) {
        AlertDialog_mostrar(codigoReferencia)
    }

    private fun AlertDialog_mostrar(codigo: String) {
        android.app.AlertDialog.Builder(this)
            .setTitle("¡Cita agendada!")
            .setMessage("Tu código de referencia es:\n$codigo")
            .setCancelable(false)
            .setPositiveButton("Aceptar") { _, _ ->
                Singleton.doctor_seleccionado_id = null
                finish()
            }
            .show()
    }
}