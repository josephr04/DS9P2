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
import android.util.Patterns
import kotlinx.coroutines.launch

class CambiarCorreoActivity : AppCompatActivity() {

    private lateinit var btnBack: ImageView
    private lateinit var tvCorreoActual: TextView
    private lateinit var etNuevoCorreo: EditText
    private lateinit var etConfirmarCorreo: EditText
    private lateinit var btnGuardar: LinearLayout
    private lateinit var btnCancelar: LinearLayout
    private lateinit var progressBar: ProgressBar

    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

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
        progressBar = findViewById(R.id.progressBar)
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
                    Toast.makeText(this, "Ingresa un correo electrónico válido", Toast.LENGTH_SHORT).show()
                }
                confirmarCorreo.isEmpty() -> {
                    Toast.makeText(this, "Confirma tu nuevo correo electrónico", Toast.LENGTH_SHORT).show()
                }
                nuevoCorreo != confirmarCorreo -> {
                    Toast.makeText(this, "Los correos electrónicos no coinciden", Toast.LENGTH_SHORT).show()
                }
                else -> {
                    cambiarCorreo(nuevoCorreo, confirmarCorreo)
                }
            }
        }

        btnCancelar.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }
    }

    private fun cargarCorreoActual() {
        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val correoActual = prefs.getString("correo_usuario", "correo@ejemplo.com")
        tvCorreoActual.text = correoActual
    }

    private fun cambiarCorreo(nuevoCorreo: String, confirmarCorreo: String) {
        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val idUsuario = prefs.getInt("id_usuario", 0)

        if (idUsuario == 0) {
            Toast.makeText(this, "Error: sesión no válida", Toast.LENGTH_SHORT).show()
            return
        }

        setLoading(true)

        lifecycleScope.launch {
            try {
                val request = mapOf(
                    "nuevo_correo" to nuevoCorreo,
                    "confirmar_correo" to confirmarCorreo
                )
                val response = api.cambiarCorreo(idUsuario, request)

                setLoading(false)

                when (response.code()) {
                    200 -> {
                        // Actualizar SharedPreferences con el nuevo correo
                        prefs.edit().putString("correo_usuario", nuevoCorreo).apply()

                        Toast.makeText(
                            this@CambiarCorreoActivity,
                            "Correo electrónico actualizado correctamente",
                            Toast.LENGTH_LONG
                        ).show()
                        finish()
                    }
                    401 -> {
                        val errorBody = response.errorBody()?.string()
                        Toast.makeText(
                            this@CambiarCorreoActivity,
                            "Error de validación. Verifica los datos ingresados.",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                    404 -> {
                        Toast.makeText(
                            this@CambiarCorreoActivity,
                            "Usuario no encontrado",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                    409 -> {
                        Toast.makeText(
                            this@CambiarCorreoActivity,
                            "El correo electrónico ya está en uso",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                    else -> {
                        Toast.makeText(
                            this@CambiarCorreoActivity,
                            "Error al actualizar el correo",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                }
            } catch (e: Exception) {
                setLoading(false)
                Toast.makeText(
                    this@CambiarCorreoActivity,
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