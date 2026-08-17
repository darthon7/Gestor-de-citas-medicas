package com.example.citasmedicas.ui

import android.content.Intent
import android.os.Bundle
import android.widget.ImageView
import android.widget.TextView
import android.widget.Toast
import android.widget.ViewFlipper
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
import com.google.android.material.textfield.TextInputLayout
import org.json.JSONObject

class Recuperar : AppCompatActivity() {
    lateinit var btn_siguiente: CardView
    lateinit var btn_atras: ImageView
    lateinit var viewflipper_recuperar: ViewFlipper
    lateinit var txt_boton: TextView
    lateinit var recuperar_correo: TextInputEditText
    lateinit var registro_codigo: TextInputEditText
    lateinit var recuperar_contra: TextInputEditText
    lateinit var recuperar_contra2: TextInputEditText
    lateinit var input_correo: TextInputLayout
    lateinit var input_nuevacontra: TextInputLayout
    lateinit var input_confirmarnuevacontra: TextInputLayout
    lateinit var input_codigo: TextInputLayout
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_recuperar)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }
        input_correo=findViewById(R.id.input_correoregistrado)
        input_nuevacontra=findViewById(R.id.input_nuevacontra)
        input_confirmarnuevacontra=findViewById(R.id.input_confirmarnuevacontra)
        input_codigo=findViewById(R.id.input_codigo)
        btn_atras=findViewById(R.id.btn_atras)
        btn_siguiente=findViewById(R.id.btn_siguiente)
        viewflipper_recuperar=findViewById(R.id.viewflipper_recuperar)
        txt_boton=findViewById(R.id.txt_boton)
        recuperar_correo=findViewById(R.id.recuperar_correo)
        registro_codigo=findViewById(R.id.registro_codigo)
        recuperar_contra=findViewById(R.id.recuperar_contra)
        recuperar_contra2=findViewById(R.id.recuperar_contra2)
        btn_atras.setOnClickListener {
            if (viewflipper_recuperar.displayedChild==0){
                onBackPressedDispatcher.onBackPressed()
            }
            else{
                viewflipper_recuperar.showPrevious()
                txt_boton.text="Siguiente"
            }
        }
        btn_siguiente.setOnClickListener {
            Toast.makeText(this,"Funcion restablecer contraseña no disponible", Toast.LENGTH_LONG).show()
            /*when(viewflipper_recuperar.displayedChild){
                0->solicitarRecuperacion()
                1->verificarcodigo()
                2->restablecerpassword()
            }*/
        }
    }
    private fun solicitarRecuperacion(){
        val correo=recuperar_correo.text.toString().trim()
        input_correo.error=null
        if (correo.isEmpty()){
            input_correo.error= Singleton.arraylist_mensajes[0]
            return
        }
        else if (!correo.matches(Regex(Singleton.arraylist_validaciones[0]))){
            input_correo.error= Singleton.arraylist_mensajes[1]
            return
        }
        val url="${Singleton.BASE_URL}/auth/solicitarRecuperacion"
        val body= JSONObject()
        body.put("email",correo)
        val request= JsonObjectRequest(
            Request.Method.POST,url,body,
            { response->
                Toast.makeText(this,response.optString("mensaje","Codigo enviado"), Toast.LENGTH_SHORT).show()
                viewflipper_recuperar.showNext()
                txt_boton.text="Verificar codigo"
            },
            {error -> errorbacked(error)}
        )
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }
    private fun verificarcodigo(){
        val codigo=registro_codigo.text.toString().trim()
        input_codigo.error=null
        if (codigo.length!=6){
            input_codigo.error= Singleton.arraylist_mensajes[7]
            return
        }
        val url="${Singleton.BASE_URL}/auth/verificarCodigo"
        val body= JSONObject()
        body.put("email",recuperar_correo.text.toString().trim())
        body.put("codigo",codigo)
        val request= JsonObjectRequest(
            Request.Method.POST,url,body,
            {response->
                viewflipper_recuperar.showNext()
                txt_boton.text="Restablecer contraseña"
            },
            {error-> errorbacked(error)
            }
        )
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }
    private fun restablecerpassword(){
        val password1=recuperar_contra.text.toString().trim()
        val password2=recuperar_contra2.text.toString().trim()
        input_nuevacontra.error=null
        input_confirmarnuevacontra.error=null
        if (!password1.matches(Regex(Singleton.arraylist_validaciones[1]))){
           input_nuevacontra.error= Singleton.arraylist_mensajes[2]
            return
        }
        if (password1!=password2){
            input_confirmarnuevacontra.error= Singleton.arraylist_mensajes[4]
            return
        }
        val url="${Singleton.BASE_URL}/auth/restablecerPassword"
        val body= JSONObject()
        body.put("email",recuperar_correo.text.toString().trim())
        body.put("codigo",registro_codigo.text.toString().trim())
        body.put("password",password1)
        body.put("password_confirmation",password2)
        val request= JsonObjectRequest(
            Request.Method.POST,url,body,{
                Toast.makeText(this,"Contraseña restablecida correctamente", Toast.LENGTH_LONG).show()
                startActivity(Intent(this, LoginActivity::class.java))
                finish()
            },
            {error -> errorbacked(error)}
        )
        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            10000,
            2,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }
    private fun errorbacked(error:com.android.volley.VolleyError){
        var mensaje="error de conexion con el servidor"
        try {
            val bodyStr=String(error.networkResponse.data)
            val json= JSONObject(bodyStr)
            mensaje=json.optString("mensaje",mensaje)
        }
        catch (e: Exception){}
        Toast.makeText(this,mensaje,Toast.LENGTH_LONG).show()
    }
}