package com.example.proyecto2_android.activities.postulante

import android.app.AlertDialog
import android.app.DatePickerDialog
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.provider.OpenableColumns
import android.view.MenuItem
import android.view.View
import android.widget.*
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.AjustesActivity
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.activities.network.RetrofitClient
import com.example.proyecto2_android.models.DocumentoPostulante
import com.example.proyecto2_android.models.GradoAcademicoDocumento
import com.example.proyecto2_android.models.Institucion
import com.example.proyecto2_android.adapters.DocumentoAdapter
import com.example.proyecto2_android.models.RutaDocumento
import com.google.android.material.bottomnavigation.BottomNavigationView
import com.google.android.material.button.MaterialButton
import kotlinx.coroutines.launch
import java.io.File
import java.io.FileOutputStream
import java.util.Calendar

class DocumentosActivity : AppCompatActivity() {

    private lateinit var bottomNav: BottomNavigationView
    private lateinit var spinnerTipo: Spinner
    private lateinit var spinnerInstitucion: Spinner
    private lateinit var spinnerProvincia: Spinner
    private lateinit var checkInstitucionCustom: CheckBox
    private lateinit var etInstitucionCustom: EditText
    private lateinit var etTituloDocumento: EditText
    private lateinit var etTotalHoras: EditText
    private lateinit var tvFechaInicio: TextView
    private lateinit var tvFechaFin: TextView
    private lateinit var tvFechaEmision: TextView
    private lateinit var layoutFechaInicio: LinearLayout
    private lateinit var layoutFechaFin: LinearLayout
    private lateinit var layoutFechaEmision: LinearLayout
    private lateinit var layoutUploadPdf: LinearLayout
    private lateinit var tvUploadLabel: TextView
    private lateinit var btnSubirDocumento: MaterialButton
    private lateinit var layoutVacio: LinearLayout
    private lateinit var rvDocumentos: RecyclerView

    private val api: ApiService by lazy { RetrofitClient.instance.create(ApiService::class.java) }

    private var listaInstituciones: List<Institucion> = emptyList()
    private var listaGrados: List<GradoAcademicoDocumento> = emptyList()
    private var listaProvincias: List<String> = emptyList()
    private val documentos: MutableList<DocumentoPostulante> = mutableListOf()
    private lateinit var adapter: DocumentoAdapter

    private var pdfUri: Uri? = null
    private var pdfRutaLocal: String? = null

    private val seleccionarPdf = registerForActivityResult(
        ActivityResultContracts.GetContent()
    ) { uri ->
        if (uri == null) return@registerForActivityResult
        val mime = contentResolver.getType(uri)
        if (mime != "application/pdf") {
            Toast.makeText(this, "Solo se permiten archivos PDF", Toast.LENGTH_SHORT).show()
            return@registerForActivityResult
        }
        val tamaño = obtenerTamaño(uri)
        if (tamaño > 10 * 1024 * 1024) {
            Toast.makeText(this, "El archivo no puede superar los 10MB", Toast.LENGTH_SHORT).show()
            return@registerForActivityResult
        }
        pdfUri = uri
        val nombre = obtenerNombreArchivo(uri)
        tvUploadLabel.text = nombre
        tvUploadLabel.setTextColor(getColor(R.color.text_primary))
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_documentos)

