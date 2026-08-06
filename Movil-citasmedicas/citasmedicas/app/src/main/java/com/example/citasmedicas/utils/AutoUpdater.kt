package com.example.citasmedicas.utils

import android.content.Context
import android.content.Intent
import android.net.Uri
import androidx.appcompat.app.AlertDialog
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.android.volley.toolbox.Volley
import com.example.citasmedicas.BuildConfig

/**
 * AutoUpdater: compara el run_number del último release de GitHub
 * contra el GITHUB_RUN_NUMBER compilado en el APK instalado.
 * Si GitHub tiene un número mayor → hay actualización disponible.
 */
object AutoUpdater {
    private const val GITHUB_RELEASE_API = "https://api.github.com/repos/darthon7/Gestor-de-citas-medicas/releases/latest"
    private const val DOWNLOAD_URL = "https://github.com/darthon7/Gestor-de-citas-medicas/releases/latest/download/app-debug.apk"

    fun comprobarActualizacion(context: Context) {
        val requestQueue = Volley.newRequestQueue(context.applicationContext)
        val request = JsonObjectRequest(
            Request.Method.GET,
            GITHUB_RELEASE_API,
            null,
            { response ->
                try {
                    // El tag es "v1.0.8", extraemos el número final: 8
                    val tagName = response.optString("tag_name", "")
                    val runNumberEnGithub = tagName.substringAfterLast(".").toIntOrNull() ?: return@JsonObjectRequest

                    // GITHUB_RUN_NUMBER es el número compilado dentro de este APK
                    val runNumberLocal = BuildConfig.GITHUB_RUN_NUMBER

                    if (runNumberEnGithub > runNumberLocal) {
                        mostrarDialogoActualizacion(context)
                    }
                } catch (e: Exception) {
                    e.printStackTrace()
                }
            },
            { /* Silencioso si no hay conexión */ }
        )
        requestQueue.add(request)
    }

    private fun mostrarDialogoActualizacion(context: Context) {
        AlertDialog.Builder(context)
            .setTitle("🚀 Actualización disponible")
            .setMessage("Se ha detectado una nueva versión de la aplicación. ¿Deseas descargar la actualización ahora?")
            .setCancelable(true)
            .setPositiveButton("Actualizar") { _, _ ->
                val intent = Intent(Intent.ACTION_VIEW, Uri.parse(DOWNLOAD_URL)).apply {
                    addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                }
                context.startActivity(intent)
            }
            .setNegativeButton("Más tarde", null)
            .show()
    }
}
