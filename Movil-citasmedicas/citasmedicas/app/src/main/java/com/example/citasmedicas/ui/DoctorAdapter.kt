package com.example.citasmedicas.ui

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import android.widget.Toast
import androidx.cardview.widget.CardView
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Doctor
import com.google.android.material.card.MaterialCardView

class DoctorAdapter(
    private var listaDoctores: List<Doctor>
) : RecyclerView.Adapter<DoctorAdapter.DoctorViewHolder>() {

    class DoctorViewHolder(view: View) : RecyclerView.ViewHolder(view) {
        val imgDoctor: ImageView = view.findViewById(R.id.img_doctor)
        val txtNombre: TextView = view.findViewById(R.id.txt_nombre_doctor)
        val txtEspecialidad: TextView = view.findViewById(R.id.txt_especialidad)
        val txtEstado: TextView = view.findViewById(R.id.txt_estado)
        val cardDoctor: MaterialCardView = view.findViewById(R.id.card_doctor)
        val txtVerPerfil: TextView = view.findViewById(R.id.txt_verperfil)
        val btnAgendar: CardView = view.findViewById(R.id.btn_agendar)
    }
    fun actualizarlista(nuevaLista: List<Doctor>){
        listaDoctores=nuevaLista
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): DoctorViewHolder {
        val vista = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_doctor, parent, false)
        return DoctorViewHolder(vista)
    }

    override fun onBindViewHolder(holder: DoctorViewHolder, position: Int) {
        val doctor = listaDoctores[position]

        holder.txtNombre.text = doctor.usuario.nombre

        if (doctor.especialidades.isNotEmpty()) {
            holder.txtEspecialidad.text = doctor.especialidades[0].nombre
        } else {
            holder.txtEspecialidad.text = "Sin especialidad asignada"
        }

        if (doctor.estadoValidacion == "validado") {
            holder.txtEstado.text = "Validado"
            holder.cardDoctor.strokeColor = ContextCompat.getColor(holder.itemView.context, R.color.secondary)
        } else {
            holder.txtEstado.text = "Pendiente de validación"
            holder.cardDoctor.strokeColor = ContextCompat.getColor(holder.itemView.context, R.color.accent_warm)
        }

        holder.btnAgendar.setOnClickListener {
            Toast.makeText(holder.itemView.context, "Función de agendar cita próximamente", Toast.LENGTH_SHORT).show()
        }

        holder.txtVerPerfil.setOnClickListener {
            Toast.makeText(holder.itemView.context, "Ver perfil próximamente", Toast.LENGTH_SHORT).show()
        }
    }

    override fun getItemCount(): Int = listaDoctores.size
}