package com.example.proyecto2_android.activities.postulante

import android.app.DatePickerDialog
import android.content.Context
import android.content.Intent
import android.os.Bundle
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

    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

    private var listaProvincias: List<Provincia> = emptyList()
    private var listaDistritos: List<Distrito> = emptyList()
    private var listaCorregimientos: List<Corregimiento> = emptyList()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val idUsuario = prefs.getInt("id_usuario", 0)

        lifecycleScope.launch {
            try {
                val response = api.getPostulantes()
                if (response.isSuccessful) {
                    val postulantes = response.body() ?: emptyList()
                    val existe = postulantes.any { it["idUsuario"]?.toString()?.toDoubleOrNull()?.toInt() == idUsuario }

                    if (existe) {
                        setContentView(R.layout.activity_mi_perfil)
                        bottomNav = findViewById(R.id.bottomNavPostulante)
                        setupBottomNav()
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
                    fechaNacimiento = formatearFechaParaMySQL(etFechaNacimiento.text.toString().trim()),                    codigo_provincia = provincia.codigo_provincia,
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
                        val response = api.registrarPostulante(postulante)
                        if (response.isSuccessful) {
                            recreate()
                        } else {
                            Toast.makeText(this@PerfilActivity, "Error al guardar perfil", Toast.LENGTH_SHORT).show()
                        }
                    } catch (e: Exception) {
                        android.util.Log.e("GUARDAR_ERROR", "Excepción: ${e.javaClass.simpleName} - ${e.message}", e)
                        // Si es retrofit HttpException, loguea el cuerpo de la respuesta
                        if (e is retrofit2.HttpException) {
                            android.util.Log.e("GUARDAR_ERROR", "HTTP ${e.code()}: ${e.response()?.errorBody()?.string()}")
                        }
                        Toast.makeText(this@PerfilActivity, "Error: ${e.message}", Toast.LENGTH_LONG).show()
                    }
                }
            }
        }
    }

    private fun cargarDatosApi() {
        lifecycleScope.launch {
            try {
                // Cargar estados civiles
                val ecResponse = api.getEstadosCiviles()
                if (ecResponse.isSuccessful) {
                    val lista = ecResponse.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + lista.map { it.nombreEstadoCiv }
                    spinnerEstadoCivil.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                }

                // Cargar tipos de sangre
                val tsResponse = api.getTiposSangre()
                if (tsResponse.isSuccessful) {
                    val lista = tsResponse.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + lista.map { it.nombreTipoSangre }
                    spinnerTipoSangre.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                }

                // Cargar rangos académicos
                val raResponse = api.getRangosAcademicos()
                if (raResponse.isSuccessful) {
                    val lista = raResponse.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + lista.map { it.nombreRangoEdu }
                    spinnerNivelAcademico.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                }

                // Cargar provincias
                val provResponse = api.getProvincias()
                if (provResponse.isSuccessful) {
                    listaProvincias = provResponse.body() ?: emptyList()
                    val nombres = listOf("Seleccione...") + listaProvincias.map { it.nombre_provincia }
                    spinnerProvincia.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                }

                // Cargar todos los distritos y corregimientos de una vez
                val distResponse = api.getDistritos()
                if (distResponse.isSuccessful) {
                    listaDistritos = distResponse.body() ?: emptyList()
                }

                val corrResponse = api.getCorregimientos()
                android.util.Log.d("CORR_DEBUG", "Status: ${corrResponse.code()}")
                android.util.Log.d("CORR_DEBUG", "Body: ${corrResponse.errorBody()?.string()}")
                if (corrResponse.isSuccessful) {
                    try {
                        listaCorregimientos = corrResponse.body() ?: emptyList()
                        android.util.Log.d("CORR_DEBUG", "Total cargados: ${listaCorregimientos.size}")
                        // Opcional: imprimir los primeros 5 para verificar
                        if (listaCorregimientos.isNotEmpty()) {
                            android.util.Log.d("CORR_DEBUG", "Primer corregimiento: ${listaCorregimientos[0].nombre_corregimiento}")
                        }
                    } catch (e: Exception) {
                        android.util.Log.e("CORR_DEBUG", "Error parseando JSON: ${e.message}")
                    }
                } else {
                    android.util.Log.e("CORR_DEBUG", "Error HTTP: ${corrResponse.code()}")
                }

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
        spinner(spinnerVacante, listOf("Seleccione una posición...", "Desarrollador Android", "Desarrollador Backend", "Diseñador UX/UI", "Project Manager", "Analista de Sistemas"))

        spinnerProvincia.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>, view: View?, pos: Int, id: Long) {
                if (pos == 0) {
                    spinnerDistrito.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                    spinnerCorregimiento.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                    return
                }
                val provincia = listaProvincias[pos - 1]
                val distritosFiltrados = listaDistritos.filter { it.codigo_provincia == provincia.codigo_provincia }
                val nombres = listOf("Seleccione...") + distritosFiltrados.map { it.nombre_distrito }
                spinnerDistrito.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                    setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                }
                spinnerCorregimiento.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply {
                    setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                }
            }
            override fun onNothingSelected(parent: AdapterView<*>) {}
        }

        spinnerDistrito.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>, view: View?, pos: Int, id: Long) {
                if (pos == 0) {
                    spinnerCorregimiento.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, listOf("Seleccione...")).apply {
                        setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                    }
                    return
                }
                val provPos = spinnerProvincia.selectedItemPosition
                if (provPos == 0) return
                val provincia = listaProvincias[provPos - 1]
                val distritosFiltrados = listaDistritos.filter { it.codigo_provincia == provincia.codigo_provincia }
                val distrito = distritosFiltrados[pos - 1]
                val codigoDistritoNormalizado = distrito.codigo_distrito.toString().padStart(4, '0')
                val corregimientosFiltrados = listaCorregimientos.filter { it.codigo_distrito == codigoDistritoNormalizado }
                val nombres = listOf("Seleccione...") + corregimientosFiltrados.map { it.nombre_corregimiento }
                spinnerCorregimiento.adapter = ArrayAdapter(this@PerfilActivity, android.R.layout.simple_spinner_item, nombres).apply {
                    setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                }
            }
            override fun onNothingSelected(parent: AdapterView<*>) {}
        }
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
        if (!correoGuardado.isNullOrEmpty()) {
            etCorreo.setText(correoGuardado)
        }
    }

    private fun setupBottomNav() {
        bottomNav.selectedItemId = R.id.nav_perfil
        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_perfil -> true
                R.id.nav_documentos -> {
                    startActivity(Intent(this, DocumentosActivity::class.java).apply {
                        flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    })
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
        if (::bottomNav.isInitialized) {
            bottomNav.selectedItemId = R.id.nav_perfil
        }
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
        // Convierte de MM/dd/yyyy a yyyy-MM-dd
        val partes = fecha.split("/")
        return if (partes.size == 3) {
            "${partes[2]}-${partes[0].padStart(2, '0')}-${partes[1].padStart(2, '0')}"
        } else {
            fecha
        }
    }
}