package com.example.citasmedicas.ui

import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.View
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.core.view.isGone
import androidx.fragment.app.Fragment
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Doctor
import com.example.citasmedicas.model.Especialidad
import com.example.citasmedicas.model.Singleton
import com.example.citasmedicas.model.usuario
import com.example.citasmedicas.network.VolleySingleton
import com.google.android.material.search.SearchBar
import com.google.android.material.search.SearchView
import com.google.android.material.textfield.TextInputEditText

class BusquedaDoctoresFragment : Fragment(R.layout.fragment_busqueda_doctores) {
    lateinit var recycler_doctores: RecyclerView
    lateinit var buscardoctor: TextInputEditText
    lateinit var adapter: DoctorAdapter
    lateinit var nodoctores: TextView
    var listadoctores=ArrayList<Doctor>()
    override fun onViewCreated(view: View,savedInstanceState: Bundle?) {
        super.onViewCreated(view,savedInstanceState)
        recycler_doctores=view.findViewById(R.id.recycler_doctores)
       buscardoctor=view.findViewById(R.id.buscar_doctor)
        nodoctores=view.findViewById(R.id.no_doctores)
        recycler_doctores.layoutManager= LinearLayoutManager(requireContext())

        adapter= DoctorAdapter(listadoctores){doctorSeleccionado->
            Singleton.doctor_seleccionado_id=doctorSeleccionado.id
            (requireActivity()as Home).irACitas()
        }
        recycler_doctores.adapter=adapter
        cargacardoctores()
        configurarbusqueda()
    }
    private fun configurarbusqueda() {
        buscardoctor.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {
                filtrarDoctores(s.toString())
            }
            override fun afterTextChanged(s: Editable?) {}
        })
    }

    private fun filtrarDoctores(query: String) {
        val q=query.trim()
        val filtrados = if (query.isBlank()) {
            listadoctores
        } else {
            listadoctores.filter { doctor ->
                val nombreCoincide = doctor.usuario.nombre.contains(q, ignoreCase = true)
                val especialidadCoincide = doctor.especialidades.any { it.nombre.contains(q, ignoreCase = true) }

                nombreCoincide || especialidadCoincide
            }
        }
        if (filtrados.isEmpty()){
            nodoctores.visibility=View.VISIBLE
            recycler_doctores.visibility=View.GONE
        }
        else{
            nodoctores.visibility=View.GONE
            recycler_doctores.visibility=View.VISIBLE
        }
        adapter.actualizarlista(filtrados)
    }
    private fun cargacardoctores(){
    val url="${Singleton.BASE_URL}/obtenerDoctores"
        val request= JsonObjectRequest(
            Request.Method.GET,url,null,
            {response->
                if (!isAdded)return@JsonObjectRequest
                listadoctores.clear()
                val data=response.getJSONObject("data")
                val arraydoctores=data.getJSONArray("data")
                for (i in 0 until arraydoctores.length()){
                    val doctorjson=arraydoctores.getJSONObject(i)
                    val usuariojson=doctorjson.getJSONObject("usuario")
                    val usuario= usuario(
                    id = usuariojson.getInt("id"),
                    nombre = usuariojson.getString("nombre"),
                    email = usuariojson.getString("email"),
                    curp = usuariojson.getString("curp"),
                    telefono = usuariojson.optString("telefono",""),
                    rol = usuariojson.getString("rol"),
                    estado = usuariojson.getString("estado"),
                    foto_perfil = if (usuariojson.isNull("foto_perfil"))null else usuariojson.getString("foto_perfil"),
                        intentos =usuariojson.getInt("intentos_fallidos"),
                        bloqueo = if (usuariojson.isNull("bloqueado_hasta")) null else usuariojson.getString("bloqueado_hasta")
                    )
                    val especialidadesarray=doctorjson.getJSONArray("especialidades")
                    val listaespecialidades= ArrayList<Especialidad>()
                    for (j in 0 until especialidadesarray.length()){
                        val especialidadjson=especialidadesarray.getJSONObject(j)
                        listaespecialidades.add(Especialidad(especialidadjson.getInt("id"),especialidadjson.getString("nombre")))
                    }
                    val doctor= Doctor(
                        id = doctorjson.getInt("id"),
                        cedulaProfesional = doctorjson.getString("cedula_profesional"),
                        estadoValidacion = doctorjson.getString("estado_validacion"),
                        usuario = usuario,
                        especialidades = listaespecialidades
                    )
                    listadoctores.add(doctor)
            }
                adapter.actualizarlista(listadoctores)
            },
            {
                error ->
                if (isAdded&&context!=null){
                    Toast.makeText(requireContext(),"Error al cargar doctores", Toast.LENGTH_SHORT).show()
                }
            }
        )
        VolleySingleton.getInstance(requireContext()).requestQueue.add(request)
    }
}