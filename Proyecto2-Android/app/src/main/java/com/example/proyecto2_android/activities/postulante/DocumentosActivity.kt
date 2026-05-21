package com.example.proyecto2_android.activities.postulante

import android.app.DatePickerDialog
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

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_documentos)

        bindViews()
        setupSpinners()
        setupDatePickers()
        setupCheckbox()
        setupUpload()
        setupBottomNav()
        setupBoton()
    }

    private fun bindViews() {
        bottomNav             = findViewById(R.id.bottomNavPostulante)
        spinnerTipo           = findViewById(R.id.spinnerTipo)
        spinnerInstitucion    = findViewById(R.id.spinnerInstitucion)
        spinnerProvincia      = findViewById(R.id.spinnerProvincia)
        checkInstitucionCustom= findViewById(R.id.checkInstitucionCustom)
        etInstitucionCustom   = findViewById(R.id.etInstitucionCustom)
        etTituloDocumento     = findViewById(R.id.etTituloDocumento)
        etTotalHoras          = findViewById(R.id.etTotalHoras)
        tvFechaInicio         = findViewById(R.id.tvFechaInicio)
        tvFechaFin            = findViewById(R.id.tvFechaFin)
        tvFechaEmision        = findViewById(R.id.tvFechaEmision)
        layoutFechaInicio     = findViewById(R.id.etFechaInicio)
        layoutFechaFin        = findViewById(R.id.etFechaFin)
        layoutFechaEmision    = findViewById(R.id.etFechaEmision)
        layoutUploadPdf       = findViewById(R.id.layoutUploadPdf)
        tvUploadLabel         = findViewById(R.id.tvUploadLabel)
        btnSubirDocumento     = findViewById(R.id.btnSubirDocumento)
        layoutVacio           = findViewById(R.id.layoutVacio)
    }

    private fun setupSpinners() {
        fun spinner(view: Spinner, items: List<String>) {
            val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, items)
            adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
            view.adapter = adapter
        }

        spinner(spinnerTipo, listOf(
            "Seleccione...", "Diploma", "Certificado", "Título", "Constancia", "Otro"
        ))

        spinner(spinnerInstitucion, listOf(
            "Selecciona una institución",
            "Universidad Tecnológica de Panamá",
            "Universidad de Panamá",
            "Universidad Santa María La Antigua",
            "INADEH",
            "Instituto Especializado de Análisis",
            "Universidad Latina de Panamá",
            "ISAE Universidad"
        ))

        spinner(spinnerProvincia, listOf(
            "Seleccione...", "Bocas del Toro", "Chiriquí", "Coclé", "Colón",
            "Darién", "Herrera", "Los Santos", "Panamá", "Panamá Oeste", "Veraguas"
        ))
    }

    private fun setupDatePickers() {
        fun showDatePicker(target: TextView) {
            val cal = Calendar.getInstance()
            DatePickerDialog(
                this,
                { _, year, month, day ->
                    target.text = "%02d/%02d/%d".format(month + 1, day, year)
                    target.setTextColor(getColor(R.color.text_primary))
                },
                cal.get(Calendar.YEAR),
                cal.get(Calendar.MONTH),
                cal.get(Calendar.DAY_OF_MONTH)
            ).show()
        }

        layoutFechaInicio.setOnClickListener  { showDatePicker(tvFechaInicio) }
        layoutFechaFin.setOnClickListener     { showDatePicker(tvFechaFin) }
        layoutFechaEmision.setOnClickListener { showDatePicker(tvFechaEmision) }
    }

    private fun setupCheckbox() {
        checkInstitucionCustom.setOnCheckedChangeListener { _, isChecked ->
            if (isChecked) {
                spinnerInstitucion.visibility = View.GONE
                etInstitucionCustom.visibility = View.VISIBLE
            } else {
                spinnerInstitucion.visibility = View.VISIBLE
                etInstitucionCustom.visibility = View.GONE
            }
        }
    }

    private fun setupUpload() {
        layoutUploadPdf.setOnClickListener {
            // TODO: abrir selector de PDF
            tvUploadLabel.text = "archivo_certificado.pdf"
        }
    }

    private fun setupBoton() {
        btnSubirDocumento.setOnClickListener {
            if (validarCampos()) {
                Toast.makeText(this, "Documento subido correctamente", Toast.LENGTH_SHORT).show()
                limpiarFormulario()
                layoutVacio.visibility = View.GONE
                // TODO: agregar al RecyclerView cuando esté implementado
            }
        }
    }

    private fun validarCampos(): Boolean {
        if (etTituloDocumento.text.isNullOrBlank()) {
            etTituloDocumento.error = "Ingresa el título del documento"
            etTituloDocumento.requestFocus()
            return false
        }
        if (spinnerTipo.selectedItemPosition == 0) {
            Toast.makeText(this, "Selecciona el tipo de documento", Toast.LENGTH_SHORT).show()
            return false
        }
        val institucionOk = if (checkInstitucionCustom.isChecked) {
            !etInstitucionCustom.text.isNullOrBlank()
        } else {
            spinnerInstitucion.selectedItemPosition != 0
        }
        if (!institucionOk) {
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
            etTotalHoras.requestFocus()
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
        tvFechaInicio.text = "mm/dd/yyyy"
        tvFechaFin.text = "mm/dd/yyyy"
        tvFechaEmision.text = "mm/dd/yyyy"
        tvFechaInicio.setTextColor(getColor(R.color.text_secondary))
        tvFechaFin.setTextColor(getColor(R.color.text_secondary))
        tvFechaEmision.setTextColor(getColor(R.color.text_secondary))
        checkInstitucionCustom.isChecked = false
        tvUploadLabel.text = "Arrastra o haz clic para subir"
    }

    private fun setupBottomNav() {
        bottomNav.selectedItemId = R.id.nav_documentos

        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_perfil -> {
                    val intent = Intent(this, PerfilActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    true
                }
                R.id.nav_documentos -> true
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
        bottomNav.selectedItemId = R.id.nav_documentos
    }
}