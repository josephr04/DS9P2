package com.example.proyecto2_android.activities.postulante

import android.os.Bundle
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.proyecto2_android.R
import android.util.Patterns

class CambiarCorreoActivity : AppCompatActivity() {

    private lateinit var btnBack: ImageView
    private lateinit var tvCorreoActual: TextView
    private lateinit var etNuevoCorreo: EditText
    private lateinit var etConfirmarCorreo: EditText
    private lateinit var btnGuardar: LinearLayout
    private lateinit var btnCancelar: LinearLayout

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_cambiar_correo)

        initViews()
        setupListeners()
        cargarCorreoActual()
    }

    private fun initViews() {
        btnBack = findViewById(R.id.btnBack)
        tvCorreoActual = findViewById(R.id.tvCorreoActual)
        etNuevoCorreo = findViewById(R.id.etNuevoCorreo)
        etConfirmarCorreo = findViewById(R.id.etConfirmarCorreo)
        btnGuardar = findViewById(R.id.btnGuardarCorreo)
        btnCancelar = findViewById(R.id.btnCancelar)
    }

    private fun setupListeners() {
        btnBack.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }

        btnGuardar.setOnClickListener {
            val nuevoCorreo = etNuevoCorreo.text.toString().trim()
            val confirmarCorreo = etConfirmarCorreo.text.toString().trim()

            when {
                nuevoCorreo.isEmpty() -> {
                    Toast.makeText(this, "Ingresa un correo electrónico", Toast.LENGTH_SHORT).show()
                }
                !Patterns.EMAIL_ADDRESS.matcher(nuevoCorreo).matches() -> {
                    Toast.makeText(this, "Ingresa un correo válido", Toast.LENGTH_SHORT).show()
                }
                nuevoCorreo != confirmarCorreo -> {
                    Toast.makeText(this, "Los correos no coinciden", Toast.LENGTH_SHORT).show()
                }
                else -> {
                    Toast.makeText(this, "Correo actualizado correctamente", Toast.LENGTH_LONG).show()
                    finish()
                }
            }
        }

        btnCancelar.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }
    }

    private fun cargarCorreoActual() {
        // Cargar desde SharedPreferences o ViewModel
        tvCorreoActual.text = "correo@ejemplo.com"
    }
}