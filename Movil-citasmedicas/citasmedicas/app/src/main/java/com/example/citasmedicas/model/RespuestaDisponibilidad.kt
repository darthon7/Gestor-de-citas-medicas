package com.example.citasmedicas.model

data class RespuestaDisponibilidad (
    val mensajes: String,
    val fecha: String,
    val doctorId: Int,
    val duracionMin: Int,
    val horarios: List<HorarioDisponible>
)