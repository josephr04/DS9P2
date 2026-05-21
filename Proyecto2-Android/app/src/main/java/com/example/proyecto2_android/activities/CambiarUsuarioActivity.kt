package com.example.proyecto2_android.activities.postulante

import android.os.Bundle
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.proyecto2_android.R

class CambiarUsuarioActivity : AppCompatActivity() {

    private lateinit var btnBack: ImageView
    private lateinit var tvUsuarioActual: TextView
    private lateinit var etNuevoUsuario: EditText
    private lateinit var btnGuardar: LinearLayout
    private lateinit var btnCancelar: LinearLayout

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_cambiar_usuario)

        initViews()
        setupListeners()
        cargarUsuarioActual()
    }

    private fun initViews() {
        btnBack = findViewById(R.id.btnBack)
        tvUsuarioActual = findViewById(R.id.tvUsuarioActual)
        etNuevoUsuario = findViewById(R.id.etNuevoUsuario)
        btnGuardar = findViewById(R.id.btnGuardarUsuario)
        btnCancelar = findViewById(R.id.btnCancelar)
    }

    private fun setupListeners() {
        btnBack.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }

        btnGuardar.setOnClickListener {
            val nuevoUsuario = etNuevoUsuario.text.toString().trim()

            when {
                nuevoUsuario.isEmpty() -> {
                    Toast.makeText(this, "Ingresa un nombre de usuario", Toast.LENGTH_SHORT).show()
                }
                nuevoUsuario.length < 6 -> {
                    Toast.makeText(this, "El usuario debe tener mínimo 6 caracteres", Toast.LENGTH_SHORT).show()
                }
                nuevoUsuario.contains(" ") -> {
                    Toast.makeText(this, "El usuario no puede contener espacios", Toast.LENGTH_SHORT).show()
                }
                else -> {
                    Toast.makeText(this, "Usuario actualizado correctamente", Toast.LENGTH_LONG).show()
                    finish()
                }
            }
        }

        btnCancelar.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }
    }

    private fun cargarUsuarioActual() {
        // Cargar desde SharedPreferences o ViewModel
        tvUsuarioActual.text = "usuario_actual"
    }
}