package com.example.citasmedicas.model

data class Cita (
    val id: Int,
    val doctorNombre: String,
    val especialidad: String,
    val fecha: String,
    val hora: String,
    val estado: String,
    val codigoReferencia: String
)