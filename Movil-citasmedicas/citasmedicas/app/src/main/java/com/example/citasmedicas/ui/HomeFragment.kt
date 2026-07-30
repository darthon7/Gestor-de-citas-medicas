package com.example.citasmedicas.ui

import android.os.Bundle
import android.view.View
import android.widget.TextView
import androidx.fragment.app.Fragment
import com.android.volley.toolbox.NetworkImageView
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Singleton

class HomeFragment : Fragment(R.layout.fragment_home) {

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val txtHola = view.findViewById<TextView>(R.id.txt_hola)
        txtHola.text = "${getString(R.string.hola)}, ${Singleton.usuario_actual}"
        val img_usuario=view.findViewById<NetworkImageView>(R.id.img_usuario)
    }
}