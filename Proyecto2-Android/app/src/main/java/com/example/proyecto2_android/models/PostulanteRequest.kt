package com.example.proyecto2_android.models

data class PostulanteRequest(
    val idUsuario: Int,
    val rangoAcademico: Int,
    val nombre: String,
    val nombre2: String,
    val apellido: String,
    val apellido2: String,
    val prefijo: String,
    val tomo: String,
    val asiento: String,
    val genero: Int,
    val estadoCivil: Int,
    val tipoSangre: Int,
    val fechaNacimiento: String,
    val codigo_provincia: String,
    val codigo_distrito: String,
    val codigo_corregimiento: String,
    val comunidad: String,
    val calle: String,
    val casa: String,
    val detalleDireccion: String,
    val celular: String,
    val celular2: String,
    val telefono: String,
    val telefono2: String,
    val correoPostulante: String
)