        bindViews()
        setupRecyclerView()
        setupDatePickers()
        setupCheckbox()
        setupUpload()
        setupBottomNav()
        setupBoton()
        cargarDatosApi()
    }

    private fun bindViews() {
        bottomNav              = findViewById(R.id.bottomNavPostulante)
        spinnerTipo            = findViewById(R.id.spinnerTipo)
        spinnerInstitucion     = findViewById(R.id.spinnerInstitucion)
        spinnerProvincia       = findViewById(R.id.spinnerProvincia)
        checkInstitucionCustom = findViewById(R.id.checkInstitucionCustom)
        etInstitucionCustom    = findViewById(R.id.etInstitucionCustom)
        etTituloDocumento      = findViewById(R.id.etTituloDocumento)
        etTotalHoras           = findViewById(R.id.etTotalHoras)
        tvFechaInicio          = findViewById(R.id.tvFechaInicio)
        tvFechaFin             = findViewById(R.id.tvFechaFin)
        tvFechaEmision         = findViewById(R.id.tvFechaEmision)
        layoutFechaInicio      = findViewById(R.id.etFechaInicio)
        layoutFechaFin         = findViewById(R.id.etFechaFin)
        layoutFechaEmision     = findViewById(R.id.etFechaEmision)
        layoutUploadPdf        = findViewById(R.id.layoutUploadPdf)
        tvUploadLabel          = findViewById(R.id.tvUploadLabel)
        btnSubirDocumento      = findViewById(R.id.btnSubirDocumento)
        layoutVacio            = findViewById(R.id.layoutVacio)
        rvDocumentos           = findViewById(R.id.rvDocumentos)
    }

    private fun setupRecyclerView() {
        adapter = DocumentoAdapter(
            documentos,
            onVer       = { doc -> verDocumento(doc) },
            onDescargar = { doc -> descargarDocumento(doc) },
            onEliminar  = { doc -> confirmarEliminar(doc) },
            onDetalle   = { doc -> mostrarDetalleDocumento(doc) }
        )
        rvDocumentos.layoutManager = LinearLayoutManager(this)
        rvDocumentos.adapter = adapter
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

        AlertDialog.Builder(this)
            .setTitle("Detalle del Documento")
            .setMessage(mensaje)
            .setPositiveButton("Cerrar", null)
            .show()
    }

    private fun cargarDatosApi() {
        lifecycleScope.launch {
            try {
                val instResp = api.getInstituciones()
                if (instResp.isSuccessful) {
                    listaInstituciones = instResp.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + listaInstituciones.map { it.nombreInstitucion }
                    spinnerInstitucion.adapter = ArrayAdapter(this@DocumentosActivity,
                        android.R.layout.simple_spinner_item, nombres)
                        .apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                }
            } catch (e: Exception) {
                android.util.Log.e("INST_ERROR", "Detalle: ${e.javaClass.name} - ${e.message}", e)
                Toast.makeText(this@DocumentosActivity, "Error inst: ${e.javaClass.simpleName}", Toast.LENGTH_SHORT).show()
            }

            try {
                val gradoResp = api.getGradosAcademicosDoc()
                if (gradoResp.isSuccessful) {
                    listaGrados = gradoResp.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + listaGrados.map { it.nombreGradoEst }
                    spinnerTipo.adapter = ArrayAdapter(this@DocumentosActivity,
                        android.R.layout.simple_spinner_item, nombres)
                        .apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                }
            } catch (e: Exception) {
                Toast.makeText(this@DocumentosActivity, "Error grados: ${e.message}", Toast.LENGTH_SHORT).show()
            }

            try {
                val provResp = api.getProvincias()
                if (provResp.isSuccessful) {
                    val provincias = provResp.body() ?: emptyList()
                    listaProvincias = provincias.map { it.nombre_provincia } // ← guarda los nombres
                    val nombres = listOf("Seleccione...") + listaProvincias
                    spinnerProvincia.adapter = ArrayAdapter(this@DocumentosActivity,
                        android.R.layout.simple_spinner_item, nombres)
                        .apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                }
            } catch (e: Exception) {
                Toast.makeText(this@DocumentosActivity, "Error provincias: ${e.message}", Toast.LENGTH_SHORT).show()
            }

            try {
                val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
                val idUsuario = prefs.getInt("id_usuario", 0)
                val docResp = api.getDocumentosPorUsuario(idUsuario)
                if (docResp.isSuccessful) {
                    documentos.addAll(docResp.body() ?: emptyList())
                    adapter.notifyDataSetChanged()
                    actualizarVisibilidad()
                }
            } catch (e: Exception) {
                Toast.makeText(this@DocumentosActivity, "Error documentos: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun setupDatePickers() {
        fun showDatePicker(target: TextView) {
            val cal = Calendar.getInstance()
            DatePickerDialog(this, { _, year, month, day ->
                target.text = "%02d/%02d/%d".format(month + 1, day, year)
                target.setTextColor(getColor(R.color.text_primary))
            }, cal.get(Calendar.YEAR), cal.get(Calendar.MONTH), cal.get(Calendar.DAY_OF_MONTH)).show()
        }
        layoutFechaInicio.setOnClickListener  { showDatePicker(tvFechaInicio) }
        layoutFechaFin.setOnClickListener     { showDatePicker(tvFechaFin) }
        layoutFechaEmision.setOnClickListener { showDatePicker(tvFechaEmision) }
    }

    private fun setupCheckbox() {
        checkInstitucionCustom.setOnCheckedChangeListener { _, isChecked ->
            spinnerInstitucion.visibility  = if (isChecked) View.GONE  else View.VISIBLE
            etInstitucionCustom.visibility = if (isChecked) View.VISIBLE else View.GONE
        }
    }

    private fun setupUpload() {
        layoutUploadPdf.setOnClickListener {
            seleccionarPdf.launch("application/pdf")
        }
    }

    private fun setupBoton() {
        btnSubirDocumento.setOnClickListener {
            if (validarCampos()) guardarDocumento()
        }
    }

    private fun guardarDocumento() {
        if (listaGrados.isEmpty() || listaInstituciones.isEmpty()) {
            Toast.makeText(this, "Los datos aún están cargando", Toast.LENGTH_SHORT).show()
            return
        }

        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val idUsuario = prefs.getInt("id_usuario", 0)
        val gradoPos = spinnerTipo.selectedItemPosition
        val provPos  = spinnerProvincia.selectedItemPosition

        val instId = if (!checkInstitucionCustom.isChecked)
            listaInstituciones[spinnerInstitucion.selectedItemPosition - 1].idInstitucion
        else 0

        val doc = DocumentoPostulante(
            idGradoEst            = listaGrados[gradoPos - 1].idGradoEst,
            idPostulante          = idUsuario,
            codigo_provincia      = provPos.toString(),
            titulo                = etTituloDocumento.text.toString().trim(),
            institucion           = instId,
            otraInstitucionn      = if (checkInstitucionCustom.isChecked) 1 else 0,
            nombreOtraInstitucion = if (checkInstitucionCustom.isChecked)
                etInstitucionCustom.text.toString().trim() else null,
            fechaInicio           = formatearFechaMySQL(tvFechaInicio.text.toString()),
            fechaFinaizacion      = formatearFechaMySQL(tvFechaFin.text.toString()),
            fechaEmision          = formatearFechaMySQL(tvFechaEmision.text.toString()),
            totalHoras            = etTotalHoras.text.toString().toInt()
        )

        lifecycleScope.launch {
            try {
                val docResp = api.crearDocumento(doc)
                if (!docResp.isSuccessful) {
                    Toast.makeText(this@DocumentosActivity,
                        "Error al guardar el documento", Toast.LENGTH_SHORT).show()
                    return@launch
                }
                val docCreado = docResp.body()!!

                pdfUri?.let { uri ->
                    val nombre  = obtenerNombreArchivo(uri)
                    val destino = File(filesDir, "documentos/$nombre")
                    destino.parentFile?.mkdirs()
                    contentResolver.openInputStream(uri)?.use { input ->
                        FileOutputStream(destino).use { output -> input.copyTo(output) }
                    }
                    val rutaObj = RutaDocumento(
                        idDocumentoPostulante = docCreado.idDocumentoPostulante,
                        ruta = destino.absolutePath
                    )
                    api.crearRuta(rutaObj)
                }

                documentos.add(docCreado)
                adapter.notifyItemInserted(documentos.size - 1)
                actualizarVisibilidad()
                limpiarFormulario()
                Toast.makeText(this@DocumentosActivity,
                    "Documento subido correctamente", Toast.LENGTH_SHORT).show()

            } catch (e: Exception) {
                Toast.makeText(this@DocumentosActivity,
                    "Error: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun verDocumento(doc: DocumentoPostulante) {
        lifecycleScope.launch {
            try {
                val rutasResp = api.getRutas()
                val ruta = rutasResp.body()
                    ?.firstOrNull { it.idDocumentoPostulante == doc.idDocumentoPostulante }
                    ?.ruta ?: run {
                    Toast.makeText(this@DocumentosActivity,
                        "Archivo no disponible", Toast.LENGTH_SHORT).show()
                    return@launch
                }
                val file = File(ruta)
                if (!file.exists()) {
                    Toast.makeText(this@DocumentosActivity,
                        "El archivo no existe en este dispositivo", Toast.LENGTH_SHORT).show()
                    return@launch
                }
                val uri = androidx.core.content.FileProvider.getUriForFile(
                    this@DocumentosActivity, "${packageName}.provider", file)
                val intent = Intent(Intent.ACTION_VIEW).apply {
                    setDataAndType(uri, "application/pdf")
                    addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
                }
                startActivity(Intent.createChooser(intent, "Abrir PDF con..."))
            } catch (e: Exception) {
                Toast.makeText(this@DocumentosActivity, "Error al abrir", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun descargarDocumento(doc: DocumentoPostulante) {
        lifecycleScope.launch {
            try {
                val rutasResp = api.getRutas()
                val ruta = rutasResp.body()
                    ?.firstOrNull { it.idDocumentoPostulante == doc.idDocumentoPostulante }
                    ?.ruta ?: run {
                    Toast.makeText(this@DocumentosActivity,
                        "Archivo no disponible", Toast.LENGTH_SHORT).show()
                    return@launch
                }

                val origen = File(ruta)
                if (!origen.exists()) {
                    Toast.makeText(this@DocumentosActivity,
                        "El archivo no existe en este dispositivo", Toast.LENGTH_SHORT).show()
                    return@launch
                }

                val resolver = contentResolver
                val contentValues = android.content.ContentValues().apply {
                    put(android.provider.MediaStore.Downloads.DISPLAY_NAME, origen.name)
                    put(android.provider.MediaStore.Downloads.MIME_TYPE, "application/pdf")
                    put(android.provider.MediaStore.Downloads.IS_PENDING, 1)
                }

                val uri = resolver.insert(
                    android.provider.MediaStore.Downloads.EXTERNAL_CONTENT_URI,
                    contentValues
                ) ?: run {
                    Toast.makeText(this@DocumentosActivity,
                        "No se pudo crear el archivo en Descargas", Toast.LENGTH_SHORT).show()
                    return@launch
                }

                resolver.openOutputStream(uri)?.use { output ->
                    origen.inputStream().use { input -> input.copyTo(output) }
                }

                contentValues.clear()
                contentValues.put(android.provider.MediaStore.Downloads.IS_PENDING, 0)
                resolver.update(uri, contentValues, null, null)

                Toast.makeText(this@DocumentosActivity,
                    "Guardado en Descargas: ${origen.name}", Toast.LENGTH_LONG).show()

            } catch (e: Exception) {
                android.util.Log.e("DESCARGA_ERROR", "${e.javaClass.name}: ${e.message}", e)
                Toast.makeText(this@DocumentosActivity,
                    "Error al descargar: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun confirmarEliminar(doc: DocumentoPostulante) {
        AlertDialog.Builder(this)
            .setTitle("Eliminar documento")
            .setMessage("¿Estás seguro de que deseas eliminar \"${doc.titulo}\"?")
            .setPositiveButton("Eliminar") { _, _ -> eliminarDocumento(doc) }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun eliminarDocumento(doc: DocumentoPostulante) {
        lifecycleScope.launch {
            try {
                val rutasResp = api.getRutas()
                if (rutasResp.isSuccessful) {
                    val ruta = rutasResp.body()
                        ?.firstOrNull { it.idDocumentoPostulante == doc.idDocumentoPostulante }
                    ruta?.let {
                        File(it.ruta).delete()
                        api.eliminarRuta(it.idRutadoc)
                    }
                }

                val resp = api.eliminarDocumento(doc.idDocumentoPostulante)
                if (resp.isSuccessful) {
                    val pos = documentos.indexOf(doc)
                    if (pos >= 0) adapter.eliminar(pos)
                    actualizarVisibilidad()
                    Toast.makeText(this@DocumentosActivity,
                        "Documento eliminado", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@DocumentosActivity,
                    "Error al eliminar", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun actualizarVisibilidad() {
        if (documentos.isEmpty()) {
            layoutVacio.visibility  = View.VISIBLE
            rvDocumentos.visibility = View.GONE
        } else {
            layoutVacio.visibility  = View.GONE
            rvDocumentos.visibility = View.VISIBLE
        }
    }

    private fun obtenerNombreArchivo(uri: Uri): String {
        var nombre = "documento.pdf"
        contentResolver.query(uri, null, null, null, null)?.use { cursor ->
            val idx = cursor.getColumnIndex(OpenableColumns.DISPLAY_NAME)
            if (cursor.moveToFirst() && idx >= 0) nombre = cursor.getString(idx)
        }
        return nombre
    }

    private fun obtenerTamaño(uri: Uri): Long {
        var size = 0L
        contentResolver.query(uri, null, null, null, null)?.use { cursor ->
            val idx = cursor.getColumnIndex(OpenableColumns.SIZE)
            if (cursor.moveToFirst() && idx >= 0) size = cursor.getLong(idx)
        }
        return size
    }

    private fun formatearFechaMySQL(fecha: String): String {
        val p = fecha.split("/")
        return if (p.size == 3) "${p[2]}-${p[0].padStart(2, '0')}-${p[1].padStart(2, '0')}" else fecha
    }

    private fun validarCampos(): Boolean {
        if (etTituloDocumento.text.isNullOrBlank()) {
            etTituloDocumento.error = "Ingresa el título del documento"
            etTituloDocumento.requestFocus(); return false
        }
        if (spinnerTipo.selectedItemPosition == 0) {
            Toast.makeText(this, "Selecciona el tipo de documento", Toast.LENGTH_SHORT).show()
            return false
        }
        val instOk = if (checkInstitucionCustom.isChecked)
            !etInstitucionCustom.text.isNullOrBlank()
        else spinnerInstitucion.selectedItemPosition != 0
        if (!instOk) {
            Toast.makeText(this, "Ingresa o selecciona una institución", Toast.LENGTH_SHORT).show()
            return false
        }
        if (spinnerProvincia.selectedItemPosition == 0) {
            Toast.makeText(this, "Selecciona una provincia", Toast.LENGTH_SHORT).show()
            return false
        }
        if (tvFechaInicio.text == "mm/dd/yyyy") {
            Toast.makeText(this, "Selecciona la fecha de inicio", Toast.LENGTH_SHORT).show()
            return false
        }
        if (tvFechaFin.text == "mm/dd/yyyy") {
            Toast.makeText(this, "Selecciona la fecha de finalización", Toast.LENGTH_SHORT).show()
            return false
        }
        if (tvFechaEmision.text == "mm/dd/yyyy") {
            Toast.makeText(this, "Selecciona la fecha de emisión", Toast.LENGTH_SHORT).show()
            return false
        }
        if (etTotalHoras.text.isNullOrBlank()) {
            etTotalHoras.error = "Ingresa el total de horas"
            etTotalHoras.requestFocus(); return false
        }
        if (pdfUri == null) {
            Toast.makeText(this, "Selecciona un archivo PDF", Toast.LENGTH_SHORT).show()
            return false
        }
        return true
    }

    private fun limpiarFormulario() {
        etTituloDocumento.text.clear()
        etTotalHoras.text.clear()
        etInstitucionCustom.text.clear()
        spinnerTipo.setSelection(0)
        spinnerInstitucion.setSelection(0)
        spinnerProvincia.setSelection(0)
        tvFechaInicio.text  = "mm/dd/yyyy"
        tvFechaFin.text     = "mm/dd/yyyy"
        tvFechaEmision.text = "mm/dd/yyyy"
        tvFechaInicio.setTextColor(getColor(R.color.text_secondary))
        tvFechaFin.setTextColor(getColor(R.color.text_secondary))
        tvFechaEmision.setTextColor(getColor(R.color.text_secondary))
        checkInstitucionCustom.isChecked = false
        tvUploadLabel.text = "Arrastra o haz clic para subir"
        pdfUri = null
        pdfRutaLocal = null
    }

    private fun setupBottomNav() {
        bottomNav.selectedItemId = R.id.nav_documentos
        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_perfil -> {
                    startActivity(Intent(this, PerfilActivity::class.java).apply {
                        flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT })
                    overridePendingTransition(0, 0); true
                }
                R.id.nav_documentos -> true
                R.id.nav_ajustes -> {
                    startActivity(Intent(this, AjustesActivity::class.java).apply {
                        putExtra("origen", "postulante")
                        flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT })
                    overridePendingTransition(0, 0); true
                }
                else -> false
            }
        }
    }

    override fun onResume() {
        super.onResume()
        bottomNav.selectedItemId = R.id.nav_documentos
    }
}