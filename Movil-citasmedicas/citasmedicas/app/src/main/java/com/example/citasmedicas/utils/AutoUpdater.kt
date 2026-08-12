package com.example.citasmedicas.utils

import android.content.Context
import android.content.Intent
import android.net.Uri
import android.util.Log
import androidx.appcompat.app.AlertDialog
import com.android.volley.Request
import com.android.volley.toolbox.JsonObjectRequest
import com.example.citasmedicas.BuildConfig
import com.example.citasmedicas.model.Singleton
import com.example.citasmedicas.network.VolleySingleton

/**
 * AutoUpdater: compara el run_number registrado en el backend
 * contra el GITHUB_RUN_NUMBER compilado en el APK instalado.
 * Si el servidor tiene un número mayor -> hay actualización disponible.
 */
object AutoUpdater {
    private const val TAG = "AutoUpdater"

    fun comprobarActualizacion(context: Context, onFinish: (() -> Unit)? = null) {
        var callbackLlamado = false
        fun ejecutarCallback() {
            if (!callbackLlamado) {
                callbackLlamado = true
                onFinish?.invoke()
            }
        }

        val url = "${Singleton.BASE_URL}/app-version/latest"
        val requestQueue = VolleySingleton.getInstance(context).requestQueue

        val request = JsonObjectRequest(
            Request.Method.GET,
            url,
            null,
            { response ->
                try {
                    val version = response.optString("version", "")
                    val runNumberRemoto = response.optInt("run_number", 0)
                    val downloadUrl = response.optString("download_url", "")

                    val runNumberLocal = BuildConfig.GITHUB_RUN_NUMBER
                    Log.d(TAG, "Versión Remota: $version (Run #$runNumberRemoto), Versión Local: Run #$runNumberLocal")

                    if (runNumberRemoto > runNumberLocal && downloadUrl.isNotEmpty()) {
                        mostrarDialogoActualizacion(context, version, downloadUrl, ::ejecutarCallback)
                    } else {
                        ejecutarCallback()
                    }
                } catch (e: Exception) {
                    Log.e(TAG, "Error al procesar versión del servidor", e)
                    ejecutarCallback()
                }
            },
            { error ->
                Log.w(TAG, "No se pudo consultar la versión más reciente en el servidor: ${error.message}")
                ejecutarCallback()
            }
        )

        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            8000,
            1,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )

        requestQueue.add(request)
    }

    private fun mostrarDialogoActualizacion(
        context: Context,
        version: String,
        downloadUrl: String,
        onDismiss: () -> Unit
    ) {
        AlertDialog.Builder(context)
            .setTitle("🚀 Actualización disponible")
            .setMessage("Se ha detectado una nueva versión de la aplicación ($version). ¿Deseas descargar la actualización ahora?")
            .setCancelable(false)
            .setPositiveButton("Actualizar") { _, _ ->
                try {
                    val intent = Intent(Intent.ACTION_VIEW, Uri.parse(downloadUrl)).apply {
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
