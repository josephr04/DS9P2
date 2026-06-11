package com.example.proyecto2_android.activities.postulante

import android.content.Context
import android.os.Bundle
import android.view.View
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.network.RetrofitClient
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.models.CambiarContrasenaRequest
import kotlinx.coroutines.launch

class CambiarContrasenaActivity : AppCompatActivity() {

    private lateinit var btnBack: ImageView
    private lateinit var etContrasenaActual: EditText
    private lateinit var etNuevaContrasena: EditText
    private lateinit var etConfirmarContrasena: EditText
    private lateinit var btnActualizar: LinearLayout
    private lateinit var btnCancelar: LinearLayout
    private lateinit var ivToggleActual: ImageView
    private lateinit var ivToggleNueva: ImageView
    private lateinit var ivToggleConfirmar: ImageView
    private lateinit var progressBar: ProgressBar

    private var contrasenaActualVisible = false
    private var nuevaContrasenaVisible = false
    private var confirmarContrasenaVisible = false

    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_cambiar_contrasena)
        initViews()
        setupListeners()
    }

    private fun initViews() {
        btnBack = findViewById(R.id.btnBack)
        etContrasenaActual = findViewById(R.id.etContrasenaActual)
        etNuevaContrasena = findViewById(R.id.etNuevaContrasena)
        etConfirmarContrasena = findViewById(R.id.etConfirmarContrasena)
        btnActualizar = findViewById(R.id.btnActualizarContrasena)
        btnCancelar = findViewById(R.id.btnCancelar)
        ivToggleActual = findViewById(R.id.ivToggleActual)
        ivToggleNueva = findViewById(R.id.ivToggleNueva)
        ivToggleConfirmar = findViewById(R.id.ivToggleConfirmar)
        progressBar = findViewById(R.id.progressBar)
    }

    private fun setupListeners() {
        btnBack.setOnClickListener { finish(); overridePendingTransition(0, 0) }
        btnCancelar.setOnClickListener { finish(); overridePendingTransition(0, 0) }

        ivToggleActual.setOnClickListener {
            contrasenaActualVisible = !contrasenaActualVisible
            togglePasswordVisibility(etContrasenaActual, ivToggleActual, contrasenaActualVisible)
        }
        ivToggleNueva.setOnClickListener {
            nuevaContrasenaVisible = !nuevaContrasenaVisible
            togglePasswordVisibility(etNuevaContrasena, ivToggleNueva, nuevaContrasenaVisible)
        }
        ivToggleConfirmar.setOnClickListener {
            confirmarContrasenaVisible = !confirmarContrasenaVisible
            togglePasswordVisibility(etConfirmarContrasena, ivToggleConfirmar, confirmarContrasenaVisible)
        }

        btnActualizar.setOnClickListener {
            val contrasenaActual = etContrasenaActual.text.toString().trim()
            val nuevaContrasena = etNuevaContrasena.text.toString().trim()
            val confirmarContrasena = etConfirmarContrasena.text.toString().trim()

            when {
                contrasenaActual.isEmpty() ->
                    Toast.makeText(this, "Ingresa tu contraseña actual", Toast.LENGTH_SHORT).show()
                nuevaContrasena.isEmpty() ->
                    Toast.makeText(this, "Ingresa una nueva contraseña", Toast.LENGTH_SHORT).show()
                nuevaContrasena.length < 6 ->
                    Toast.makeText(this, "La contraseña debe tener mínimo 6 caracteres", Toast.LENGTH_SHORT).show()
                nuevaContrasena != confirmarContrasena ->
                    Toast.makeText(this, "Las contraseñas no coinciden", Toast.LENGTH_SHORT).show()
                else -> cambiarContrasena(contrasenaActual, nuevaContrasena)
            }
        }
    }

    private fun cambiarContrasena(actual: String, nueva: String) {
        val prefs = getSharedPreferences("careerport", Context.MODE_PRIVATE)
        val idUsuario = prefs.getInt("id_usuario", 0)

        if (idUsuario == 0) {
            Toast.makeText(this, "Error: sesión no válida", Toast.LENGTH_SHORT).show()
            return
        }

        setLoading(true)

        lifecycleScope.launch {
            try {
                val response = api.cambiarContrasena(
                    idUsuario,
                    CambiarContrasenaRequest(actual, nueva)
                )

                setLoading(false)

                when (response.code()) {
                    200 -> {
                        Toast.makeText(
                            this@CambiarContrasenaActivity,
                            "Contraseña actualizada correctamente",
                            Toast.LENGTH_LONG
                        ).show()
                        finish()
                    }
                    401 -> {
                        Toast.makeText(
                            this@CambiarContrasenaActivity,
                            "La contraseña actual es incorrecta",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                    else -> {
                        Toast.makeText(
                            this@CambiarContrasenaActivity,
                            "Error al actualizar la contraseña",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                }
            } catch (e: Exception) {
                setLoading(false)
                Toast.makeText(
                    this@CambiarContrasenaActivity,
                    "Error de conexión: ${e.message}",
                    Toast.LENGTH_SHORT
                ).show()
            }
        }
    }

    private fun setLoading(loading: Boolean) {
        progressBar.visibility = if (loading) View.VISIBLE else View.GONE
        btnActualizar.isEnabled = !loading
        btnActualizar.alpha = if (loading) 0.6f else 1.0f
    }

    private fun togglePasswordVisibility(editText: EditText, imageView: ImageView, isVisible: Boolean) {
        if (isVisible) {
            imageView.setImageResource(R.drawable.ic_visibility)
            editText.inputType = android.text.InputType.TYPE_TEXT_VARIATION_VISIBLE_PASSWORD
        } else {
            imageView.setImageResource(R.drawable.ic_visibility_off)
            editText.inputType = android.text.InputType.TYPE_CLASS_TEXT or android.text.InputType.TYPE_TEXT_VARIATION_PASSWORD
        }
        editText.setSelection(editText.text.length)
    }
}