package com.example.citasmedicas.ui

import android.os.Bundle
import android.view.View
import android.widget.ImageView
import android.widget.TextView
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import com.android.volley.toolbox.NetworkImageView
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Singleton
import com.example.citasmedicas.network.VolleySingleton
import com.google.android.material.card.MaterialCardView
import java.text.SimpleDateFormat
import java.util.Locale

class DetalleConsultaActivity : AppCompatActivity() {
    lateinit var imgdoctor: NetworkImageView
    lateinit var txtDoctor: TextView
    lateinit var txtEspecialidades: TextView
    lateinit var txtFecha: TextView
    lateinit var txtHora: TextView
    lateinit var txtDiagnostico: TextView
    lateinit var txtTratamiento: TextView
    lateinit var cardNotas: MaterialCardView
    lateinit var txtNotas: TextView
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_detalle_consulta)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }
        imgdoctor=findViewById(R.id.img_doctor_perfil)
        txtDoctor = findViewById(R.id.txt_doctor)
        txtEspecialidades = findViewById(R.id.txt_especialidades)
        txtFecha = findViewById(R.id.txt_fecha)
        txtHora = findViewById(R.id.txt_hora)
        txtDiagnostico = findViewById(R.id.txt_diagnostico)
        txtTratamiento = findViewById(R.id.txt_tratamiento)
        cardNotas = findViewById(R.id.card_notas_adicionales)
        txtNotas = findViewById(R.id.txt_notas)
        findViewById<ImageView>(R.id.btn_atras_detalle).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
        val foto_perfil=intent.getStringExtra("foto_perfil")?: ""
        val doctorNombre = intent.getStringExtra("doctor_nombre") ?: ""
        val especialidad = intent.getStringExtra("especialidad") ?: ""
        val fechaIso = intent.getStringExtra("fecha") ?: ""
        val hora = intent.getStringExtra("hora") ?: ""
        val diagnostico = intent.getStringExtra("diagnostico") ?: ""
        val tratamiento = intent.getStringExtra("tratamiento") ?: ""
        val notasAdicionales = intent.getStringExtra("notas_adicionales") ?: ""
        imgdoctor.setDefaultImageResId(R.drawable.baseline_person_outline_24)
        imgdoctor.setErrorImageResId(R.drawable.baseline_error_outline_24)
        if (foto_perfil.isNotEmpty()){
            val imageLoader= VolleySingleton.getInstance(this).imageLoader
            val urlcompleta= Singleton.obtenerfoto(foto_perfil)
            imgdoctor.setImageUrl(urlcompleta,imageLoader)
        }
        txtDoctor.text=doctorNombre
        txtEspecialidades.text=especialidad
        txtFecha.text=formatearFecha(fechaIso)
        txtHora.text=formateadorhora(hora)
        txtDiagnostico.text=diagnostico
        txtTratamiento.text=tratamiento
        if (notasAdicionales.isEmpty()){
            cardNotas.visibility= View.GONE
        }
        else{
            txtNotas.text=notasAdicionales
        }
    }
    private fun formateadorhora(horaiso: String): String{
        return try {
            val formatoentrada= SimpleDateFormat("HH:mm:ss", Locale.getDefault())
            val hora=formatoentrada.parse(horaiso)
            val formatosalida= SimpleDateFormat("hh:mm a")
            formatosalida.format(hora!!)
        }
        catch (e: Exception){
            horaiso
        }
    }
    private fun formatearFecha(fechaIso: String): String {
        return try {
            val formatoEntrada = SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss", Locale.getDefault())
            val fechaSoloParteUtil = fechaIso.substring(0, 19)
            val fecha = formatoEntrada.parse(fechaSoloParteUtil)
            val formatoSalida = SimpleDateFormat("dd MMMM yyyy", Locale("es", "MX"))
            formatoSalida.format(fecha!!)
        } catch (e: Exception) {
            fechaIso
        }
    }
}