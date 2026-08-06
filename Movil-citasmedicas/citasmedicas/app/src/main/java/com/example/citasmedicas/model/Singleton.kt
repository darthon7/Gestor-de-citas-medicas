package com.example.citasmedicas.model

object Singleton {
    const val BASE_URL: String = "http://127.0.0.1:8000/api"
    var token_actual: String?= null
    var rol_usuario: String=""
    var usuario_actual: String=""
    var foto_perfil: String?=null
    var doctor_seleccionado_id: Int? = null
    val arraylist_validaciones: ArrayList<String>
    val arraylist_mensajes: ArrayList<String>
    var list_sexo=listOf("Masculino","Femenino")
    init {
        arraylist_validaciones= ArrayList<String>()
        //correo
        arraylist_validaciones.add(0,"^[A-Za-z0-9+_.-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$")
        //contraseña
        arraylist_validaciones.add(1,"^.{8,}$")
        //curp
        arraylist_validaciones.add(2,"^[A-Z]{4}\\d{6}[HM][A-Z]{5}[A-Z0-9]\\d$")
        //nombre
        arraylist_validaciones.add(3,"^[A-Za-záéíóúÁÉÍÓÚñÑ ]+$")
        //direccion
        arraylist_validaciones.add(4,"^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ .,#-]+$")

        arraylist_mensajes= ArrayList<String>()
        arraylist_mensajes.add(0,"El campo no puede estar vacio")
        arraylist_mensajes.add(1,"El correo electronico no tiene un formato valido")
        arraylist_mensajes.add(2,"La contraseña debe tener al menos 8 caracteres")
        arraylist_mensajes.add(3,"La curp no tiene un formato valido")
        arraylist_mensajes.add(4,"La contraseña no coincide")
        arraylist_mensajes.add(5,"El numero tiene que tener al menos 10 digitos")
        arraylist_mensajes.add(6,"Debes ser mayor de 18 años para registrarte")
        arraylist_mensajes.add(7,"El codigo debe tener 6 digitos")
        arraylist_mensajes.add(8,"El nombre solo puede contener letras")
        arraylist_mensajes.add(9,"La direccion contiene caracteres no permitidos")

    }
    //convertir ruta relativa a url absoluta
    fun obtenerfoto(rutaRelativa: String?): String?{
        if (rutaRelativa.isNullOrEmpty())return null
        if (rutaRelativa.startsWith("http://")||rutaRelativa.startsWith("https://")){
            return rutaRelativa
        }
        val baseurllimpia=BASE_URL.removeSuffix("/api").removeSuffix("/")
        val rutalimpia=rutaRelativa.removePrefix("/")
        return "$baseurllimpia/storage/$rutalimpia"
    }
}