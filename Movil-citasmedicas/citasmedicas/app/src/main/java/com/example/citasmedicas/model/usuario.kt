package com.example.citasmedicas.model


data class usuario(
    val id: Int,
    val nombre: String,
    val email: String,
    val curp: String,
    val telefono: String,
    val rol: String,
    val estado: String,
    val foto_perfil: String?,
    val intentos: Int,
    val bloqueo: String?,
    val perfilpaciente: perfilpaciente?=null
)