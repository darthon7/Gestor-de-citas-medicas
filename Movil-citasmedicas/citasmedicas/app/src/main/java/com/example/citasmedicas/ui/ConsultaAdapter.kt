package com.example.citasmedicas.ui

import android.content.Intent
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.cardview.widget.CardView
import androidx.recyclerview.widget.RecyclerView
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Consulta
import java.text.SimpleDateFormat
import java.util.Locale

class ConsultaAdapter (
private var listaconsultas: List<Consulta>
): RecyclerView.Adapter<ConsultaAdapter.ConsultaViewHolder>(){

    class ConsultaViewHolder(view: View): RecyclerView.ViewHolder(view){
        val txtnombredoctor: TextView=view.findViewById(R.id.txt_nombre_doctor)
        val txtespecialidad: TextView=view.findViewById(R.id.txt_especialidad)
        val txtfecha: TextView=view.findViewById(R.id.txt_fecha)
        val txtresumen: TextView=view.findViewById(R.id.txt_resumen)
        val btndetalle: CardView=view.findViewById(R.id.btn_verdetalle)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ConsultaViewHolder {
        val vista= LayoutInflater.from(parent.context).inflate(R.layout.item_consulta,parent,false)
       return ConsultaViewHolder(vista)
    }

    override fun onBindViewHolder(holder: ConsultaViewHolder, position: Int) {
        val consulta=listaconsultas[position]
        holder.txtnombredoctor.text=consulta.doctor.nombre
        holder.txtespecialidad.text=consulta.especialidad.nombre
        holder.txtfecha.text=formateadordefecha(consulta.fechaCita)
        val diagnosticocompleto=consulta.notaConsulta.diagnostico
        holder.txtresumen.text=if (diagnosticocompleto.length>40){
            diagnosticocompleto.substring(0,40)+"..."
        }
        else{
            diagnosticocompleto
        }
        holder.btndetalle.setOnClickListener {
            val intent= Intent(holder.itemView.context, DetalleConsultaActivity::class.java)
            intent.putExtra("foto_perfil",consulta.doctor.foto_perfil?:"")
            intent.putExtra("doctor_nombre",consulta.doctor.nombre)
            intent.putExtra("especialidad",consulta.especialidad.nombre)
            intent.putExtra("fecha",consulta.fechaCita)
            intent.putExtra("hora",consulta.horaCita)
            intent.putExtra("diagnostico",consulta.notaConsulta.diagnostico)
            intent.putExtra("tratamiento",consulta.notaConsulta.tratamiento)
            intent.putExtra("notas_adicionales",consulta.notaConsulta.notasAdicionales?:"")
            holder.itemView.context.startActivity(intent)
        }
    }

    override fun getItemCount(): Int=listaconsultas.size

    fun actualizarlista(nuevalista: List<Consulta>){
        listaconsultas=nuevalista
        notifyDataSetChanged()
    }
    private fun formateadordefecha(fechavieja: String): String{
        return try {
            val formatoentrada= SimpleDateFormat("yyy-MM-dd'T'HH:mm:ss", Locale.getDefault())
            val fechaparteutil=fechavieja.substring(0,19)
            val fecha=formatoentrada.parse(fechaparteutil)
            val formatosalida= SimpleDateFormat("dd MMMM yyyy", Locale("es","MX"))
            formatosalida.format(fecha!!)
        }
        catch (e: Exception){
            fechavieja
        }
    }
}