package com.example.proyecto2_android.activities.postulante

import android.app.DatePickerDialog
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.view.View
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.AjustesActivity
import com.google.android.material.bottomnavigation.BottomNavigationView
import com.google.android.material.button.MaterialButton
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

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val perfilCompleto = prefs.getBoolean("perfil_completo", false)

        if (perfilCompleto) {
            setContentView(R.layout.activity_mi_perfil)
            bottomNav = findViewById(R.id.bottomNavPostulante)
            setupBottomNav()
        } else {
            setContentView(R.layout.activity_completa_perfil)
            bindViews()
            setupSpinners()
            setupDatePicker()
            setupBottomNav()

            btnEnviar.setOnClickListener {
                if (validarCampos()) {
                    prefs.edit().putBoolean("perfil_completo", true).apply()
                    recreate()
                }
            }
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
    }

    private fun setupBottomNav() {
        bottomNav.selectedItemId = R.id.nav_perfil

        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_perfil -> true
                R.id.nav_documentos -> {
                    val intent = Intent(this, DocumentosActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    true
                }
                R.id.nav_ajustes -> {
                    val intent = Intent(this, AjustesActivity::class.java)
                    intent.putExtra("origen", "postulante")
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    true
                }
                else -> false
            }
        }
    }

    override fun onResume() {
        super.onResume()
        bottomNav.selectedItemId = R.id.nav_perfil
    }

    private fun setupSpinners() {
        fun spinner(view: Spinner, items: List<String>) {
            val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, items)
            adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
            view.adapter = adapter
        }

        spinner(spinnerGenero, listOf("Seleccione...", "Masculino", "Femenino", "Otro"))
        spinner(spinnerEstadoCivil, listOf("Seleccione...", "Soltero/a", "Casado/a", "Divorciado/a", "Viudo/a", "Unión libre"))
        spinner(spinnerTipoSangre, listOf("Seleccione...", "A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"))
        spinner(spinnerNivelAcademico, listOf("Seleccione...", "Primaria", "Secundaria", "Técnico", "Universitario", "Postgrado", "Maestría", "Doctorado"))
        spinner(spinnerProvincia, listOf("Seleccione...", "Bocas del Toro", "Chiriquí", "Coclé", "Colón", "Darién", "Herrera", "Los Santos", "Panamá", "Panamá Oeste", "Veraguas"))
        spinner(spinnerDistrito, listOf("Seleccione..."))
        spinner(spinnerCorregimiento, listOf("Seleccione..."))

        spinnerProvincia.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>, view: View?, pos: Int, id: Long) {
                actualizarDistritos(spinnerProvincia.selectedItem.toString())
            }
            override fun onNothingSelected(parent: AdapterView<*>) {}
        }

        spinnerDistrito.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>, view: View?, pos: Int, id: Long) {
                actualizarCorregimientos(spinnerDistrito.selectedItem.toString())
            }
            override fun onNothingSelected(parent: AdapterView<*>) {}
        }

        spinner(spinnerVacante, listOf("Seleccione una posición...", "Desarrollador Android", "Desarrollador Backend", "Diseñador UX/UI", "Project Manager", "Analista de Sistemas"))
    }

    private fun actualizarDistritos(provincia: String) {
        val distritos = when (provincia) {
            "Panamá"   -> listOf("Seleccione...", "Panamá", "San Miguelito", "Chepo", "Balboa")
            "Colón"    -> listOf("Seleccione...", "Colón", "Portobelo", "Chagres", "Donoso")
            "Chiriquí" -> listOf("Seleccione...", "David", "Boquete", "Bugaba", "Barú")
            "Coclé"    -> listOf("Seleccione...", "Penonomé", "Aguadulce", "Natá", "Olá")
            else       -> listOf("Seleccione...")
        }
        val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, distritos)
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerDistrito.adapter = adapter
    }

    private fun actualizarCorregimientos(distrito: String) {
        val corregimientos = when (distrito) {
            "Panamá"        -> listOf("Seleccione...", "Ancón", "Betania", "Bella Vista", "Calidonia", "El Chorrillo")
            "San Miguelito" -> listOf("Seleccione...", "Amelia Denis de Icaza", "Belisario Frías", "José Domingo Espinar")
            "Colón"         -> listOf("Seleccione...", "Barrio Norte", "Barrio Sur", "Cristóbal", "Cativá")
            else            -> listOf("Seleccione...")
        }
        val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, corregimientos)
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerCorregimiento.adapter = adapter
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
}