package com.example.proyecto2_android.models

import java.io.Serializable

data class DocumentoCandidato(
    val nombre: String,
    val fecha: String,
    val tamaño: String,
    val icono: Int
) : Serializable