package com.example.citasmedicas.ui

import android.os.Bundle
import android.view.View
import android.widget.ImageView
import android.widget.TextView
import android.widget.Toast
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import com.android.volley.toolbox.JsonObjectRequest
import com.android.volley.toolbox.NetworkImageView
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Singleton
import com.android.volley.Request
import com.example.citasmedicas.network.VolleySingleton

class DetalleDoctorActivity : AppCompatActivity() {
    lateinit var txtNombre: TextView
    lateinit var txtEspecialidad: TextView
    lateinit var txtCedula: TextView
    lateinit var txtCorreo: TextView
    lateinit var txtTelefono: TextView
    lateinit var img_verificado: ImageView
    lateinit var img_doctor: NetworkImageView
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_detalle_doctor)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }
        img_doctor=findViewById(R.id.img_doctor_perfil)
        img_verificado=findViewById(R.id.img_verificado)
        txtNombre = findViewById(R.id.nombre_doctor)
        txtEspecialidad = findViewById(R.id.txt_especialidad)
        txtCedula = findViewById(R.id.txt_cedula)
        txtCorreo = findViewById(R.id.correo_txt)
        txtTelefono = findViewById(R.id.telefono_txt)
        findViewById<ImageView>(R.id.btn_atras_detalle).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
        val doctorId=intent.getIntExtra("doctor_id",-1)
        if (doctorId==-1){
            Toast.makeText(this,"No se puedo cargar el doctor", Toast.LENGTH_SHORT).show()
            finish()
            return
        }
        cargardetalledoctor(doctorId)
    }
    private fun cargardetalledoctor(doctorId: Int){
        val url = "${Singleton.BASE_URL}/obtenerDoctor/$doctorId"
        val request = JsonObjectRequest(
            Request.Method.GET, url, null,
            { response ->
                val data = response.getJSONObject("data")
                val usuarioJson = data.getJSONObject("usuario")

                txtNombre.text = usuarioJson.getString("nombre")
                txtCedula.text = data.getString("cedula_profesional")
                txtCorreo.text = usuarioJson.getString("email")
                txtTelefono.text = usuarioJson.optString("telefono", "No disponible")

                val estadovalidacion=data.getString("estado_validacion")
                img_verificado.visibility=if (estadovalidacion=="validado")View.VISIBLE else View.GONE

                val foto=if (usuarioJson.isNull("foto_perfil")) null else usuarioJson.getString("foto_perfil")
                    val imageLoader= VolleySingleton.getInstance(this).imageLoader
                    img_doctor.setDefaultImageResId(R.drawable.baseline_person_outline_24)
                    img_doctor.setErrorImageResId(R.drawable.baseline_error_outline_24)
                val urlcompleta= Singleton.obtenerfoto(foto)
                    img_doctor.setImageUrl(urlcompleta,imageLoader)
                val especialidadesArray = data.getJSONArray("especialidades")
                if (especialidadesArray.length() > 0) {
                    val nombres = ArrayList<String>()
                    for (i in 0 until especialidadesArray.length()) {
                        nombres.add(especialidadesArray.getJSONObject(i).getString("nombre"))
                    }
                    txtEspecialidad.text = nombres.joinToString(", ")
                } else {
                    txtEspecialidad.text = "Sin especialidad asignada"
                }
            },
            { error ->
                Toast.makeText(this, "Error al cargar el detalle del doctor", Toast.LENGTH_SHORT).show()
                finish()
            }
        )
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }
}