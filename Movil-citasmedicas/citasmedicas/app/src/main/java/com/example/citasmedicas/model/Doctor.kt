package com.example.citasmedicas.model

data class Doctor(
    val id: Int,
    val cedulaProfesional: String,
    val estadoValidacion: String,
    val usuario: usuario,
    val especialidades: List<Especialidad>
)