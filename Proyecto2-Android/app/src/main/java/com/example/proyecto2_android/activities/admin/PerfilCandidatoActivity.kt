package com.example.proyecto2_android.activities.admin

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.adapters.DocumentosCandidatoAdapter
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.activities.network.RetrofitClient
import com.example.proyecto2_android.models.DocumentoPostulante
import com.example.proyecto2_android.models.GradoAcademicoDocumento
import com.example.proyecto2_android.models.Institucion
import de.hdodenhof.circleimageview.CircleImageView
import kotlinx.coroutines.async
import kotlinx.coroutines.launch
import java.io.File

class PerfilCandidatoActivity : AppCompatActivity() {

    private lateinit var ivBack: ImageView
    private lateinit var ivAvatar: CircleImageView
    private lateinit var tvNombre: TextView
    private lateinit var tvPosicion: TextView
    private lateinit var tabInfoPersonal: LinearLayout
    private lateinit var tabDocumentos: LinearLayout
    private lateinit var viewIndicatorInfo: View
    private lateinit var viewIndicatorDocs: View
    private lateinit var layoutInfoPersonal: LinearLayout
    private lateinit var layoutDocumentos: LinearLayout
    private lateinit var rvDocumentos: RecyclerView
    private lateinit var tvTotalArchivos: TextView
    private lateinit var progressBar: ProgressBar

    private var provinciaMap = mutableMapOf<String, String>()
    private var distritoMap = mutableMapOf<String, String>()

    private var idPostulante: Int = 0
    private var documentosList = mutableListOf<DocumentoPostulante>()
    private lateinit var documentosConRuta: MutableList<Pair<DocumentoPostulante, String>>

    private var estadoCivilMap = mutableMapOf<Int, String>()
    private var rangoAcademicoMap = mutableMapOf<Int, String>()
    private var tipoSangreMap = mutableMapOf<Int, String>()

