package com.example.proyecto2_android.models

data class CambiarContrasenaRequest(
    val contrasena_actual: String,
    val nueva_contrasena: String
)