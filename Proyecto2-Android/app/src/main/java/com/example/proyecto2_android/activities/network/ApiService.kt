package com.example.proyecto2_android.activities.network

import com.example.proyecto2_android.models.CambiarContrasenaRequest
import com.example.proyecto2_android.models.Corregimiento
import com.example.proyecto2_android.models.Distrito
import com.example.proyecto2_android.models.DocumentoPostulante
import com.example.proyecto2_android.models.EstadoCivil
import com.example.proyecto2_android.models.GradoAcademicoDocumento
import com.example.proyecto2_android.models.Institucion
import com.example.proyecto2_android.models.PostulanteRequest
import com.example.proyecto2_android.models.Provincia
import com.example.proyecto2_android.models.RangoAcademico
import com.example.proyecto2_android.models.RutaDocumento
import com.example.proyecto2_android.models.TipoSangre
import com.example.proyecto2_android.models.Usuario
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path
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

    @PUT("postulantes/{id}")
    suspend fun actualizarPostulante(
        @Path("id") id: Int,
        @Body postulante: PostulanteRequest
    ): Response<Any>

    @GET("corregimientos/por-distrito/{codigo}")
    suspend fun getCorregimientosPorDistrito(@Path("codigo") codigo: String): Response<List<Corregimiento>>

    // Documentos
    @GET("documentos-postulante")
    suspend fun getDocumentos(): Response<List<DocumentoPostulante>>

    @POST("documentos-postulante")
    suspend fun crearDocumento(@Body doc: DocumentoPostulante): Response<DocumentoPostulante>

    @DELETE("documentos-postulante/{id}")
    suspend fun eliminarDocumento(@Path("id") id: Int): Response<Any>

    // Dropdowns
    @GET("instituciones")
    suspend fun getInstituciones(): Response<List<Institucion>>

    @GET("grados-academicos")
    suspend fun getGradosAcademicosDoc(): Response<List<GradoAcademicoDocumento>>

    @GET("rutas-documento")
    suspend fun getRutas(): Response<List<RutaDocumento>>

    @POST("rutas-documento")
    suspend fun crearRuta(@Body ruta: RutaDocumento): Response<RutaDocumento>

    @DELETE("rutas-documento/{id}")
    suspend fun eliminarRuta(@Path("id") id: Int): Response<Any>

    @GET("documentos-postulante/por-usuario/{id}")
    suspend fun getDocumentosPorUsuario(@Path("id") id: Int): Response<List<DocumentoPostulante>>

    @POST("usuarios/{id}/cambiar-contrasena")
    suspend fun cambiarContrasena(
        @Path("id") id: Int,
        @Body body: CambiarContrasenaRequest
    ): Response<Map<String, String>>

    @PUT("usuarios/{id}/cambiar-usuario")
    suspend fun cambiarUsuario(
        @Path("id") id: Int,
        @Body body: Map<String, String>
    ): Response<Map<String, String>>

    @PUT("usuarios/{id}/cambiar-correo")
    suspend fun cambiarCorreo(
        @Path("id") id: Int,
        @Body body: Map<String, String>
    ): Response<Map<String, String>>
}