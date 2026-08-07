package com.example.citasmedicas.ui

import android.os.Bundle
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.fragment.app.Fragment
import com.example.citasmedicas.R
import com.example.citasmedicas.utils.AutoUpdater
import com.google.android.material.appbar.MaterialToolbar
import com.google.android.material.bottomnavigation.BottomNavigationView

class Home : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_home)
        val topbar=findViewById<MaterialToolbar>(R.id.top_bar)
        val bottomNav = findViewById<BottomNavigationView>(R.id.bottom_navigation)

        // Verificar si hay una nueva versión del APK en GitHub Releases
        AutoUpdater.comprobarActualizacion(this)

        if (savedInstanceState == null) {
            cambiarFragment(HomeFragment())
            topbar.title=getString(R.string.inicio)
        }

        bottomNav.setOnItemSelectedListener { item ->
            when (item.itemId) {
                R.id.nav_home -> {
                    topbar.title=getString(R.string.inicio)
                    cambiarFragment(HomeFragment())
                    true
                }
                R.id.nav_citas -> {
                    topbar.title=getString(R.string.citas)
                    cambiarFragment(CitasFragment())
                    true
                }
                R.id.nav_doctores -> {
                    topbar.title=getString(R.string.doctores)
                    cambiarFragment(BusquedaDoctoresFragment())
                    true
                }
                R.id.nav_historial -> {
                    topbar.title=getString(R.string.historial)
                    cambiarFragment(HistorialFragment())
                    true
                }
                R.id.nav_perfil -> {
                    topbar.title=getString(R.string.perfil)
                    cambiarFragment(PerfilFragment())
                    true
                }
                else -> false
            }
        }
    }
    private fun cambiarFragment(fragment: Fragment) {
        supportFragmentManager.beginTransaction()
            .replace(R.id.fragment_container, fragment)
            .commit()
    }
}