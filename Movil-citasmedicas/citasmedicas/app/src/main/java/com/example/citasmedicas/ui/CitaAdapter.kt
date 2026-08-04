package com.example.citasmedicas.ui

import android.graphics.Color
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Cita
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class CitaAdapter(
    private var citas: List<Cita>,
    private val onCancelarClick: (Cita) -> Unit,
    private val onItemClick: (Cita) -> Unit
) : RecyclerView.Adapter<CitaAdapter.CitaViewHolder>() {

    inner class CitaViewHolder(view: View) : RecyclerView.ViewHolder(view) {
        val tvDoctorEspecialidad: TextView = view.findViewById(R.id.tvDoctorEspecialidad)
        val tvFechaHora: TextView = view.findViewById(R.id.tvFechaHora)
        val tvEstado: TextView = view.findViewById(R.id.tvEstado)
        val tvCodigoReferencia: TextView = view.findViewById(R.id.tvCodigoReferencia)
        val tvCancelar: TextView = view.findViewById(R.id.tvCancelar)
        val tvHoy: TextView = view.findViewById(R.id.tvHoy)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): CitaViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_cita, parent, false)
        return CitaViewHolder(view)
    }

    override fun onBindViewHolder(holder: CitaViewHolder, position: Int) {
        val cita = citas[position]

        holder.tvDoctorEspecialidad.text = "${cita.doctorNombre} - ${cita.especialidad}"
        holder.tvFechaHora.text = "${cita.fecha}    ${cita.hora}"
        holder.tvCodigoReferencia.text = cita.codigoReferencia

        aplicarEstiloEstado(holder, cita.estado)

        val esCancelable = cita.estado == "agendada" || cita.estado == "confirmada"
        holder.tvCancelar.visibility = if (esCancelable) View.VISIBLE else View.GONE

        val formato = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
        val hoy = formato.format(Date())
        holder.tvHoy.visibility = if (cita.fecha == hoy) View.VISIBLE else View.GONE

        holder.tvCancelar.setOnClickListener { onCancelarClick(cita) }
        holder.itemView.setOnClickListener { onItemClick(cita) }
    }

    private fun aplicarEstiloEstado(holder: CitaViewHolder, estado: String) {
        when (estado) {
            "agendada" -> {
                holder.tvEstado.text = "Agendada"
                holder.tvEstado.setBackgroundColor(Color.parseColor("#D6E9F5"))
                holder.tvEstado.setTextColor(Color.parseColor("#1B6B93"))
            }
            "confirmada" -> {
                holder.tvEstado.text = "Confirmada"
                holder.tvEstado.setBackgroundColor(Color.parseColor("#B5E8D5"))
                holder.tvEstado.setTextColor(Color.parseColor("#006A60"))
            }
            "completada" -> {
                holder.tvEstado.text = "Completada"
                holder.tvEstado.setBackgroundColor(Color.parseColor("#E2E2E2"))
                holder.tvEstado.setTextColor(Color.parseColor("#6B6B6B"))
            }
            "cancelada" -> {
                holder.tvEstado.text = "Cancelada"
                holder.tvEstado.setBackgroundColor(Color.parseColor("#F9D6D2"))
                holder.tvEstado.setTextColor(Color.parseColor("#E76F51"))
            }
            else -> {
                holder.tvEstado.text = estado
                holder.tvEstado.setBackgroundColor(Color.parseColor("#E2E2E2"))
                holder.tvEstado.setTextColor(Color.parseColor("#6B6B6B"))
            }
        }
    }

    override fun getItemCount(): Int = citas.size

    fun actualizarLista(nuevaLista: List<Cita>) {
        citas = nuevaLista
        notifyDataSetChanged()
    }
}