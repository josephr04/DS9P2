package com.example.proyecto2_android.models

data class DocumentoPostulante(
    val idDocumentoPostulante: Int = 0,
    val idGradoEst: Int,
    val idPostulante: Int,
    val codigo_provincia: String,
    val titulo: String,
    val institucion: Int,
    val otraInstitucionn: Int = 0,  // ← Int, no Boolean
    val nombreOtraInstitucion: String? = null,
    val fechaInicio: String,
    val fechaFinaizacion: String,
    val fechaEmision: String,
    val totalHoras: Int
)