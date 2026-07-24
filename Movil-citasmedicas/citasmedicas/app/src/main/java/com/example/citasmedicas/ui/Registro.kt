package com.example.citasmedicas.ui

import android.os.Bundle
import android.widget.ImageView
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.cardview.widget.CardView
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import com.example.citasmedicas.R
import com.google.android.material.textfield.TextInputEditText

class Registro : AppCompatActivity() {
    lateinit var btn_atras: ImageView
    lateinit var registro_nombre: TextInputEditText
    lateinit var registro_telefono: TextInputEditText
    lateinit var registro_direccion: TextInputEditText
    lateinit var btn_siguiente: CardView
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_registro)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }
        registro_nombre=findViewById(R.id.registro_nombre)
        registro_telefono=findViewById(R.id.registro_telefono)
        registro_direccion=findViewById(R.id.registro_direccion)
        btn_atras=findViewById(R.id.btn_atras)
        btn_siguiente=findViewById(R.id.btn_siguiente)
        btn_atras.setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
        btn_siguiente.setOnClickListener {

        }
    }
}