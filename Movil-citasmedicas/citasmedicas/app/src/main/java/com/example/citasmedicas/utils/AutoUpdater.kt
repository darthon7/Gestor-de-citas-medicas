package com.example.citasmedicas.utils

import android.content.Context
import android.content.Intent
import android.net.Uri
import androidx.appcompat.app.AlertDialog
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.android.volley.toolbox.Volley

object AutoUpdater {
    private const val GITHUB_RELEASE_API = "https://api.github.com/repos/darthon7/Gestor-de-citas-medicas/releases/latest"
    private const val DOWNLOAD_URL = "https://github.com/darthon7/Gestor-de-citas-medicas/releases/latest/download/app-debug.apk"

    fun comprobarActualizacion(context: Context) {
        val prefs = context.getSharedPreferences("app_update_prefs", Context.MODE_PRIVATE)
        val ultimaVersionGuardada = prefs.getString("last_published_at", "")

        val requestQueue = Volley.newRequestQueue(context.applicationContext)
        val request = JsonObjectRequest(
            Request.Method.GET,
            GITHUB_RELEASE_API,
            null,
            { response ->
                try {
                    val publishedAt = response.optString("published_at", "")
                    if (publishedAt.isNotEmpty() && publishedAt != ultimaVersionGuardada && !ultimaVersionGuardada.isNullOrEmpty()) {
                        mostrarDialogoActualizacion(context, publishedAt)
                    } else if (ultimaVersionGuardada.isNullOrEmpty() && publishedAt.isNotEmpty()) {
                        // Guardar versión inicial silenciosamente
                        prefs.edit().putString("last_published_at", publishedAt).apply()
                    }
                } catch (e: Exception) {
                    e.printStackTrace()
                }
            },
            { /* Omitir error si no hay conexión */ }
        )
        requestQueue.add(request)
    }

    private fun mostrarDialogoActualizacion(context: Context, nuevaFechaVersion: String) {
        AlertDialog.Builder(context)
            .setTitle("🚀 Actualización disponible")
            .setMessage("Se ha detectado una nueva versión de la aplicación. ¿Deseas descargar la actualización ahora?")
            .setCancelable(true)
            .setPositiveButton("Actualizar") { _, _ ->
                val prefs = context.getSharedPreferences("app_update_prefs", Context.MODE_PRIVATE)
                prefs.edit().putString("last_published_at", nuevaFechaVersion).apply()

                val intent = Intent(Intent.ACTION_VIEW, Uri.parse(DOWNLOAD_URL)).apply {
                    addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                }
                context.startActivity(intent)
            }
            .setNegativeButton("Más tarde", null)
            .show()
    }
}
