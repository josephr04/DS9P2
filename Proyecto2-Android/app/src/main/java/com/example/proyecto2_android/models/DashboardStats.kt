package com.example.proyecto2_android.models

data class DashboardStats(
    val totalPostulantes: Int,
    val totalDocumentos: Int,
    val postulantesListos: Int,
    val edadPromedio: Int,
    val postulantesRecientes: List<PostulanteReciente>
)

data class PostulanteReciente(
    val idPostulante: Int,
    val nombreCompleto: String,
    val perfil: String? = null
)