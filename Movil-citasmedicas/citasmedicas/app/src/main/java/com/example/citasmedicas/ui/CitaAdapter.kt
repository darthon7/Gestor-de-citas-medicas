package com.example.citasmedicas.ui

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.core.content.ContextCompat
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
        val context = holder.itemView.context
        when (estado) {
            "agendada" -> {
                holder.tvEstado.text = "Agendada"
                holder.tvEstado.setBackgroundColor(ContextCompat.getColor(context, R.color.estado_agendada_bg))
                holder.tvEstado.setTextColor(ContextCompat.getColor(context, R.color.estado_agendada_text))
            }
            "confirmada" -> {
                holder.tvEstado.text = "Confirmada"
                holder.tvEstado.setBackgroundColor(ContextCompat.getColor(context, R.color.estado_confirmada_bg))
                holder.tvEstado.setTextColor(ContextCompat.getColor(context, R.color.estado_confirmada_text))
            }
            "completada" -> {
                holder.tvEstado.text = "Completada"
                holder.tvEstado.setBackgroundColor(ContextCompat.getColor(context, R.color.estado_completada_bg))
                holder.tvEstado.setTextColor(ContextCompat.getColor(context, R.color.estado_completada_text))
            }
            "cancelada" -> {
                holder.tvEstado.text = "Cancelada"
                holder.tvEstado.setBackgroundColor(ContextCompat.getColor(context, R.color.estado_cancelada_bg))
                holder.tvEstado.setTextColor(ContextCompat.getColor(context, R.color.estado_cancelada_text))
            }
            else -> {
                holder.tvEstado.text = estado
                holder.tvEstado.setBackgroundColor(ContextCompat.getColor(context, R.color.estado_completada_bg))
                holder.tvEstado.setTextColor(ContextCompat.getColor(context, R.color.estado_completada_text))
            }
        }
    }

    override fun getItemCount(): Int = citas.size

    fun actualizarLista(nuevaLista: List<Cita>) {
        citas = nuevaLista
        notifyDataSetChanged()
    }
}