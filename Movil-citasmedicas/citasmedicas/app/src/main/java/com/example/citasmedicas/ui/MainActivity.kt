package com.example.citasmedicas.ui

import android.content.Context
import android.content.Intent
import android.os.Bundle
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.lifecycle.lifecycleScope
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Singleton
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_main)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }
        lifecycleScope.launch {
            delay(2000)
            verificarsesion()
        }
    }
    private fun verificarsesion(){
        val guardado=getSharedPreferences("sesion_citas",Context.MODE_PRIVATE)
        val token_guardado=guardado.getString("token",null)
        if (token_guardado!=null){
            Singleton.rol_usuario=guardado.getString("rol","")?:""
            Singleton.usuario_actual=guardado.getString("usuario","")?:""
            Singleton.token_actual=token_guardado
            Singleton.foto_perfil=guardado.getString("foto_perfil",null)
            startActivity(Intent(this, Home::class.java))
        }
        else {
            startActivity(Intent(this, LoginActivity::class.java))
        }
            finish()
    }
}