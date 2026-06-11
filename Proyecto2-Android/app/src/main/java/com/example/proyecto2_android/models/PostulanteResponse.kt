package com.example.proyecto2_android.models

data class PostulanteResponse(
    val idPostulante: Int,
    val idUsuario: Int,
    val nombre: String?,
    val nombre2: String?,
    val apellido: String?,
    val apellido2: String?,
    val perfil: String?,
    val correoPostulante: String?,
    val celular: String?
)

data class PostulantesListResponse(
    val success: Boolean,
    val data: List<PostulanteResponse>
)