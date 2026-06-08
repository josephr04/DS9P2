package com.example.proyecto2_android.models

import java.io.Serializable

data class Candidato(
    val nombre: String,
    val posicion: String,
    val fecha: String = "",
    val avatarResId: Int? = null,
    val idPostulante: Int = 0,
    val idUsuario: Int = 0
) : Serializable