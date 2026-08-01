package com.example.citasmedicas.model

data class Consulta(
    val id: Int,
    val codigoReferencia: String,
    val fechaCita: String,
    val horaCita: String,
    val notaConsulta: NotaConsulta,
    val doctor: usuario,
    val especialidad: Especialidad
)