    private var listaGrados = mutableListOf<GradoAcademicoDocumento>()
    private var listaInstituciones = mutableListOf<Institucion>()
    private var listaProvincias = mutableListOf<String>()

    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_perfil_candidato)

        initViews()
        recibirDatos()
        setupTabs()
        cargarCatalogos()
    }

    private fun initViews() {
        ivBack = findViewById(R.id.ivBack)
        ivAvatar = findViewById(R.id.ivAvatar)
        tvNombre = findViewById(R.id.tvNombreCandidato)
        tvPosicion = findViewById(R.id.tvPosicionCandidato)
        tabInfoPersonal = findViewById(R.id.tabInfoPersonal)
        tabDocumentos = findViewById(R.id.tabDocumentos)
        viewIndicatorInfo = findViewById(R.id.viewIndicatorInfo)
        viewIndicatorDocs = findViewById(R.id.viewIndicatorDocs)
        layoutInfoPersonal = findViewById(R.id.layoutInfoPersonal)
        layoutDocumentos = findViewById(R.id.layoutDocumentos)
        rvDocumentos = findViewById(R.id.rvDocumentos)
        tvTotalArchivos = findViewById(R.id.tvTotalArchivos)
        progressBar = findViewById(R.id.progressBarPerfil)

        // Ocultar contenido hasta que cargue
        layoutInfoPersonal.visibility = View.INVISIBLE
        layoutDocumentos.visibility = View.GONE

        ivBack.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }
    }

    private fun recibirDatos() {
        idPostulante = intent.getIntExtra("idPostulante", 0)
        val nombre = intent.getStringExtra("nombre") ?: "Candidato"
        tvNombre.text = nombre
        tvPosicion.text = "Postulante"
    }

    private suspend fun cargarCatalogosUbicacion() {
        try {
            val provResponse = api.getProvincias()
            if (provResponse.isSuccessful) {
                provResponse.body()?.forEach { provincia ->
                    provinciaMap[provincia.codigo_provincia] = provincia.nombre_provincia
                }
            }

            val distResponse = api.getDistritos()
            if (distResponse.isSuccessful) {
                distResponse.body()?.forEach { distrito ->
                    distritoMap[distrito.codigo_distrito.toString().padStart(4, '0')] =
                        distrito.nombre_distrito
                }
            }
        } catch (e: Exception) {
            android.util.Log.e("UBICACION_ERROR", "Error: ${e.message}")
        }
    }

    private fun cargarCatalogos() {
        progressBar.visibility = View.VISIBLE

        lifecycleScope.launch {
            try {
                cargarCatalogosUbicacion()

                val estadoCivilDeferred = async {
                    try { api.getEstadosCiviles() } catch (e: Exception) { null }
                }
                val rangoAcademicoDeferred = async {
                    try { api.getRangosAcademicos() } catch (e: Exception) { null }
                }
                val tipoSangreDeferred = async {
                    try { api.getTiposSangre() } catch (e: Exception) { null }
                }
                val postulanteDeferred = async {
                    try { api.getPostulantePorId(idPostulante) } catch (e: Exception) { null }
                }
                val gradosDeferred = async {
                    try { api.getGradosAcademicosDoc() } catch (e: Exception) { null }
                }
                val institucionesDeferred = async {
                    try { api.getInstituciones() } catch (e: Exception) { null }
                }
                val provinciasDeferred = async {
                    try { api.getProvincias() } catch (e: Exception) { null }
                }

                val estadoCivilResponse = estadoCivilDeferred.await()
                val rangoAcademicoResponse = rangoAcademicoDeferred.await()
                val tipoSangreResponse = tipoSangreDeferred.await()
                val postulanteResponse = postulanteDeferred.await()
                val gradosResponse = gradosDeferred.await()
                val institucionesResponse = institucionesDeferred.await()
                val provinciasResponse = provinciasDeferred.await()

                gradosResponse?.takeIf { it.isSuccessful }?.body()?.let { listaGrados.addAll(it) }
                institucionesResponse?.takeIf { it.isSuccessful }?.body()?.let { listaInstituciones.addAll(it) }
                provinciasResponse?.takeIf { it.isSuccessful }?.body()?.let {
                    listaProvincias.addAll(it.map { p -> p.nombre_provincia })
                }

                estadoCivilResponse?.takeIf { it.isSuccessful }?.body()?.forEach {
                    estadoCivilMap[it.idEstadoCivil] = it.nombreEstadoCiv
                }
                rangoAcademicoResponse?.takeIf { it.isSuccessful }?.body()?.forEach {
                    rangoAcademicoMap[it.idRangoEdu] = it.nombreRangoEdu
                }
                tipoSangreResponse?.takeIf { it.isSuccessful }?.body()?.forEach {
                    tipoSangreMap[it.idTipoSangre] = it.nombreTipoSangre
                }

                postulanteResponse?.takeIf { it.isSuccessful }?.body()?.let {
                    cargarDatosPersonales(it)
                }

                layoutInfoPersonal.visibility = View.VISIBLE
                progressBar.visibility = View.GONE

                cargarDocumentosDelPostulante()

            } catch (e: Exception) {
                progressBar.visibility = View.GONE
                layoutInfoPersonal.visibility = View.VISIBLE
                Toast.makeText(this@PerfilCandidatoActivity, "Error: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun cargarDatosPersonales(postulante: Map<String, Any>) {
        findViewById<TextView>(R.id.tvFirstName).text = postulante["nombre"] as? String ?: "-"
        findViewById<TextView>(R.id.tvMiddleName).text = postulante["nombre2"] as? String ?: "-"
        findViewById<TextView>(R.id.tvLastName).text = postulante["apellido"] as? String ?: "-"
        findViewById<TextView>(R.id.tvSecondLastName).text = postulante["apellido2"] as? String ?: "-"

        val prefijo = postulante["prefijo"] as? String ?: ""
        val tomo = postulante["tomo"] as? String ?: ""
        val asiento = postulante["asiento"] as? String ?: ""
        findViewById<TextView>(R.id.tvCedula).text =
            if (prefijo.isNotEmpty() || tomo.isNotEmpty() || asiento.isNotEmpty()) {
                "$prefijo-$tomo-$asiento"
            } else {
                "No registrada"
            }

        val genero = (postulante["genero"] as? Number)?.toInt() ?: 0
        findViewById<TextView>(R.id.tvGender).text = when (genero) {
            1 -> "Masculino"
            2 -> "Femenino"
            3 -> "Otro"
            else -> "No especificado"
        }

        val fechaNacimiento = postulante["fechaNacimiento"] as? String ?: ""
        findViewById<TextView>(R.id.tvBirthDate).text = formatFecha(fechaNacimiento)

        val estadoCivilId = (postulante["estadoCivil"] as? Number)?.toInt() ?: 0
        findViewById<TextView>(R.id.tvMaritalStatus).text =
            estadoCivilMap[estadoCivilId] ?: "No especificado"

        val tipoSangreId = (postulante["tipoSangre"] as? Number)?.toInt() ?: 0
        findViewById<TextView>(R.id.tvBloodType).text =
            tipoSangreMap[tipoSangreId] ?: "No especificado"

        val rangoAcademicoId = (postulante["rangoAcademico"] as? Number)?.toInt() ?: 0
        findViewById<TextView>(R.id.tvAcademicLevel).text =
            rangoAcademicoMap[rangoAcademicoId] ?: "No especificado"

        val codProv = postulante["codigo_provincia"] as? String ?: ""
        val codDist = postulante["codigo_distrito"] as? String ?: ""
        val codCorr = postulante["codigo_corregimiento"] as? String ?: ""
        val codDistNormalizado = codDist.padStart(4, '0')

        findViewById<TextView>(R.id.tvProvince).text = provinciaMap[codProv] ?: codProv
        findViewById<TextView>(R.id.tvDistrict).text = distritoMap[codDistNormalizado] ?: codDist

        // Corregimiento: buscar por código directamente en la API
        findViewById<TextView>(R.id.tvCorregimiento).text = codCorr
        lifecycleScope.launch {
            try {
                val response = api.getCorregimientoPorCodigo(codCorr)
                if (response.isSuccessful) {
                    val nombre = response.body()?.nombre_corregimiento ?: codCorr
                    findViewById<TextView>(R.id.tvCorregimiento).text = nombre
                }
            } catch (e: Exception) {
                // Muestra el código como fallback, ya está seteado arriba
            }
        }

        findViewById<TextView>(R.id.tvUrbanization).text = postulante["comunidad"] as? String ?: "-"
        findViewById<TextView>(R.id.tvStreet).text = postulante["calle"] as? String ?: "-"
        findViewById<TextView>(R.id.tvHouseBuilding).text = postulante["casa"] as? String ?: "-"
        findViewById<TextView>(R.id.tvAdditionalDetails).text =
            postulante["detalleDireccion"] as? String ?: "-"

        findViewById<TextView>(R.id.tvPrimaryPhone).text = postulante["telefono"] as? String ?: "-"
        findViewById<TextView>(R.id.tvSecondaryPhone).text = postulante["telefono2"] as? String ?: "-"
        findViewById<TextView>(R.id.tvPrimaryCell).text = postulante["celular"] as? String ?: "-"
        findViewById<TextView>(R.id.tvSecondaryCell).text = postulante["celular2"] as? String ?: "-"
        findViewById<TextView>(R.id.tvEmail).text = postulante["correoPostulante"] as? String ?: "-"

        findViewById<TextView>(R.id.tvVacancyApplied).text = "Desarrollador Android"
    }

    private fun cargarDocumentosDelPostulante() {
        lifecycleScope.launch {
            try {
                val response = api.getDocumentosPorPostulante(idPostulante)

                if (response.isSuccessful) {
                    val documentos = response.body() ?: emptyList()
                    documentosList = documentos.toMutableList()

                    documentosConRuta = mutableListOf()
                    val rutasResponse = api.getRutas()
                    val rutas = rutasResponse.body() ?: emptyList()

                    for (doc in documentosList) {
                        val ruta =
                            rutas.firstOrNull { it.idDocumentoPostulante == doc.idDocumentoPostulante }
                        if (ruta != null) {
                            val file = File(ruta.ruta)
                            if (file.exists()) {
                                documentosConRuta.add(Pair(doc, ruta.ruta))
                            }
                        }
                    }

                    tvTotalArchivos.text = "${documentosConRuta.size} archivo(s)"
                    setupRecyclerView()
                } else {
                    tvTotalArchivos.text = "0 archivos"
                }
            } catch (e: Exception) {
                tvTotalArchivos.text = "Error al cargar"
            }
        }
    }

    private fun setupRecyclerView() {
        rvDocumentos.layoutManager = LinearLayoutManager(this)
        rvDocumentos.adapter = DocumentosCandidatoAdapter(
            documentos = documentosConRuta,
            onVer = { ruta -> verDocumento(ruta) },
            onDescargar = { ruta -> descargarDocumento(ruta) },
            onDetalle = { doc -> mostrarDetalleDocumento(doc) }
        )
    }

    private fun verDocumento(ruta: String) {
        val file = File(ruta)
        if (!file.exists()) {
            Toast.makeText(this, "El archivo no existe", Toast.LENGTH_SHORT).show()
            return
        }
        val uri = androidx.core.content.FileProvider.getUriForFile(
            this, "${packageName}.provider", file
        )
        val intent = Intent(Intent.ACTION_VIEW).apply {
            setDataAndType(uri, "application/pdf")
            addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
        }
        startActivity(Intent.createChooser(intent, "Abrir PDF con..."))
    }

    private fun descargarDocumento(ruta: String) {
        val origen = File(ruta)
        if (!origen.exists()) {
            Toast.makeText(this, "El archivo no existe", Toast.LENGTH_SHORT).show()
            return
        }
        Toast.makeText(this, "Descargando: ${origen.name}", Toast.LENGTH_SHORT).show()
    }

    private fun formatFecha(fecha: String): String {
        if (fecha.isEmpty() || fecha == "null") return "No registrada"
        return try {
            val partes = fecha.split("-")
            if (partes.size == 3) "${partes[2]}/${partes[1]}/${partes[0]}" else fecha
        } catch (e: Exception) {
            fecha
        }
    }

    private fun setupTabs() {
        tabInfoPersonal.setOnClickListener { cambiarTab(0) }
        tabDocumentos.setOnClickListener { cambiarTab(1) }
    }

    private fun cambiarTab(index: Int) {
        when (index) {
            0 -> {
                layoutInfoPersonal.visibility = View.VISIBLE
                layoutDocumentos.visibility = View.GONE
                viewIndicatorInfo.visibility = View.VISIBLE
                viewIndicatorDocs.visibility = View.INVISIBLE
                tabInfoPersonal.setBackgroundColor(ContextCompat.getColor(this, R.color.surface))
                tabDocumentos.setBackgroundColor(ContextCompat.getColor(this, R.color.surface))
            }
            1 -> {
                layoutInfoPersonal.visibility = View.GONE
                layoutDocumentos.visibility = View.VISIBLE
                viewIndicatorInfo.visibility = View.INVISIBLE
                viewIndicatorDocs.visibility = View.VISIBLE
                tabInfoPersonal.setBackgroundColor(ContextCompat.getColor(this, R.color.surface))
                tabDocumentos.setBackgroundColor(ContextCompat.getColor(this, R.color.surface))
            }
        }
    }

    private fun mostrarDetalleDocumento(doc: DocumentoPostulante) {
        val nombreGrado = listaGrados
            .firstOrNull { it.idGradoEst == doc.idGradoEst }
            ?.nombreGradoEst ?: "Desconocido"

        val nombreInstitucion = if (doc.otraInstitucionn == 1)
            doc.nombreOtraInstitucion ?: "Otra institución"
        else
            listaInstituciones
                .firstOrNull { it.idInstitucion == doc.institucion }
                ?.nombreInstitucion ?: "Desconocida"

        val provPos = doc.codigo_provincia.toIntOrNull() ?: 0
        val nombreProvincia = if (provPos > 0 && provPos <= listaProvincias.size)
            listaProvincias[provPos - 1]
        else "Desconocida"

        val mensaje = """
        📄 Título: ${doc.titulo}
        
        🎓 Grado: $nombreGrado
        🏛️ Institución: $nombreInstitucion
        🗺️ Provincia: $nombreProvincia
        
        📅 Inicio: ${doc.fechaInicio}
        📅 Finalización: ${doc.fechaFinaizacion}
        📅 Emisión: ${doc.fechaEmision}
        
        ⏱️ Total de horas: ${doc.totalHoras}
    """.trimIndent()

        android.app.AlertDialog.Builder(this)
            .setTitle("Detalle del Documento")
            .setMessage(mensaje)
            .setPositiveButton("Cerrar", null)
            .show()
    }
}