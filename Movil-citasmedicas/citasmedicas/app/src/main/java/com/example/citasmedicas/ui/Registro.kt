package com.example.citasmedicas.ui

import android.app.DatePickerDialog
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.util.Log
import android.widget.ArrayAdapter
import android.widget.AutoCompleteTextView
import android.widget.ImageView
import android.widget.Spinner
import android.widget.TextView
import android.widget.Toast
import android.widget.ViewFlipper
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.cardview.widget.CardView
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import com.android.volley.toolbox.JsonObjectRequest
import com.example.citasmedicas.R
import com.example.citasmedicas.model.Singleton
import com.google.android.material.textfield.TextInputEditText
import org.json.JSONObject
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Locale
import com.android.volley.Request
import com.example.citasmedicas.network.VolleySingleton
import com.google.android.material.textfield.TextInputLayout

class Registro : AppCompatActivity() {
    lateinit var btn_siguiente: CardView
    lateinit var btn_atras: ImageView
    lateinit var viewflipper_registro: ViewFlipper
    lateinit var txt_boton: TextView
    lateinit var registro_nombre: TextInputEditText
    lateinit var registro_telefono: TextInputEditText
    lateinit var registro_fecha: TextInputEditText
    lateinit var spn_sexo: AutoCompleteTextView
    lateinit var registro_direccion: TextInputEditText
    lateinit var registro_correo: TextInputEditText
    lateinit var registro_contra: TextInputEditText
    lateinit var registro_verificarcontra: TextInputEditText
    lateinit var registro_curp: TextInputEditText
    lateinit var registro_nss: TextInputEditText
    lateinit var input_nombre: TextInputLayout
    lateinit var input_telefono: TextInputLayout
    lateinit var input_direccion: TextInputLayout
    lateinit var input_fecha: TextInputLayout
    lateinit var input_sexo: TextInputLayout
    lateinit var input_correo: TextInputLayout
    lateinit var input_contra: TextInputLayout
    lateinit var input_verificarcontra: TextInputLayout
    lateinit var input_curp: TextInputLayout
    lateinit var input_nss: TextInputLayout
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_registro)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }
        btn_atras=findViewById(R.id.btn_atras)
        btn_siguiente=findViewById(R.id.btn_siguiente)
        viewflipper_registro=findViewById(R.id.viewflipper_registro)
        txt_boton=findViewById(R.id.txt_boton)

        registro_nombre=findViewById(R.id.registro_nombre)
        registro_telefono=findViewById(R.id.registro_telefono)
        registro_direccion=findViewById(R.id.registro_direccion)
        registro_correo=findViewById(R.id.registro_correo)
        registro_contra=findViewById(R.id.registro_contra)
        registro_verificarcontra=findViewById(R.id.registro_verificarcontra)
        registro_curp=findViewById(R.id.registro_curp)
        registro_nss=findViewById(R.id.registro_nss)
        input_nombre=findViewById(R.id.input_nombre)
        input_telefono=findViewById(R.id.input_telefono)
        input_direccion=findViewById(R.id.input_direccion)
        input_fecha=findViewById(R.id.input_fecha)
        input_sexo=findViewById(R.id.input_sexo)
        input_correo = findViewById(R.id.input_correo)
        input_contra = findViewById(R.id.input_contra)
        input_verificarcontra = findViewById(R.id.input_confirmarcontra)
        input_curp = findViewById(R.id.input_curp)
        input_nss = findViewById(R.id.input_nss)

        registro_fecha=findViewById(R.id.registro_fecha)

        registro_fecha.setOnClickListener {
            mostrarcalendario()
        }
        spn_sexo=findViewById(R.id.spn_sexo)
        var  adapter = ArrayAdapter(this,android.R.layout.simple_list_item_1, Singleton.list_sexo)
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spn_sexo.setAdapter(adapter)

        btn_atras.setOnClickListener {
            if (viewflipper_registro.displayedChild==0){
                onBackPressedDispatcher.onBackPressed()
            }
            else{
                viewflipper_registro.showPrevious()
                txt_boton.text="Siguiente"
            }
        }
        btn_siguiente.setOnClickListener {
            when(viewflipper_registro.displayedChild){
                0->{
                    if (validacionpaso1()){
                    viewflipper_registro.showNext()
                    }
                }
                1->{
                    if (validacionpaso2()){
                    viewflipper_registro.showNext()
                    txt_boton.text="Registrarme"
                    }
                }
                2->{
                    if (validacionpaso3()){
                        btn_siguiente.isEnabled = false
                        txt_boton.text = "Registrando..."
                    registrarpaciente()
                    }
                }
            }
        }
    }
    private fun mostrarcalendario(){
        val calendario = Calendar.getInstance()
        val año_actual = calendario.get(Calendar.YEAR)
        val mes_actual = calendario.get(Calendar.MONTH)
        val dia_actual = calendario.get(Calendar.DAY_OF_MONTH)
        val datePickerDialog = DatePickerDialog(this,
            { _, año, mes, dia ->
                calendario.set(año, mes, dia)

                val formato = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
                val fechaString = formato.format(calendario.time)
                registro_fecha.setText(fechaString)
            },
            año_actual, mes_actual, dia_actual
        )

        datePickerDialog.show()
    }
    private fun validacionpaso1(): Boolean{
        val nombre=registro_nombre.text.toString().trim()
        val fecha=registro_fecha.text.toString().trim()
        val direccion=registro_direccion.text.toString().trim()
        val numero=registro_telefono.text.toString().trim()
        val sexo=spn_sexo.text.toString().trim()
        input_nombre.error=null
        input_telefono.error=null
        input_direccion.error=null
        input_fecha.error=null
        input_sexo.error=null
        if (nombre.isEmpty()){
            input_nombre.error= Singleton.arraylist_mensajes[0]
            return false
        }
        else if (!nombre.matches(Regex(Singleton.arraylist_validaciones[3]))){
            input_nombre.error= Singleton.arraylist_mensajes[8]
        }
        if (numero.isEmpty()){
            input_telefono.error= Singleton.arraylist_mensajes[0]
            return false
        }
        else if (numero.length<10){
            input_telefono.error= Singleton.arraylist_mensajes[5]
            return false
        }
        if (direccion.isEmpty()){
            input_direccion.error= Singleton.arraylist_mensajes[0]
            return false
        }
        else if (!direccion.matches(Regex(Singleton.arraylist_validaciones[4]))){
            input_direccion.error= Singleton.arraylist_mensajes[9]
            return false
        }
        if (fecha.isEmpty()){
            input_fecha.error= Singleton.arraylist_mensajes[0]
            return false
        }
        else {
            val edad=calcularedad(fecha)
            if (edad<18){
                input_fecha.error= Singleton.arraylist_mensajes[6]
                return false
            }
        }

        if (sexo.isEmpty()){
            input_sexo.error= Singleton.arraylist_mensajes[0]
            return false
        }

        return true
    }
    private fun validacionpaso2(): Boolean{
        val correo=registro_correo.text.toString().trim()
        val contra=registro_contra.text.toString().trim()
        val verificar=registro_verificarcontra.text.toString().trim()
        input_correo.error=null
        input_contra.error=null
        input_verificarcontra.error=null

        if (correo.isEmpty()){
           input_correo.error= Singleton.arraylist_mensajes[0]
            return false
        }
        else if(!correo.matches(Regex(Singleton.arraylist_validaciones[0]))){
            input_correo.error= Singleton.arraylist_mensajes[1]
            return false
        }
        if (contra.isEmpty()){
            input_contra.error= Singleton.arraylist_mensajes[0]
            return false
        }
        else if(!contra.matches(Regex(Singleton.arraylist_validaciones[1]))){
           input_contra.error= Singleton.arraylist_mensajes[2]
            return false
        }
        if (verificar.isEmpty()){
            input_verificarcontra.error= Singleton.arraylist_mensajes[0]
            return false
        }
        else if (contra!=verificar){
            input_verificarcontra.error= Singleton.arraylist_mensajes[4]
            return false
        }
        return true
    }
    private fun validacionpaso3(): Boolean{
        val curp=registro_curp.text.toString().trim().uppercase()
        input_curp.error=null
        if (curp.isEmpty()){
            input_curp.error= Singleton.arraylist_mensajes[0]
            return false
        }
        else if (!curp.matches(Regex(Singleton.arraylist_validaciones[2]))){
            input_curp.error= Singleton.arraylist_mensajes[3]
            return false
        }
        return true
    }
    private fun calcularedad(fechanacimiento: String): Int{
        return try {
            val formato= SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
            val fechanacimiento=formato.parse(fechanacimiento)?:return -1
            val hoy= Calendar.getInstance()
            val nacimiento= Calendar.getInstance()
            nacimiento.time=fechanacimiento
            var edad=hoy.get(Calendar.YEAR)-nacimiento.get(Calendar.YEAR)
            if (hoy.get(Calendar.DAY_OF_YEAR)<nacimiento.get(Calendar.DAY_OF_YEAR)){
                edad--
            }
            edad
        }catch (e: Exception){
            -1
        }
    }
    private fun registrarpaciente(){
            val sexotexto=spn_sexo.text.toString()
        val sexoparaelbackend=when(sexotexto){
            "Masculino"->"M"
            "Femenino"->"F"
            else -> ""
        }
        val url="${Singleton.BASE_URL}/auth/registrarPaciente"
        val body= JSONObject()
        body.put("nombre",registro_nombre.text.toString().trim())
        body.put("email",registro_correo.text.toString().trim())
        body.put("password",registro_contra.text.toString().trim())
        body.put("password_confirmation",registro_verificarcontra.text.toString().trim())
        body.put("curp",registro_curp.text.toString().trim().uppercase())

        val fecha=registro_fecha.text.toString().trim()
        body.put("telefono",registro_telefono.text.toString().trim())
        body.put("fecha_nacimiento",if (fecha.isNotEmpty())fecha else JSONObject.NULL)
        body.put("sexo",if (sexoparaelbackend.isNotEmpty())sexoparaelbackend else JSONObject.NULL)
        body.put("direccion",registro_direccion.text.toString().trim())
        body.put("nss",registro_nss.text.toString().trim())

        val request= JsonObjectRequest(
            Request.Method.POST, url, body,
            { response ->
                if (response.has("token")) {
                    val token = response.getString("token")
                    val usuarioJson = response.getJSONObject("usuario")
                    val nombre = usuarioJson.getString("nombre")
                    val rol = usuarioJson.getString("rol")
                    val foto=if (!usuarioJson.isNull("foto_perfil"))usuarioJson.optString("foto_perfil",null)else null
                    Singleton.token_actual = token
                    Singleton.usuario_actual = nombre
                    Singleton.rol_usuario = rol
                    Singleton.foto_perfil=foto
                    val prefs = getSharedPreferences("sesion_citas", Context.MODE_PRIVATE)
                    prefs.edit()
                        .putString("token", token)
                        .putString("usuario", nombre)
                        .putString("rol", rol)
                        .putString("foto_perfil",foto)
                        .apply()

                    Toast.makeText(this, "Registro exitoso, bienvenido $nombre", Toast.LENGTH_SHORT).show()
                    startActivity(Intent(this, Home::class.java))
                    finish()
                } else {
                    val mensaje = response.optString("mensaje", "No se pudo completar el registro")
                    Toast.makeText(this, mensaje, Toast.LENGTH_LONG).show()
                }
            },
            { error ->
                btn_siguiente.isEnabled = true
                txt_boton.text = "Registrarme"
                var mensaje = "Error de conexión con el servidor"
                try {
                    val bodyStr = String(error.networkResponse.data)
                    Log.d("REGISTRO_ERROR", bodyStr)
                    val json = JSONObject(bodyStr)
                    if (json.has("errors")){
                        val errores=json.getJSONObject("errors")
                        val primercampo=errores.keys().next()
                        val listamensajes=errores.getJSONArray(primercampo)
                        mensaje=listamensajes.getString(0)
                    }
                    else{
                        mensaje = json.optString("mensaje", json.optString("msj", mensaje))
                    }
                } catch (e: Exception) { }
                Toast.makeText(this, mensaje, Toast.LENGTH_LONG).show()
            }
        )
        request.retryPolicy = com.android.volley.DefaultRetryPolicy(
            20000,
            2,
            com.android.volley.DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        )
        VolleySingleton.getInstance(this).requestQueue.add(request)
    }
}