package com.example.citasmedicas.ui

import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Consulta
import com.example.citasmedicas.model.Especialidad
import com.example.citasmedicas.model.NotaConsulta
import com.example.citasmedicas.model.Singleton
import com.example.citasmedicas.model.usuario
import com.google.android.material.textfield.TextInputEditText
import com.example.citasmedicas.network.VolleySingleton


class HistorialFragment : Fragment(R.layout.fragment_historial) {
    lateinit var recyclerconsulta: RecyclerView
    lateinit var buscarconsulta: TextInputEditText
    lateinit var noconsultas: TextView
    var listaconsultas= ArrayList<Consulta>()
    lateinit var adapter: ConsultaAdapter
    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        recyclerconsulta=view.findViewById(R.id.recycler_consulta)
        buscarconsulta=view.findViewById(R.id.buscar_consulta)
        noconsultas=view.findViewById(R.id.no_consultas)
        recyclerconsulta.layoutManager= LinearLayoutManager(requireContext())
        adapter= ConsultaAdapter(listaconsultas)
        recyclerconsulta.adapter=adapter
        cargarhistorial()
        configurarbusqueda()
    }
    private fun configurarbusqueda() {
        buscarconsulta.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {
                filtrarconsulta(s.toString())
            }
            override fun afterTextChanged(s: Editable?) {}
        })
    }

    private fun filtrarconsulta(query: String) {
        val q=query.trim()
        val filtradas = if (query.isBlank()) {
            listaconsultas
        } else {
            listaconsultas.filter { consulta ->
                consulta.doctor.nombre.contains(q,ignoreCase = true)
            }
        }
        if (filtradas.isEmpty()){
            noconsultas.visibility=View.VISIBLE
            recyclerconsulta.visibility=View.GONE
        }
        else{
            noconsultas.visibility=View.GONE
            recyclerconsulta.visibility=View.VISIBLE
        }
        adapter.actualizarlista(filtradas)
    }
    private fun cargarhistorial(){
        val url="${Singleton.BASE_URL}/miHistorial"
        val request= object :JsonObjectRequest(
            Request.Method.GET,url,null,
            respuestaexitosa@{response->
                if (!isAdded)return@respuestaexitosa
                listaconsultas.clear()
                val arrayconsultas=response.getJSONArray("data")
                for (i in 0 until arrayconsultas.length()){
                    val citaJson = arrayconsultas.getJSONObject(i)
                    val notaJson = citaJson.getJSONObject("nota_consulta")
                    val doctorPerfilJson = citaJson.getJSONObject("perfil_doctor")
                    val doctorUsuarioJson = doctorPerfilJson.getJSONObject("usuario")
                    val especialidadJson = citaJson.getJSONObject("especialidad")

                    val nota = NotaConsulta(
                        diagnostico = notaJson.getString("diagnostico"),
                        tratamiento = notaJson.getString("tratamiento"),
                        notasAdicionales = if (notaJson.isNull("notas_adicionales")) null else notaJson.getString("notas_adicionales")
                    )

                    val doctor = usuario(
                        id = doctorUsuarioJson.getInt("id"),
                        nombre = doctorUsuarioJson.getString("nombre"),
                        email = doctorUsuarioJson.getString("email"),
                        curp = doctorUsuarioJson.getString("curp"),
                        telefono = doctorUsuarioJson.optString("telefono", ""),
                        rol = doctorUsuarioJson.getString("rol"),
                        estado = doctorUsuarioJson.getString("estado"),
                        foto_perfil = if (doctorUsuarioJson.isNull("foto_perfil")) null else doctorUsuarioJson.getString("foto_perfil"),
                        intentos = doctorUsuarioJson.getInt("intentos_fallidos"),
                        bloqueo = if (doctorUsuarioJson.isNull("bloqueado_hasta")) null else doctorUsuarioJson.getString("bloqueado_hasta")
                    )

                    val especialidad = Especialidad(
                        especialidadJson.getInt("id"),
                        especialidadJson.getString("nombre")
                    )

                    val consulta = Consulta(
                        id = citaJson.getInt("id"),
                        codigoReferencia = citaJson.getString("codigo_referencia"),
                        fechaCita = citaJson.getString("fecha_cita"),
                        horaCita = citaJson.getString("hora_cita"),
                        notaConsulta = nota,
                        doctor = doctor,
                        especialidad = especialidad
                    )
                    listaconsultas.add(consulta)
                }
                if (listaconsultas.isEmpty()){
                    noconsultas.visibility= View.VISIBLE
                    recyclerconsulta.visibility= View.GONE
                }
                adapter.actualizarlista(listaconsultas)
            },
            {error->
                if (isAdded&&context!=null){
                    Toast.makeText(requireContext(),"Error al cargar tu historial medico", Toast.LENGTH_SHORT).show()
                }
            }
        ){
            override fun getHeaders(): MutableMap<String, String> {
                val headers= HashMap<String, String>()
                headers["Authorization"]="Bearer ${Singleton.token_actual}"
                return  headers
            }
        }
        VolleySingleton.getInstance(requireContext()).requestQueue.add(request)
    }
}