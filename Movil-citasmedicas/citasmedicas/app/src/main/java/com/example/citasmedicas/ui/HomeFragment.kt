package com.example.citasmedicas.ui

import android.os.Bundle
import android.view.View
import android.widget.TextView
import androidx.fragment.app.Fragment
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.android.volley.toolbox.NetworkImageView
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Cita
import com.example.citasmedicas.model.Singleton
import com.example.citasmedicas.model.usuario
import com.example.citasmedicas.network.VolleySingleton
import com.google.android.material.card.MaterialCardView
import java.text.SimpleDateFormat
import java.util.Locale

class HomeFragment : Fragment(R.layout.fragment_home) {
    lateinit var txtHola: TextView
    lateinit var img_usuario: NetworkImageView
    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

         txtHola = view.findViewById(R.id.txt_hola)
         img_usuario=view.findViewById(R.id.img_usuario)

        img_usuario.setDefaultImageResId(R.drawable.baseline_person_outline_24)
        img_usuario.setErrorImageResId(R.drawable.baseline_error_outline_24)
    }

    override fun onResume() {
        super.onResume()
        txtHola.text = "${getString(R.string.hola)}, ${Singleton.usuario_actual}"
        val imageLoader= VolleySingleton.getInstance(requireContext()).imageLoader
        val urlcompleta= Singleton.obtenerfoto(Singleton.foto_perfil)
        img_usuario.setImageUrl(urlcompleta,imageLoader)
        cargarproximacita()
    }
    private fun cargarproximacita(){
        val url="${Singleton.BASE_URL}/misCitas"
        val request=object : JsonObjectRequest(
            Request.Method.GET,url,null,
            respuestaExitosa@{response->
                if (!isAdded)return@respuestaExitosa
                val dataobjeto=response.getJSONObject("data")
                val data=dataobjeto.getJSONArray("data")
                var proximacita: Cita?=null
                for (i in 0 until data.length()){
                    val citajson=data.getJSONObject(i)
                    val estado=citajson.getString("estado")
                    if (estado!="cancelada"&&estado!="completada"){
                        val doctorjson=citajson.getJSONObject("perfil_doctor").getJSONObject("usuario")
                        val especialidadjson=citajson.getJSONObject("especialidad")
                        val fecha=citajson.getString("fecha_cita").substring(0,10)
                        val hora=citajson.getString("hora_cita").substring(0,5)
                        proximacita= Cita(
                            id=citajson.getInt("id"),
                            doctorNombre = doctorjson.getString("nombre"),
                            especialidad = especialidadjson.getString("nombre"),
                            fecha=fecha,
                            hora=hora,
                            estado=estado,
                            codigoReferencia = citajson.getString("codigo_referencia")
                        )
                        break
                    }
                }
                mostrarproximacita(proximacita)
            },
            {error->}
        ){
            override fun getHeaders(): MutableMap<String, String> {
                val headers= HashMap<String, String>()
                headers["Authorization"] = "Bearer ${Singleton.token_actual}"
                headers["Accept"] = "application/json"
                return headers
            }
        }
        VolleySingleton.getInstance(requireContext()).requestQueue.add(request)
    }
    private fun mostrarproximacita(cita: Cita?){
        val carddoctor=view?.findViewById<MaterialCardView>(R.id.card_doctor)
        val txtsincitas=view?.findViewById<TextView>(R.id.txt_sincitas)
        if (cita==null){
            carddoctor?.visibility= View.GONE
            txtsincitas?.visibility=View.VISIBLE
            return
        }
        carddoctor?.visibility= View.VISIBLE
        txtsincitas?.visibility=View.GONE
        view?.findViewById<TextView>(R.id.txt_nombre_doctorsito)?.text=cita.doctorNombre
        view?.findViewById<TextView>(R.id.txt_especialidadhome)?.text=cita.especialidad
        view?.findViewById<TextView>(R.id.txt_tiempo)?.text=cita.hora
        view?.findViewById<TextView>(R.id.txt_consultorio)?.text=cita.codigoReferencia
        val (dia,mes)=separardiames(cita.fecha)
        view?.findViewById<TextView>(R.id.txt_dia)?.text=dia
        view?.findViewById<TextView>(R.id.txt_mes)?.text=mes
    }
    private fun separardiames(fechaiso: String): Pair<String, String>{
        return try {
            val formatoentrada= SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
            val fecha=formatoentrada.parse(fechaiso)
            val dia= SimpleDateFormat("dd", Locale.getDefault()).format(fecha!!)
            val mes= SimpleDateFormat("MMM", Locale("es","MX")).format(fecha)
            Pair(dia,mes)
        }
        catch (e: Exception){
            Pair("--","---")
        }
    }
}