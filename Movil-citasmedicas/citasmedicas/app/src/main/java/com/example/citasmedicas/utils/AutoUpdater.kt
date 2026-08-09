package com.example.citasmedicas.utils

import android.content.Context
import android.content.Intent
import android.net.Uri
import android.util.Log
import androidx.appcompat.app.AlertDialog
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.example.citasmedicas.BuildConfig
import com.example.citasmedicas.network.VolleySingleton

/**
 * AutoUpdater: compara el run_number del último release de GitHub
 * contra el GITHUB_RUN_NUMBER compilado en el APK instalado.
 * Si GitHub tiene un número mayor -> hay actualización disponible.
 */
object AutoUpdater {
    private const val GITHUB_RELEASE_API = "https://api.github.com/repos/darthon7/Gestor-de-citas-medicas/releases/latest"
    private const val DOWNLOAD_URL = "https://github.com/darthon7/Gestor-de-citas-medicas/releases/latest/download/app-debug.apk"
    private const val TAG = "AutoUpdater"

    fun comprobarActualizacion(context: Context, onFinish: (() -> Unit)? = null) {
        var callbackLlamado = false
        fun ejecutarCallback() {
            if (!callbackLlamado) {
                callbackLlamado = true
                onFinish?.invoke()
            }
        }

        val requestQueue = VolleySingleton.getInstance(context).requestQueue
        val request = object : JsonObjectRequest(
            Method.GET,
            GITHUB_RELEASE_API,
            null,
            { response ->
                try {
                    // El tag es "v1.0.8" o similar, extraemos el número final después del punto
                    val tagName = response.optString("tag_name", "")
                    val runNumberEnGithub = tagName.substringAfterLast(".").toIntOrNull()

                    val runNumberLocal = BuildConfig.GITHUB_RUN_NUMBER
                    Log.d(TAG, "Versión GitHub: $tagName (Run #$runNumberEnGithub), Versión Local Run #$runNumberLocal")

                    if (runNumberEnGithub != null && runNumberEnGithub > runNumberLocal) {
                        mostrarDialogoActualizacion(context, tagName, ::ejecutarCallback)
                    } else {
                        ejecutarCallback()
                    }
                } catch (e: Exception) {
                    Log.e(TAG, "Error al procesar versión de GitHub", e)
                    ejecutarCallback()
                }
            },
            { error ->
                Log.w(TAG, "No se pudo consultar actualizaciones en GitHub: ${error.message}")
                ejecutarCallback()
            }
        ) {
            override fun getHeaders(): MutableMap<String, String> {
                val headers = HashMap<String, String>()
                headers["User-Agent"] = "CitasMedicas-AndroidApp"
                return headers
            }
        }
        requestQueue.add(request)
    }

    private fun mostrarDialogoActualizacion(context: Context, tagName: String, onDismiss: () -> Unit) {
        AlertDialog.Builder(context)
            .setTitle("🚀 Actualización disponible")
            .setMessage("Se ha detectado una nueva versión de la aplicación ($tagName). ¿Deseas descargar la actualización ahora?")
            .setCancelable(false)
            .setPositiveButton("Actualizar") { _, _ ->
                try {
                    val intent = Intent(Intent.ACTION_VIEW, Uri.parse(DOWNLOAD_URL)).apply {
                        addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                    }
                    context.startActivity(intent)
                } catch (e: Exception) {
                    Log.e(TAG, "Error al abrir la URL de descarga", e)
                }
                onDismiss()
            }
            .setNegativeButton("Más tarde") { dialog, _ ->
                dialog.dismiss()
                onDismiss()
            }
            .show()
    }
}
