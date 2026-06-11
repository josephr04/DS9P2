package com.example.proyecto2_android.activities.postulante

import android.content.Context
import android.os.Bundle
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.activities.network.RetrofitClient
import kotlinx.coroutines.launch

class CambiarUsuarioActivity : AppCompatActivity() {

    private lateinit var btnBack: ImageView
    private lateinit var tvUsuarioActual: TextView
    private lateinit var etNuevoUsuario: EditText
    private lateinit var btnGuardar: LinearLayout
    private lateinit var btnCancelar: LinearLayout
    private lateinit var progressBar: ProgressBar

    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

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
        progressBar = findViewById(R.id.progressBar)
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
                nuevoUsuario.length < 5 -> {
                    Toast.makeText(this, "El usuario debe tener mínimo 5 caracteres", Toast.LENGTH_SHORT).show()
                }
                nuevoUsuario.contains(" ") -> {
                    Toast.makeText(this, "El usuario no puede contener espacios", Toast.LENGTH_SHORT).show()
                }
                else -> {
                    cambiarUsuario(nuevoUsuario)
                }
            }
        }

        btnCancelar.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }
    }

    private fun cargarUsuarioActual() {
        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val nombreUsuario = prefs.getString("nombre_usuario", "usuario_actual")
        tvUsuarioActual.text = nombreUsuario
    }

    private fun cambiarUsuario(nuevoUsuario: String) {
        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val idUsuario = prefs.getInt("id_usuario", 0)

        if (idUsuario == 0) {
            Toast.makeText(this, "Error: sesión no válida", Toast.LENGTH_SHORT).show()
            return
        }

        setLoading(true)

        lifecycleScope.launch {
            try {
                val request = mapOf("nuevo_usuario" to nuevoUsuario)
                val response = api.cambiarUsuario(idUsuario, request)

                setLoading(false)

                when (response.code()) {
                    200 -> {
                        // Actualizar SharedPreferences con el nuevo nombre
                        prefs.edit().putString("nombre_usuario", nuevoUsuario).apply()

                        Toast.makeText(
                            this@CambiarUsuarioActivity,
                            "Usuario actualizado correctamente",
                            Toast.LENGTH_LONG
                        ).show()
                        finish()
                    }
                    401 -> {
                        val errorBody = response.errorBody()?.string()
                        Toast.makeText(
                            this@CambiarUsuarioActivity,
                            "Error de validación",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                    404 -> {
                        Toast.makeText(
                            this@CambiarUsuarioActivity,
                            "Usuario no encontrado",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                    409 -> {
                        Toast.makeText(
                            this@CambiarUsuarioActivity,
                            "El nombre de usuario ya está en uso",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                    else -> {
                        Toast.makeText(
                            this@CambiarUsuarioActivity,
                            "Error al actualizar el usuario",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                }
            } catch (e: Exception) {
                setLoading(false)
                Toast.makeText(
                    this@CambiarUsuarioActivity,
                    "Error de conexión: ${e.message}",
                    Toast.LENGTH_SHORT
                ).show()
            }
        }
    }

    private fun setLoading(loading: Boolean) {
        progressBar.visibility = if (loading) android.view.View.VISIBLE else android.view.View.GONE
        btnGuardar.isEnabled = !loading
        btnGuardar.alpha = if (loading) 0.6f else 1.0f
    }
}