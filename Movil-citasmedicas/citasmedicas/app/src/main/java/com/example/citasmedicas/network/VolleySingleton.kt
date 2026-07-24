package com.example.citasmedicas.network

import android.content.Context
import android.graphics.Bitmap
import androidx.collection.LruCache
import com.android.volley.RequestQueue
import com.android.volley.toolbox.ImageLoader
import com.android.volley.toolbox.Volley

class VolleySingleton private constructor(context: Context) {

    val requestQueue: RequestQueue = Volley.newRequestQueue(context.applicationContext)

    val imageLoader: ImageLoader = ImageLoader(requestQueue, object : ImageLoader.ImageCache {

        private val cache = LruCache<String, Bitmap>(20)

        override fun getBitmap(url: String): Bitmap? {
            return cache.get(url)
        }

        override fun putBitmap(url: String, bitmap: Bitmap) {
            cache.put(url, bitmap)
        }
    })

    companion object {
        @Volatile
        private var INSTANCE: VolleySingleton? = null

        fun getInstance(context: Context): VolleySingleton =
            INSTANCE ?: synchronized(this) {
                INSTANCE ?: VolleySingleton(context).also { INSTANCE = it }
            }
    }
}