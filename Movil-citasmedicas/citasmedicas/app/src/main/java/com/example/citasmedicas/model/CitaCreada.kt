package com.example.citasmedicas.model

data class CitaCreada (
    val id: Int,
    val perfilDoctorId: Int,
    val especialidadId: Int,
    val codigoReferencia: String,
    val fechaCita: String,
    val horaCita: String,
    val duracionMinutos: Int,
    val estado: String,
    val doctorNombre: String,
    val especialidadNombre: String
)