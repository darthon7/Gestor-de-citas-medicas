package com.example.citasmedicas.ui

import android.app.AlertDialog
import android.content.Intent
import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.View
import android.widget.Toast
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Cita
import com.example.citasmedicas.model.Singleton
import com.example.citasmedicas.network.VolleySingleton
import com.google.android.material.bottomnavigation.BottomNavigationView
import com.google.android.material.floatingactionbutton.FloatingActionButton
import com.google.android.material.tabs.TabLayout
import org.json.JSONObject

class CitasFragment : Fragment(R.layout.fragment_citas) {

    private lateinit var rvCitas: RecyclerView
    private lateinit var tabLayoutCitas: TabLayout
    private lateinit var adapter: CitaAdapter
    private var listaCompleta = ArrayList<Cita>()
    private var pestanaActual = 0

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        rvCitas = view.findViewById(R.id.rvCitas)
        tabLayoutCitas = view.findViewById(R.id.tabLayoutCitas)
        rvCitas.layoutManager = LinearLayoutManager(requireContext())
        adapter = CitaAdapter(
            citas = ArrayList(),
            onCancelarClick = { cita -> confirmarCancelacion(cita) },
            onItemClick = { cita -> mostrarDetalleCita(cita) }
        )
        rvCitas.adapter = adapter

        tabLayoutCitas.addOnTabSelectedListener(object : TabLayout.OnTabSelectedListener {
            override fun onTabSelected(tab: TabLayout.Tab?) {
                pestanaActual = tab?.position ?: 0
                actualizarListaMostrada()
            }
            override fun onTabUnselected(tab: TabLayout.Tab?) {}
            override fun onTabReselected(tab: TabLayout.Tab?) {}
        })

        val fabAgendar = view.findViewById<FloatingActionButton>(R.id.fabAgendarCita)
        fabAgendar.setOnClickListener {
            if (Singleton.doctor_seleccionado_id == null) {
                (requireActivity() as Home).findViewById<BottomNavigationView>(
                    R.id.bottom_navigation
                ).selectedItemId = R.id.nav_doctores
            } else {
                startActivity(Intent(requireContext(), AgendarCitaActivity::class.java))
            }
        }

        cargarMisCitas()
    }

    override fun onResume() {
        super.onResume()
        cargarMisCitas()
    }

    private fun cargarMisCitas() {
        val url = "${Singleton.BASE_URL}/misCitas"
        val request = object : JsonObjectRequest(
            Request.Method.GET, url, null,
            JsonObjectRequest@{ response ->
                if (!isAdded) return@JsonObjectRequest
                listaCompleta.clear()
                val dataObjeto = response.getJSONObject("data")
                val data = dataObjeto.getJSONArray("data")
                for (i in 0 until data.length()) {
                    val citaJson = data.getJSONObject(i)
                    val doctorJson = citaJson.getJSONObject("perfil_doctor").getJSONObject("usuario")
                    val especialidadJson = citaJson.getJSONObject("especialidad")

                    val fechaCompleta = citaJson.getString("fecha_cita")
                    val fecha = fechaCompleta.substring(0, 10)
                    val hora = citaJson.getString("hora_cita").substring(0, 5)

                    listaCompleta.add(
                        Cita(
                            id = citaJson.getInt("id"),
                            doctorNombre = doctorJson.getString("nombre"),
                            especialidad = especialidadJson.getString("nombre"),
                            fecha = fecha,
                            hora = hora,
                            estado = citaJson.getString("estado"),
                            codigoReferencia = citaJson.getString("codigo_referencia")
                        )
                    )
                }
                actualizarListaMostrada()
            },
            { error ->
                if (isAdded && context != null) {
                    Toast.makeText(requireContext(), "Error al cargar tus citas", Toast.LENGTH_SHORT).show()
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

    private fun actualizarListaMostrada() {
        val listaFiltrada = if (pestanaActual == 0) {
            listaCompleta.filter { it.estado == "agendada" || it.estado == "confirmada" }
                .sortedWith(compareBy({ it.fecha }, { it.hora }))
        } else {
            listaCompleta.filter { it.estado == "completada" || it.estado == "cancelada" }
                .sortedWith(compareByDescending<Cita> { it.fecha }.thenByDescending { it.hora })
        }
        adapter.actualizarLista(listaFiltrada)
    }

    private fun mostrarDetalleCita(cita: Cita) {
        val mensaje = "Doctor: ${cita.doctorNombre}\n" +
                "Especialidad: ${cita.especialidad}\n" +
                "Fecha: ${cita.fecha}\n" +
                "Hora: ${cita.hora}\n" +
                "Estado: ${cita.estado}\n" +
                "Codigo de referencia: ${cita.codigoReferencia}"

        AlertDialog.Builder(requireContext())
            .setTitle("Detalle de la cita")
            .setMessage(mensaje)
            .setPositiveButton("Cerrar", null)
            .show()
    }

    private fun confirmarCancelacion(cita: Cita) {
        val input = android.widget.EditText(requireContext())
        input.hint = "Motivo de cancelacion"

        AlertDialog.Builder(requireContext())
            .setTitle("Cancelar esta cita?")
            .setMessage("${cita.doctorNombre} - ${cita.fecha} ${cita.hora}")
            .setView(input)
            .setNegativeButton("No", null)
            .setPositiveButton("Si, cancelar") { _, _ ->
                val motivo = input.text.toString().ifBlank { "Cancelada por el paciente" }
                cancelarCita(cita, motivo)
            }
            .show()
    }

    private fun cancelarCita(cita: Cita, motivo: String) {
        val url = "${Singleton.BASE_URL}/cancelarMiCita/${cita.id}"
        val body = JSONObject()
        body.put("motivo_cancelacion", motivo)

        val request = object : JsonObjectRequest(
            Request.Method.PATCH, url, body,
            { response ->
                Toast.makeText(requireContext(), response.optString("mensaje", "Cita cancelada"), Toast.LENGTH_SHORT).show()
                cargarMisCitas()
            },
            { error ->
                var mensaje = "No se pudo cancelar la cita"
                try {
                    val bodyStr = String(error.networkResponse.data)
                    val json = JSONObject(bodyStr)
                    mensaje = json.optString("mensaje", json.optString("msj", mensaje))
                } catch (e: Exception) { }
                Toast.makeText(requireContext(), mensaje, Toast.LENGTH_LONG).show()
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
}