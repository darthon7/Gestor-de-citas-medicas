package com.example.citasmedicas.model

object Singleton {
    const val BASE_URL: String = "http://127.0.0.1:8000/api"
    var token_actual: String?= null
    var rol_usuario: String=""
    var usuario_actual: String=""
    var respuesta: String?=null
    val arraylist_validaciones: ArrayList<String>
    val arraylist_mensajes: ArrayList<String>
    var list_sexo=listOf("Masculino","Femenino")
    init {
        arraylist_validaciones= ArrayList<String>()
        //correo
        arraylist_validaciones.add(0,"^[A-Za-z0-9+_.-]+@[A-Za-z0-9_.-]+$")
        //contraseña
        arraylist_validaciones.add(1,"^.{8,}$")
        //curp
        arraylist_validaciones.add(2,"^[A-Z]{4}\\d{6}[HM][A-Z]{5}[A-Z0-9]\\d$")

        arraylist_mensajes= ArrayList<String>()
        arraylist_mensajes.add(0,"Los campos no pueden estar vacios")
        arraylist_mensajes.add(1,"El correo electronico no tiene un formato valido")
        arraylist_mensajes.add(2,"La contraseña debe tener al menos 8 caracteres")
        arraylist_mensajes.add(3,"La curp no tiene un formato valido (18 caracteres)")
        arraylist_mensajes.add(4,"Las contraseñas no coinciden")

    }
}