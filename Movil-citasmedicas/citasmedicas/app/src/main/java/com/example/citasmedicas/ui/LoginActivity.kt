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
import org.json.JSONObject

class LoginActivity : AppCompatActivity() {
    lateinit var login_usuario: TextInputEditText
    lateinit var login_usuariocontra: TextInputEditText
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

        btn_olvido.setOnClickListener {
        Toast.makeText(this,"funcion proximamente disponible",Toast.LENGTH_SHORT).show()
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
        if (email.isEmpty()||password.isEmpty()){
            Toast.makeText(this, Singleton.arraylist_mensajes[0],Toast.LENGTH_SHORT).show()
            return
        }
        if (!email.matches(Regex(Singleton.arraylist_validaciones[0]))){
            Toast.makeText(this, Singleton.arraylist_mensajes[1], Toast.LENGTH_SHORT).show()
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

                Singleton.token_actual = token
                Singleton.usuario_actual = nombre
                Singleton.rol_usuario = rol

                val prefs = getSharedPreferences("sesion_citas", Context.MODE_PRIVATE)
                prefs.edit()
                    .putString("token", token)
                    .putString("usuario", nombre)
                    .putString("rol", rol)
                    .apply()

                Toast.makeText(this, "Bienvenido $nombre", Toast.LENGTH_SHORT).show()
                startActivity(Intent(this, BusquedaDoctores::class.java))
                finish()
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
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }
}