package com.example.citasmedicas.model

data class NotaConsulta(
    val diagnostico: String,
    val tratamiento: String,
    val notasAdicionales: String?
)