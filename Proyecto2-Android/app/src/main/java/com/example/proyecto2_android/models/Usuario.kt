package com.example.proyecto2_android.models

data class Usuario(
    val idUsuario: Int? = null,
    val rolUsuario: Int = 1,
    val nombreUsuario: String,
    val contrasen: String,
    val correo: String
)