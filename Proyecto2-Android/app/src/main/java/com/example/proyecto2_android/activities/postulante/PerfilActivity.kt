package com.example.proyecto2_android.activities.postulante

import android.app.DatePickerDialog
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.MenuItem
import android.view.View
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.AjustesActivity
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.activities.network.RetrofitClient
import com.example.proyecto2_android.models.Corregimiento
import com.example.proyecto2_android.models.Distrito
import com.example.proyecto2_android.models.PostulanteRequest
import com.example.proyecto2_android.models.Provincia
import com.google.android.material.bottomnavigation.BottomNavigationView
import com.google.android.material.button.MaterialButton
import kotlinx.coroutines.launch
import java.util.Calendar

class PerfilActivity : AppCompatActivity() {

    // Views del formulario de registro (activity_completa_perfil)
    private lateinit var etPrimerNombre: EditText
    private lateinit var etSegundoNombre: EditText
    private lateinit var etPrimerApellido: EditText
    private lateinit var etSegundoApellido: EditText
    private lateinit var etCedulaProvincia: EditText
    private lateinit var etCedulaTomo: EditText
    private lateinit var etCedulaAsiento: EditText
    private lateinit var spinnerGenero: Spinner
    private lateinit var etFechaNacimiento: EditText
    private lateinit var spinnerEstadoCivil: Spinner
    private lateinit var spinnerTipoSangre: Spinner
    private lateinit var spinnerNivelAcademico: Spinner
    private lateinit var etTelefonoPrimario: EditText
    private lateinit var etTelefonoSecundario: EditText
    private lateinit var etCelularPrimario: EditText
    private lateinit var etCelularSecundario: EditText
    private lateinit var etCorreo: EditText
    private lateinit var spinnerProvincia: Spinner
    private lateinit var spinnerDistrito: Spinner
    private lateinit var spinnerCorregimiento: Spinner
    private lateinit var etUrbanizacion: EditText
    private lateinit var etCalle: EditText
    private lateinit var etCasaEdificio: EditText
    private lateinit var etDetallesAdicionales: EditText
    private lateinit var spinnerVacante: Spinner
    private lateinit var btnEnviar: MaterialButton
    private lateinit var bottomNav: BottomNavigationView
    private var idPostulanteActual: Int = -1
    private lateinit var progressBar: ProgressBar
    private lateinit var scrollView: ScrollView   // referencia al ScrollView del layout

    // Views exclusivas de activity_mi_perfil
    private lateinit var layoutBotones: View
    private lateinit var btnGuardar: Button
    private lateinit var btnDescartar: Button

    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

    private var listaProvincias: List<Provincia> = emptyList()
    private var listaDistritos: List<Distrito> = emptyList()
    private var listaCorregimientos: List<Corregimiento> = emptyList()

    // Datos originales para detectar cambios y para descartar
    private var datosOriginales: Map<String, Any> = emptyMap()
    private var haycambios = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val idUsuario = prefs.getInt("id_usuario", 0)

