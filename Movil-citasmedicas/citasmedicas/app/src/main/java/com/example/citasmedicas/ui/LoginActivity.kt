package com.example.citasmedicas.ui

import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.TextView
import android.widget.Toast
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.cardview.widget.CardView
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Singleton
import com.example.citasmedicas.network.VolleySingleton
import com.google.android.material.card.MaterialCardView
import com.google.android.material.textfield.TextInputEditText
import com.google.android.material.textfield.TextInputLayout
import org.json.JSONObject

class LoginActivity : AppCompatActivity() {
    lateinit var login_usuario: TextInputEditText
    lateinit var login_usuariocontra: TextInputEditText
    lateinit var login_usuario1: TextInputLayout
    lateinit var login_usuariocontra1: TextInputLayout
    lateinit var btn_olvido: TextView
    lateinit var btn_login: CardView
    lateinit var btn_registro: MaterialCardView
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_login)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }
        login_usuario=findViewById(R.id.login_usuario)
        login_usuariocontra=findViewById(R.id.login_usuariocontra)
        btn_olvido=findViewById(R.id.btn_olvido)
        btn_login=findViewById(R.id.btn_login)
        btn_registro=findViewById(R.id.btn_registro)
        login_usuario1=findViewById(R.id.login_usuario1)
        login_usuariocontra1=findViewById(R.id.login_usuariocontra1)

        btn_olvido.setOnClickListener {
            startActivity(Intent(this, Recuperar::class.java))
        }
        btn_login.setOnClickListener {
           login()
        }
        btn_registro.setOnClickListener {
            startActivity(Intent(this, Registro::class.java))
        }
    }
    private fun login(){
        val email=login_usuario.text.toString().trim()
        val password=login_usuariocontra.text.toString().trim()
        login_usuario1.error=null
        login_usuariocontra1.error=null
        if (email.isEmpty()){
            login_usuario1.error= Singleton.arraylist_mensajes[0]
            return
        }
        else if (!email.matches(Regex(Singleton.arraylist_validaciones[0]))){
            login_usuario1.error= Singleton.arraylist_mensajes[1]
            return
        }
        if (password.isEmpty()){
            login_usuariocontra1.error= Singleton.arraylist_mensajes[0]
            return
        }
        val url="${Singleton.BASE_URL}/auth/login"
        val body= JSONObject()
        body.put("email",email)
        body.put("password",password)
        val request = JsonObjectRequest(
            Request.Method.POST, url, body,
            { response ->
                val token = response.getString("token")
                val usuarioJson = response.getJSONObject("usuario")
                val nombre = usuarioJson.getString("nombre")
                val rol = usuarioJson.getString("rol")
                if (rol.lowercase()!="paciente"){
                    Singleton.token_actual=null
                    Singleton.usuario_actual=""
                    Singleton.rol_usuario=""
                    Singleton.foto_perfil=null
                    Singleton.doctor_seleccionado_id=null
                    val prefs=getSharedPreferences("sesion_citas",Context.MODE_PRIVATE)
                    prefs.edit().clear().apply()
                    Toast.makeText(this, "Acceso denegado. Esta app es exclusiva para pacientes", Toast.LENGTH_SHORT).show()
                }
                else{
                    val foto=if (usuarioJson.isNull("foto_perfil")) null else usuarioJson.getString("foto_perfil")
                    Singleton.token_actual = token
                    Singleton.usuario_actual = nombre
                    Singleton.rol_usuario = rol
                    Singleton.foto_perfil=foto
                    val prefs = getSharedPreferences("sesion_citas", Context.MODE_PRIVATE)
                    prefs.edit()
                        .putString("token", token)
                        .putString("usuario", nombre)
                        .putString("rol", rol)
                        .putString("foto_perfil",foto)
                        .apply()

                    Toast.makeText(this, "Bienvenido $nombre", Toast.LENGTH_SHORT).show()
                    startActivity(Intent(this, Home::class.java))
                    finish()
                }
            },
            { error ->
                val statusCode = error.networkResponse?.statusCode
                val mensaje = if(statusCode == 401 || statusCode == 403 || statusCode == 422) {
                    try {
                        val body=String(error.networkResponse.data)
                        val json= JSONObject(body)
                        json.optString("mensaje","Correo o contraseña incorrectos")
                    }
                    catch (e: Exception){
                        "Correo o contraseña incorrectos"
                    }
                } else {
                     "Error de conexión con el servidor"
                }
                Toast.makeText(this, mensaje, Toast.LENGTH_LONG).show()
            }
        )
        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            20000,
            2,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }
}