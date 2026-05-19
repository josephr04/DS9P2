// ─── models/Candidato.kt ──────────────────────────────────────────────────────
package com.example.proyecto2_android.models

data class Candidato(
    val nombre: String,
    val posicion: String,
    val fechaAplicacion: String,
    val avatarRes: Int? = null   // opcional: resource id de imagen local
)