        lifecycleScope.launch {
            try {
                val response = api.getPostulantes()
                if (response.isSuccessful) {
                    val postulantes = response.body() ?: emptyList()
                    val postulante = postulantes.firstOrNull {
                        it["idUsuario"]?.toString()?.toDoubleOrNull()?.toInt() == idUsuario
                    }

                    if (postulante != null) {
                        idPostulanteActual = (postulante["idPostulante"] as? Number)?.toInt() ?: -1
                        mostrarMiPerfil(postulante, prefs)
                    } else {
                        mostrarFormulario(prefs)
                    }
                } else {
                    mostrarFormulario(prefs)
                }
            } catch (e: Exception) {
                mostrarFormulario(prefs)
            }
        }
    }

    // ──────────────────────────────────────────────
    //  Vista "Mi Perfil" (postulante ya registrado)
    // ──────────────────────────────────────────────
    private fun mostrarMiPerfil(
        datos: Map<String, Any>,
        prefs: android.content.SharedPreferences
    ) {
        setContentView(R.layout.activity_mi_perfil)
        bottomNav = findViewById(R.id.bottomNavPostulante)
        setupBottomNav()

        // Referencias al loading/contenido
        progressBar = findViewById(R.id.progressBar)
        scrollView  = findViewById(R.id.scrollViewPerfil)   // ← pon este id en el ScrollView del XML

        // Mostrar loading, ocultar contenido
        progressBar.visibility = View.VISIBLE
        scrollView.visibility  = View.GONE

        // Bind views del perfil
        etPrimerNombre        = findViewById(R.id.etPrimerNombre)
        etSegundoNombre       = findViewById(R.id.etSegundoNombre)
        etPrimerApellido      = findViewById(R.id.etPrimerApellido)
        etSegundoApellido     = findViewById(R.id.etSegundoApellido)
        etCedulaProvincia     = findViewById(R.id.etCedulaProvincia)
        etCedulaTomo          = findViewById(R.id.etCedulaTomo)
        etCedulaAsiento       = findViewById(R.id.etCedulaAsiento)
        spinnerGenero         = findViewById(R.id.spinnerGenero)
        etFechaNacimiento     = findViewById(R.id.etFechaNacimiento)
        spinnerEstadoCivil    = findViewById(R.id.spinnerEstadoCivil)
        spinnerTipoSangre     = findViewById(R.id.spinnerTipoSangre)
        spinnerNivelAcademico = findViewById(R.id.spinnerNivelAcademico)
        etTelefonoPrimario    = findViewById(R.id.etTelefonoPrimario)
        etTelefonoSecundario  = findViewById(R.id.etTelefonoSecundario)
        etCelularPrimario     = findViewById(R.id.etCelularPrimario)
        etCelularSecundario   = findViewById(R.id.etCelularSecundario)
        etCorreo              = findViewById(R.id.etCorreo)
        spinnerProvincia      = findViewById(R.id.spinnerProvincia)
        spinnerDistrito       = findViewById(R.id.spinnerDistrito)
        spinnerCorregimiento  = findViewById(R.id.spinnerCorregimiento)
        etUrbanizacion        = findViewById(R.id.etUrbanizacion)
        etCalle               = findViewById(R.id.etCalle)
        etCasaEdificio        = findViewById(R.id.etCasaEdificio)
        etDetallesAdicionales = findViewById(R.id.etDetallesAdicionales)
        layoutBotones         = findViewById(R.id.layoutBotones)
        btnGuardar            = findViewById(R.id.btnGuardar)
        btnDescartar          = findViewById(R.id.btnDescartar)

        val tvNombre = findViewById<TextView>(R.id.tvNombreCompleto)
        val tvCorreo = findViewById<TextView>(R.id.tvCorreoPerfil)
        val nombre = "${datos["nombre"] ?: ""} ${datos["nombre2"] ?: ""} ${datos["apellido"] ?: ""} ${datos["apellido2"] ?: ""}".trim()
        tvNombre.text = nombre
        tvCorreo.text = datos["correoPostulante"]?.toString() ?: ""

        datosOriginales = datos

        setupSpinnersEstaticos()
        setupDatePicker()

        lifecycleScope.launch {
            // Cargar datos de la API y poblar campos
            cargarDatosApiMiPerfil()
            poblarCampos(datos)
            registrarListenersCambios()

            // Todo listo → ocultar loading, mostrar contenido
            progressBar.visibility = View.GONE
            scrollView.visibility  = View.VISIBLE
        }

        btnDescartar.setOnClickListener {
            poblarCampos(datosOriginales)
            ocultarBotones()
        }

        btnGuardar.setOnClickListener {
            guardarCambios(prefs)
        }
    }

    /** Rellena todos los EditTexts y Spinners con los datos del mapa recibido */
    private fun poblarCampos(datos: Map<String, Any>) {
        etPrimerNombre.setText(datos["nombre"]?.toString() ?: "")
        etSegundoNombre.setText(datos["nombre2"]?.toString() ?: "")
        etPrimerApellido.setText(datos["apellido"]?.toString() ?: "")
        etSegundoApellido.setText(datos["apellido2"]?.toString() ?: "")
        etCedulaProvincia.setText(datos["prefijo"]?.toString() ?: "")
        etCedulaTomo.setText(datos["tomo"]?.toString() ?: "")
        etCedulaAsiento.setText(datos["asiento"]?.toString() ?: "")
        etTelefonoPrimario.setText(datos["telefono"]?.toString() ?: "")
        etTelefonoSecundario.setText(datos["telefono2"]?.toString() ?: "")
        etCelularPrimario.setText(datos["celular"]?.toString() ?: "")
        etCelularSecundario.setText(datos["celular2"]?.toString() ?: "")
        etCorreo.setText(datos["correoPostulante"]?.toString() ?: "")
        etUrbanizacion.setText(datos["comunidad"]?.toString() ?: "")
        etCalle.setText(datos["calle"]?.toString() ?: "")
        etCasaEdificio.setText(datos["casa"]?.toString() ?: "")

        val detalle = datos["detalleDireccion"]?.toString()
            ?: datos["detalle_direccion"]?.toString() ?: ""
        etDetallesAdicionales.setText(detalle)

        // Fecha: convertir de yyyy-MM-dd a MM/dd/yyyy para mostrar
        val fechaRaw = datos["fechaNacimiento"]?.toString() ?: ""
        etFechaNacimiento.setText(if (fechaRaw.contains("-")) {
            val p = fechaRaw.split("-")
            if (p.size == 3) "${p[1]}/${p[2]}/${p[0]}" else fechaRaw
        } else fechaRaw)

        // Género (1=Masculino, 2=Femenino, 3=Otro)
        val generoIdx = datos["genero"]?.toString()?.toDoubleOrNull()?.toInt() ?: 1
        spinnerGenero.setSelection(generoIdx) // posición 0=Seleccione, 1=Masculino...

        // Spinners numéricos (índice == posición en el spinner incluida la opción "Seleccione")
        val estadoCivilIdx = datos["estadoCivil"]?.toString()?.toDoubleOrNull()?.toInt() ?: 0
        if (estadoCivilIdx < spinnerEstadoCivil.count) spinnerEstadoCivil.setSelection(estadoCivilIdx)

        val tipoSangreIdx = datos["tipoSangre"]?.toString()?.toDoubleOrNull()?.toInt() ?: 0
        if (tipoSangreIdx < spinnerTipoSangre.count) spinnerTipoSangre.setSelection(tipoSangreIdx)

        val nivelAcadIdx = datos["rangoAcademico"]?.toString()?.toDoubleOrNull()?.toInt() ?: 0
        if (nivelAcadIdx < spinnerNivelAcademico.count) spinnerNivelAcademico.setSelection(nivelAcadIdx)

        // ── Cascada provincia → distrito → corregimiento ──
        val codProv = datos["codigo_provincia"]?.toString() ?: ""
        val codDist = datos["codigo_distrito"]?.toString() ?: ""
        val codCorr = datos["codigo_corregimiento"]?.toString() ?: ""

        val provIdx = listaProvincias.indexOfFirst { it.codigo_provincia == codProv }
        if (provIdx < 0) return
        // Desactivamos temporalmente el listener para que no llame a la API
        spinnerProvincia.onItemSelectedListener = null
        // 1. Selecciona provincia (dispara listener que puebla distritos)
        spinnerProvincia.setSelection(provIdx + 1)

        // 2. Esperamos que el listener pinte los distritos, luego seleccionamos
        spinnerProvincia.post {
            // Forzar el adapter del distrito manualmente
            val distFilt = listaDistritos.filter { it.codigo_provincia == codProv }
            val nombresDistrito = listOf("Seleccione...") + distFilt.map { it.nombre_distrito }
            spinnerDistrito.adapter = ArrayAdapter(
                this@PerfilActivity,
                android.R.layout.simple_spinner_item,
                nombresDistrito
            ).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }

            val distIdx = distFilt.indexOfFirst {
                it.codigo_distrito.toString().padStart(4, '0') == codDist
            }
            if (distIdx < 0) {
                restaurarListenerDistrito()
                return@post
            }

            spinnerDistrito.onItemSelectedListener = null
            spinnerDistrito.setSelection(distIdx + 1)

            // Cargar corregimientos y seleccionar
            lifecycleScope.launch {
                try {
                    val response = api.getCorregimientosPorDistrito(codDist)
                    if (response.isSuccessful) {
                        val lista = response.body() ?: emptyList()
                        listaCorregimientos = lista
                        val nombres = listOf("Seleccione...") + lista.map { it.nombre_corregimiento }
                        spinnerCorregimiento.adapter = ArrayAdapter(
                            this@PerfilActivity,
                            android.R.layout.simple_spinner_item,
                            nombres
                        ).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }

                        val corrIdx = lista.indexOfFirst {
                            it.codigo_corregimiento.toString() == codCorr
                        }
                        if (corrIdx >= 0) spinnerCorregimiento.setSelection(corrIdx + 1)
                    }
                } catch (e: Exception) {
                    android.util.Log.e("POBLAR_ERROR", "Error corregimientos: ${e.message}")
                } finally {
                    restaurarListenerDistrito()
                    restaurarListenerProvincia()
                    ocultarBotones()
                    registrarListenersCambios()
                }
            }
        }
    }

    private fun restaurarListenerDistrito() {
        spinnerDistrito.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>, view: View?, pos: Int, id: Long) {
                spinnerCorregimiento.adapter = ArrayAdapter(
                    this@PerfilActivity,
                    android.R.layout.simple_spinner_item,
                    listOf("Seleccione...")
                ).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }

                if (pos == 0) return
                val provPos = spinnerProvincia.selectedItemPosition
                if (provPos == 0) return

                val provincia = listaProvincias[provPos - 1]
                val distFilt = listaDistritos.filter { it.codigo_provincia == provincia.codigo_provincia }
                val distrito = distFilt[pos - 1]
                val codigo = distrito.codigo_distrito.toString().padStart(4, '0')

                lifecycleScope.launch {
                    try {
                        val response = api.getCorregimientosPorDistrito(codigo)
                        if (response.isSuccessful) {
                            val lista = response.body() ?: emptyList()
                            listaCorregimientos = lista
                            val nombres = listOf("Seleccione...") + lista.map { it.nombre_corregimiento }
                            spinnerCorregimiento.adapter = ArrayAdapter(
                                this@PerfilActivity,
                                android.R.layout.simple_spinner_item,
                                nombres
                            ).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                        }
                    } catch (e: Exception) {
                        android.util.Log.e("CORR_ERROR", "Error: ${e.message}")
                    }
                }
            }
            override fun onNothingSelected(parent: AdapterView<*>) {}
        }
    }


    /** Registra TextWatchers y OnItemSelectedListeners para detectar ediciones */
    private fun registrarListenersCambios() {
        val watcher = object : TextWatcher {
            override fun afterTextChanged(s: Editable?) { mostrarBotones() }
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
        }

        listOf(
            etPrimerNombre, etSegundoNombre, etPrimerApellido, etSegundoApellido,
            etCedulaProvincia, etCedulaTomo, etCedulaAsiento,
            etTelefonoPrimario, etTelefonoSecundario,
            etCelularPrimario, etCelularSecundario,
            etCorreo, etUrbanizacion, etCalle, etCasaEdificio, etDetallesAdicionales
        ).forEach { it.addTextChangedListener(watcher) }

        // Para los spinners usamos un flag que ignora la selección inicial
        fun spinnerChangeListener() = object : AdapterView.OnItemSelectedListener {
            private var primeraVez = true
            override fun onItemSelected(p: AdapterView<*>?, v: View?, pos: Int, id: Long) {
                if (primeraVez) { primeraVez = false; return }
                mostrarBotones()
            }
            override fun onNothingSelected(p: AdapterView<*>?) {}
        }

        spinnerGenero.onItemSelectedListener = spinnerChangeListener()
        spinnerEstadoCivil.onItemSelectedListener = spinnerChangeListener()
        spinnerTipoSangre.onItemSelectedListener = spinnerChangeListener()
        spinnerNivelAcademico.onItemSelectedListener = spinnerChangeListener()
        // Nota: spinnerProvincia y spinnerDistrito ya tienen listeners de cascada;
        // se pueden combinar si se desea, pero para simplicidad se registra aparte.
        spinnerCorregimiento.onItemSelectedListener = spinnerChangeListener()
    }

    private fun mostrarBotones() {
        if (layoutBotones.visibility != View.VISIBLE) {
            layoutBotones.visibility = View.VISIBLE
        }
    }

    private fun ocultarBotones() {
        layoutBotones.visibility = View.GONE
    }

    private fun guardarCambios(prefs: android.content.SharedPreferences) {
        val idUsuario = prefs.getInt("id_usuario", 0)
        val provPos = spinnerProvincia.selectedItemPosition
        val distPos = spinnerDistrito.selectedItemPosition
        val corrPos = spinnerCorregimiento.selectedItemPosition

        if (provPos == 0 || distPos == 0 || corrPos == 0) {
            Toast.makeText(this, "Selecciona provincia, distrito y corregimiento", Toast.LENGTH_SHORT).show()
            return
        }

        val provincia = listaProvincias[provPos - 1]
        val distritosFiltrados = listaDistritos.filter { it.codigo_provincia == provincia.codigo_provincia }
        val distrito = distritosFiltrados[distPos - 1]
        val codigoNormalizado = distrito.codigo_distrito.toString().padStart(4, '0')
        val corregimientosFiltrados = listaCorregimientos.filter { it.codigo_distrito == codigoNormalizado }
        val corregimiento = corregimientosFiltrados[corrPos - 1]

        val postulante = PostulanteRequest(
            idUsuario = idUsuario,
            rangoAcademico = spinnerNivelAcademico.selectedItemPosition,
            nombre = etPrimerNombre.text.toString().trim(),
            nombre2 = etSegundoNombre.text.toString().trim(),
            apellido = etPrimerApellido.text.toString().trim(),
            apellido2 = etSegundoApellido.text.toString().trim(),
            prefijo = etCedulaProvincia.text.toString().trim(),
            tomo = etCedulaTomo.text.toString().trim(),
            asiento = etCedulaAsiento.text.toString().trim(),
            genero = spinnerGenero.selectedItemPosition,
            estadoCivil = spinnerEstadoCivil.selectedItemPosition,
            tipoSangre = spinnerTipoSangre.selectedItemPosition,
            fechaNacimiento = formatearFechaParaMySQL(etFechaNacimiento.text.toString().trim()),
            codigo_provincia = provincia.codigo_provincia,
            codigo_distrito = codigoNormalizado,
            codigo_corregimiento = corregimiento.codigo_corregimiento.toString(),
            comunidad = etUrbanizacion.text.toString().trim(),
            calle = etCalle.text.toString().trim(),
            casa = etCasaEdificio.text.toString().trim(),
            detalleDireccion = etDetallesAdicionales.text.toString().trim(),
            celular = etCelularPrimario.text.toString().trim(),
            celular2 = etCelularSecundario.text.toString().trim(),
            telefono = etTelefonoPrimario.text.toString().trim(),
            telefono2 = etTelefonoSecundario.text.toString().trim().ifEmpty { "" },
            correoPostulante = etCorreo.text.toString().trim()
        )

        lifecycleScope.launch {
            try {
                val response = api.actualizarPostulante(idUsuario, postulante)
                if (response.isSuccessful) {
                    Toast.makeText(this@PerfilActivity, "Perfil actualizado correctamente", Toast.LENGTH_SHORT).show()
                    ocultarBotones()
                    // Actualizar nombre en el header
                    findViewById<TextView>(R.id.tvNombreCompleto).text =
                        "${postulante.nombre} ${postulante.nombre2} ${postulante.apellido} ${postulante.apellido2}".trim()
                } else {
                    Toast.makeText(this@PerfilActivity, "Error al guardar perfil", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@PerfilActivity, "Error: ${e.message}", Toast.LENGTH_LONG).show()
            }
        }
    }

    /** Carga los datos de la API (provincias, distritos, corregimientos, etc.) para activity_mi_perfil */
    private suspend fun cargarDatosApiMiPerfil() {
        try {
            val ecResponse = api.getEstadosCiviles()
            if (ecResponse.isSuccessful) {
                val lista = ecResponse.body() ?: emptyList()
                val nombres = listOf("Seleccione...") + lista.map { it.nombreEstadoCiv }
                spinnerEstadoCivil.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, nombres).apply {
                    setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                }
            }

            val tsResponse = api.getTiposSangre()
            if (tsResponse.isSuccessful) {
                val lista = tsResponse.body() ?: emptyList()
                val nombres = listOf("Seleccione...") + lista.map { it.nombreTipoSangre }
                spinnerTipoSangre.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, nombres).apply {
                    setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                }
            }

            val raResponse = api.getRangosAcademicos()
            if (raResponse.isSuccessful) {
                val lista = raResponse.body() ?: emptyList()
                val nombres = listOf("Seleccione...") + lista.map { it.nombreRangoEdu }
                spinnerNivelAcademico.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, nombres).apply {
                    setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                }
            }

            val provResponse = api.getProvincias()
            if (provResponse.isSuccessful) {
                listaProvincias = provResponse.body() ?: emptyList()
                val nombres = listOf("Seleccione...") + listaProvincias.map { it.nombre_provincia }
                spinnerProvincia.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, nombres).apply {
                    setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                }
            }

            val distResponse = api.getDistritos()
            if (distResponse.isSuccessful) listaDistritos = distResponse.body() ?: emptyList()

            val corrResponse = api.getCorregimientos()
            if (corrResponse.isSuccessful) listaCorregimientos = corrResponse.body() ?: emptyList()

        } catch (e: Exception) {
            android.util.Log.e("PERFIL_ERROR", "Error cargando datos API: ${e.message}", e)
        }
    }

    private fun restaurarListenerProvincia() {
        spinnerProvincia.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>, view: View?, pos: Int, id: Long) {
                if (pos == 0) {
                    spinnerDistrito.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                    spinnerCorregimiento.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                    return
                }
                val provincia = listaProvincias[pos - 1]
                val distritosFiltrados = listaDistritos.filter { it.codigo_provincia == provincia.codigo_provincia }
                val nombres = listOf("Seleccione...") + distritosFiltrados.map { it.nombre_distrito }
                spinnerDistrito.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                spinnerCorregimiento.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
            }
            override fun onNothingSelected(parent: AdapterView<*>) {}
        }
    }

    // ──────────────────────────────────────────────
    //  Vista "Completar Perfil" (nuevo postulante)
    // ──────────────────────────────────────────────
    private fun mostrarFormulario(prefs: android.content.SharedPreferences) {
        setContentView(R.layout.activity_completa_perfil)
        bindViews()
        setupSpinnersEstaticos()
        setupDatePicker()
        cargarDatosApi()
        setupBottomNav()

        btnEnviar.setOnClickListener {
            if (validarCampos()) {
                val idUsuario = prefs.getInt("id_usuario", 0)
                val provPos = spinnerProvincia.selectedItemPosition
                val distPos = spinnerDistrito.selectedItemPosition
                val corrPos = spinnerCorregimiento.selectedItemPosition

                val provincia = listaProvincias[provPos - 1]
                val distritosFiltrados = listaDistritos.filter { it.codigo_provincia == provincia.codigo_provincia }
                val distrito = distritosFiltrados[distPos - 1]
                val codigoNormalizado = distrito.codigo_distrito.toString().padStart(4, '0')
                val corregimientosFiltrados = listaCorregimientos.filter { it.codigo_distrito == codigoNormalizado }
                val corregimiento = corregimientosFiltrados[corrPos - 1]

                val postulante = PostulanteRequest(
                    idUsuario = idUsuario,
                    rangoAcademico = spinnerNivelAcademico.selectedItemPosition,
                    nombre = etPrimerNombre.text.toString().trim(),
                    nombre2 = etSegundoNombre.text.toString().trim(),
                    apellido = etPrimerApellido.text.toString().trim(),
                    apellido2 = etSegundoApellido.text.toString().trim(),
                    prefijo = etCedulaProvincia.text.toString().trim(),
                    tomo = etCedulaTomo.text.toString().trim(),
                    asiento = etCedulaAsiento.text.toString().trim(),
                    genero = when (spinnerGenero.selectedItem.toString()) {
                        "Masculino" -> 1
                        "Femenino" -> 2
                        "Otro" -> 3
                        else -> 1
                    },
                    estadoCivil = spinnerEstadoCivil.selectedItemPosition,
                    tipoSangre = spinnerTipoSangre.selectedItemPosition,
                    fechaNacimiento = formatearFechaParaMySQL(etFechaNacimiento.text.toString().trim()),
                    codigo_provincia = provincia.codigo_provincia,
                    codigo_distrito = codigoNormalizado,
                    codigo_corregimiento = corregimiento.codigo_corregimiento.toString().padStart(6, '0'),
                    comunidad = etUrbanizacion.text.toString().trim(),
                    calle = etCalle.text.toString().trim(),
                    casa = etCasaEdificio.text.toString().trim(),
                    detalleDireccion = etDetallesAdicionales.text.toString().trim(),
                    celular = etCelularPrimario.text.toString().trim(),
                    celular2 = etCelularSecundario.text.toString().trim(),
                    telefono = etTelefonoPrimario.text.toString().trim(),
                    telefono2 = etTelefonoSecundario.text.toString().trim().ifEmpty { "" },
                    correoPostulante = etCorreo.text.toString().trim()
                )

                lifecycleScope.launch {
                    try {
                        val response = api.registrarPostulante(postulante)
                        if (response.isSuccessful) {
                            recreate()
                        } else {
                            Toast.makeText(this@PerfilActivity, "Error al guardar perfil", Toast.LENGTH_SHORT).show()
                        }
                    } catch (e: Exception) {
                        android.util.Log.e("GUARDAR_ERROR", "Excepción: ${e.javaClass.simpleName} - ${e.message}", e)
                        if (e is retrofit2.HttpException) {
                            android.util.Log.e("GUARDAR_ERROR", "HTTP ${e.code()}: ${e.response()?.errorBody()?.string()}")
                        }
                        Toast.makeText(this@PerfilActivity, "Error: ${e.message}", Toast.LENGTH_LONG).show()
                    }
                }
            }
        }
    }

    // ──────────────────────────────────────────────
    //  Métodos compartidos (sin cambios relevantes)
    // ──────────────────────────────────────────────
    private fun cargarDatosApi() {
        lifecycleScope.launch {
            try {
                val ecResponse = api.getEstadosCiviles()
                if (ecResponse.isSuccessful) {
                    val lista = ecResponse.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + lista.map { it.nombreEstadoCiv }
                    spinnerEstadoCivil.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                }
                val tsResponse = api.getTiposSangre()
                if (tsResponse.isSuccessful) {
                    val lista = tsResponse.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + lista.map { it.nombreTipoSangre }
                    spinnerTipoSangre.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                }
                val raResponse = api.getRangosAcademicos()
                if (raResponse.isSuccessful) {
                    val lista = raResponse.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + lista.map { it.nombreRangoEdu }
                    spinnerNivelAcademico.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                }
                val provResponse = api.getProvincias()
                if (provResponse.isSuccessful) {
                    listaProvincias = provResponse.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + listaProvincias.map { it.nombre_provincia }
                    spinnerProvincia.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                }
                val distResponse = api.getDistritos()
                if (distResponse.isSuccessful) listaDistritos = distResponse.body() ?: emptyList()

                val corrResponse = api.getCorregimientos()
                if (corrResponse.isSuccessful) listaCorregimientos = corrResponse.body() ?: emptyList()

            } catch (e: Exception) {
                android.util.Log.e("PERFIL_ERROR", "Error: ${e.message}", e)
                Toast.makeText(this@PerfilActivity, "Error: ${e.message}", Toast.LENGTH_LONG).show()
            }
        }
    }

    private fun setupSpinnersEstaticos() {
        fun spinner(view: Spinner, items: List<String>) {
            view.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, items).apply {
                setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
            }
        }

        spinner(spinnerGenero, listOf("Seleccione...", "Masculino", "Femenino", "Otro"))
        spinner(spinnerEstadoCivil, listOf("Seleccione..."))
        spinner(spinnerTipoSangre, listOf("Seleccione..."))
        spinner(spinnerNivelAcademico, listOf("Seleccione..."))
        spinner(spinnerProvincia, listOf("Seleccione..."))
        spinner(spinnerDistrito, listOf("Seleccione..."))
        spinner(spinnerCorregimiento, listOf("Seleccione..."))

        // spinnerVacante solo existe en activity_completa_perfil
        if (::spinnerVacante.isInitialized) {
            spinner(spinnerVacante, listOf("Seleccione una posición...", "Desarrollador Android", "Desarrollador Backend", "Diseñador UX/UI", "Project Manager", "Analista de Sistemas"))
        }

        spinnerProvincia.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>, view: View?, pos: Int, id: Long) {
                if (pos == 0) {
                    spinnerDistrito.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                    spinnerCorregimiento.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                    return
                }
                val provincia = listaProvincias[pos - 1]
                val distritosFiltrados = listaDistritos.filter { it.codigo_provincia == provincia.codigo_provincia }
                val nombres = listOf("Seleccione...") + distritosFiltrados.map { it.nombre_distrito }
                spinnerDistrito.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                spinnerCorregimiento.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
            }
            override fun onNothingSelected(parent: AdapterView<*>) {}
        }

        restaurarListenerDistrito()
    }

    private fun bindViews() {
        etPrimerNombre        = findViewById(R.id.etPrimerNombre)
        etSegundoNombre       = findViewById(R.id.etSegundoNombre)
        etPrimerApellido      = findViewById(R.id.etPrimerApellido)
        etSegundoApellido     = findViewById(R.id.etSegundoApellido)
        etCedulaProvincia     = findViewById(R.id.etCedulaProvincia)
        etCedulaTomo          = findViewById(R.id.etCedulaTomo)
        etCedulaAsiento       = findViewById(R.id.etCedulaAsiento)
        spinnerGenero         = findViewById(R.id.spinnerGenero)
        etFechaNacimiento     = findViewById(R.id.etFechaNacimiento)
        spinnerEstadoCivil    = findViewById(R.id.spinnerEstadoCivil)
        spinnerTipoSangre     = findViewById(R.id.spinnerTipoSangre)
        spinnerNivelAcademico = findViewById(R.id.spinnerNivelAcademico)
        etTelefonoPrimario    = findViewById(R.id.etTelefonoPrimario)
        etTelefonoSecundario  = findViewById(R.id.etTelefonoSecundario)
        etCelularPrimario     = findViewById(R.id.etCelularPrimario)
        etCelularSecundario   = findViewById(R.id.etCelularSecundario)
        etCorreo              = findViewById(R.id.etCorreo)
        spinnerProvincia      = findViewById(R.id.spinnerProvincia)
        spinnerDistrito       = findViewById(R.id.spinnerDistrito)
        spinnerCorregimiento  = findViewById(R.id.spinnerCorregimiento)
        etUrbanizacion        = findViewById(R.id.etUrbanizacion)
        etCalle               = findViewById(R.id.etCalle)
        etCasaEdificio        = findViewById(R.id.etCasaEdificio)
        etDetallesAdicionales = findViewById(R.id.etDetallesAdicionales)
        spinnerVacante        = findViewById(R.id.spinnerVacante)
        btnEnviar             = findViewById(R.id.btnEnviarSolicitud)
        bottomNav             = findViewById(R.id.bottomNavPostulante)

        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val correoGuardado = prefs.getString("correo_usuario", "")
        if (!correoGuardado.isNullOrEmpty()) etCorreo.setText(correoGuardado)
    }

    private fun setupBottomNav() {
        bottomNav.selectedItemId = R.id.nav_perfil
        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_perfil -> true
                R.id.nav_documentos -> {
                    startActivity(Intent(this, DocumentosActivity::class.java).apply { flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT })
                    overridePendingTransition(0, 0)
                    true
                }
                R.id.nav_ajustes -> {
                    startActivity(Intent(this, AjustesActivity::class.java).apply {
                        putExtra("origen", "postulante")
                        flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    })
                    overridePendingTransition(0, 0)
                    true
                }
                else -> false
            }
        }
    }

    override fun onResume() {
        super.onResume()
        if (::bottomNav.isInitialized) bottomNav.selectedItemId = R.id.nav_perfil
    }

    private fun setupDatePicker() {
        etFechaNacimiento.setOnClickListener {
            val cal = Calendar.getInstance()
            DatePickerDialog(this, { _, year, month, day ->
                etFechaNacimiento.setText("%02d/%02d/%d".format(month + 1, day, year))
            }, cal.get(Calendar.YEAR) - 18, cal.get(Calendar.MONTH), cal.get(Calendar.DAY_OF_MONTH)).show()
        }
    }

    private fun validarCampos(): Boolean {
        val obligatorios = listOf(
            etPrimerNombre    to "Ingresa tu primer nombre",
            etPrimerApellido  to "Ingresa tu primer apellido",
            etCedulaProvincia to "Ingresa la provincia de tu cédula",
            etCedulaTomo      to "Ingresa el tomo de tu cédula",
            etCedulaAsiento   to "Ingresa el asiento de tu cédula",
            etCelularPrimario to "Ingresa tu celular primario",
            etCorreo          to "Ingresa tu correo electrónico",
            etUrbanizacion    to "Ingresa tu urbanización/barriada",
            etCalle           to "Ingresa tu calle",
            etCasaEdificio    to "Ingresa el número de casa o edificio",
            etFechaNacimiento to "Selecciona tu fecha de nacimiento"
        )
        for ((campo, mensaje) in obligatorios) {
            if (campo.text.isNullOrBlank()) {
                campo.error = mensaje
                campo.requestFocus()
                return false
            }
        }
        val email = etCorreo.text.toString().trim()
        if (!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            etCorreo.error = getString(R.string.error_email_invalid)
            etCorreo.requestFocus()
            return false
        }
        val spinnersObligatorios = listOf(
            spinnerGenero         to "Selecciona tu género",
            spinnerEstadoCivil    to "Selecciona tu estado civil",
            spinnerTipoSangre     to "Selecciona tu tipo de sangre",
            spinnerNivelAcademico to "Selecciona tu nivel académico",
            spinnerProvincia      to "Selecciona tu provincia",
            spinnerDistrito       to "Selecciona tu distrito",
            spinnerCorregimiento  to "Selecciona tu corregimiento",
            spinnerVacante        to "Selecciona la vacante a la que aplicas"
        )
        for ((spinner, mensaje) in spinnersObligatorios) {
            if (spinner.selectedItemPosition == 0) {
                Toast.makeText(this, mensaje, Toast.LENGTH_SHORT).show()
                spinner.requestFocus()
                return false
            }
        }
        return true
    }

    private fun formatearFechaParaMySQL(fecha: String): String {
        val partes = fecha.split("/")
        return if (partes.size == 3) "${partes[2]}-${partes[0].padStart(2, '0')}-${partes[1].padStart(2, '0')}" else fecha
    }
}
