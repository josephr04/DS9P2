package com.example.proyecto2_android.activities.network

import com.example.proyecto2_android.models.Corregimiento
import com.example.proyecto2_android.models.Distrito
import com.example.proyecto2_android.models.EstadoCivil
import com.example.proyecto2_android.models.PostulanteRequest
import com.example.proyecto2_android.models.Provincia
import com.example.proyecto2_android.models.RangoAcademico
import com.example.proyecto2_android.models.TipoSangre
import com.example.proyecto2_android.models.Usuario
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Query

interface ApiService {
    @GET("usuarios")
    suspend fun getUsuarios(): Response<List<Usuario>>

    @POST("usuarios")
    suspend fun registrarUsuario(@Body usuario: Usuario): Response<Usuario>

    @GET("provincias")
    suspend fun getProvincias(): Response<List<Provincia>>

    @GET("distritos")
    suspend fun getDistritos(): Response<List<Distrito>>

    @GET("corregimientos")
    suspend fun getCorregimientos(): Response<List<Corregimiento>>

    @GET("estados-civiles")
    suspend fun getEstadosCiviles(): Response<List<EstadoCivil>>

    @GET("tipos-sangre")
    suspend fun getTiposSangre(): Response<List<TipoSangre>>

    @GET("rangos-academicos")
    suspend fun getRangosAcademicos(): Response<List<RangoAcademico>>

    @POST("postulantes")
    suspend fun registrarPostulante(@Body postulante: PostulanteRequest): Response<Any>

    @GET("postulantes")
    suspend fun getPostulantes(): Response<List<Map<String, Any>>>
}