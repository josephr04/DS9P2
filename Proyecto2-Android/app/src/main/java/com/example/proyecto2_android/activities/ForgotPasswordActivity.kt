package com.example.proyecto2_android.activities

import android.content.Intent
import android.os.Bundle
import android.text.InputType
import android.view.View
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

class ForgotPasswordActivity : AppCompatActivity() {

    // ---- Paso 1 ----
    private lateinit var layoutStep1: LinearLayout
    private lateinit var etCorreo: EditText
    private lateinit var btnEnviarCodigo: LinearLayout

    // ---- Paso 2 ----
    private lateinit var layoutStep2: LinearLayout
    private lateinit var tvCodigoGenerado: TextView
    private lateinit var etCodigo: EditText
    private lateinit var tvErrorCodigo: TextView
    private lateinit var tvReenviarCodigo: TextView
    private lateinit var btnVerificarCodigo: LinearLayout

    // ---- Paso 3 ----
    private lateinit var layoutStep3: LinearLayout
    private lateinit var etNuevaContrasena: EditText
    private lateinit var etConfirmarContrasena: EditText
    private lateinit var ivToggleNueva: ImageView
    private lateinit var ivToggleConfirmar: ImageView
    private lateinit var btnGuardarContrasena: LinearLayout

    // ---- Comunes ----
    private lateinit var btnBack: ImageView
    private lateinit var tvStepTitle: TextView
    private lateinit var tvStepSubtitle: TextView
    private lateinit var ivStepIcon: ImageView
    private lateinit var progressBar: ProgressBar

    private var codigoGenerado: String = ""
    private var correoIngresado: String = ""
    private var nuevaVisible = false
    private var confirmarVisible = false

    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_forgot_password)
        initViews()
        setupListeners()
        mostrarPaso1()
    }

    private fun initViews() {
        btnBack             = findViewById(R.id.btnBack)
        tvStepTitle         = findViewById(R.id.tvStepTitle)
        tvStepSubtitle      = findViewById(R.id.tvStepSubtitle)
        ivStepIcon          = findViewById(R.id.ivStepIcon)
        progressBar         = findViewById(R.id.progressBar)

        layoutStep1         = findViewById(R.id.layoutStep1)
        etCorreo            = findViewById(R.id.etCorreo)
        btnEnviarCodigo     = findViewById(R.id.btnEnviarCodigo)

        layoutStep2         = findViewById(R.id.layoutStep2)
        tvCodigoGenerado    = findViewById(R.id.tvCodigoGenerado)
        etCodigo            = findViewById(R.id.etCodigo)
        tvErrorCodigo       = findViewById(R.id.tvErrorCodigo)
        tvReenviarCodigo    = findViewById(R.id.tvReenviarCodigo)
        btnVerificarCodigo  = findViewById(R.id.btnVerificarCodigo)

        layoutStep3             = findViewById(R.id.layoutStep3)
        etNuevaContrasena       = findViewById(R.id.etNuevaContrasena)
        etConfirmarContrasena   = findViewById(R.id.etConfirmarContrasena)
        ivToggleNueva           = findViewById(R.id.ivToggleNueva)
        ivToggleConfirmar       = findViewById(R.id.ivToggleConfirmar)
        btnGuardarContrasena    = findViewById(R.id.btnGuardarContrasena)
    }

    private fun setupListeners() {
        btnBack.setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
            overridePendingTransition(0, 0)
        }

        // Paso 1 → enviar código
        btnEnviarCodigo.setOnClickListener {
            val correo = etCorreo.text.toString().trim()
            when {
                correo.isEmpty() ->
                    Toast.makeText(this, "Ingresa tu correo electrónico", Toast.LENGTH_SHORT).show()
                !android.util.Patterns.EMAIL_ADDRESS.matcher(correo).matches() ->
                    Toast.makeText(this, "Ingresa un correo válido", Toast.LENGTH_SHORT).show()
                else -> {
                    correoIngresado = correo
                    generarYMostrarCodigo()
                }
            }
        }

        // Paso 2 → verificar código
        btnVerificarCodigo.setOnClickListener {
            val codigoIngresado = etCodigo.text.toString().trim()
            when {
                codigoIngresado.isEmpty() ->
                    Toast.makeText(this, "Ingresa el código de verificación", Toast.LENGTH_SHORT).show()
                codigoIngresado != codigoGenerado -> {
                    tvErrorCodigo.visibility = View.VISIBLE
                    etCodigo.text?.clear()
                }
                else -> {
                    tvErrorCodigo.visibility = View.GONE
                    mostrarPaso3()
                }
            }
        }

        // Reenviar código
        tvReenviarCodigo.setOnClickListener {
            generarNuevoCodigo()
            Toast.makeText(this, "Se generó un nuevo código", Toast.LENGTH_SHORT).show()
        }

        // Toggle visibilidad contraseñas
        ivToggleNueva.setOnClickListener {
            nuevaVisible = !nuevaVisible
            togglePasswordVisibility(etNuevaContrasena, ivToggleNueva, nuevaVisible)
        }
        ivToggleConfirmar.setOnClickListener {
            confirmarVisible = !confirmarVisible
            togglePasswordVisibility(etConfirmarContrasena, ivToggleConfirmar, confirmarVisible)
        }

        // Paso 3 → guardar nueva contraseña
        btnGuardarContrasena.setOnClickListener {
            val nueva     = etNuevaContrasena.text.toString().trim()
            val confirmar = etConfirmarContrasena.text.toString().trim()
            when {
                nueva.isEmpty() ->
                    Toast.makeText(this, "Ingresa tu nueva contraseña", Toast.LENGTH_SHORT).show()
                nueva.length < 6 ->
                    Toast.makeText(this, "La contraseña debe tener mínimo 6 caracteres", Toast.LENGTH_SHORT).show()
                nueva != confirmar ->
                    Toast.makeText(this, "Las contraseñas no coinciden", Toast.LENGTH_SHORT).show()
                else -> guardarNuevaContrasena(nueva)
            }
        }
    }

    // ─────────────── Pasos ───────────────

    private fun mostrarPaso1() {
        layoutStep1.visibility = View.VISIBLE
        layoutStep2.visibility = View.GONE
        layoutStep3.visibility = View.GONE

        ivStepIcon.setImageResource(R.drawable.ic_lock)
        tvStepTitle.text    = "¿Olvidaste tu contraseña?"
        tvStepSubtitle.text = "Ingresa tu correo electrónico y te enviaremos un código de verificación."
    }

    private fun generarYMostrarCodigo() {
        generarNuevoCodigo()
        mostrarPaso2()
    }

    private fun generarNuevoCodigo() {
        codigoGenerado = (100000..999999).random().toString()
        tvCodigoGenerado.text = codigoGenerado
        etCodigo.text?.clear()
        tvErrorCodigo.visibility = View.GONE
    }

    private fun mostrarPaso2() {
        layoutStep1.visibility = View.GONE
        layoutStep2.visibility = View.VISIBLE
        layoutStep3.visibility = View.GONE

        ivStepIcon.setImageResource(R.drawable.ic_lock)
        tvStepTitle.text    = "Verifica tu identidad"
        tvStepSubtitle.text = "Ingresa el código de 6 dígitos que aparece a continuación."
    }

    private fun mostrarPaso3() {
        layoutStep1.visibility = View.GONE
        layoutStep2.visibility = View.GONE
        layoutStep3.visibility = View.VISIBLE

        ivStepIcon.setImageResource(R.drawable.ic_lock)
        tvStepTitle.text    = "Nueva contraseña"
        tvStepSubtitle.text = "Elige una contraseña segura para tu cuenta."
    }

    // ─────────────── Lógica real con API ───────────────

    private fun guardarNuevaContrasena(nuevaContrasena: String) {
        setLoading(true)

        lifecycleScope.launch {
            try {
                val body = mapOf(
                    "correo"            to correoIngresado,
                    "nueva_contrasena"  to nuevaContrasena
                )

                val response = api.resetContrasena(body)

                setLoading(false)

                when (response.code()) {
                    200 -> {
                        Toast.makeText(
                            this@ForgotPasswordActivity,
                            "¡Contraseña actualizada correctamente!",
                            Toast.LENGTH_LONG
                        ).show()
                        irAlLogin()
                    }
                    404 -> {
                        Toast.makeText(
                            this@ForgotPasswordActivity,
                            "No existe una cuenta con ese correo",
                            Toast.LENGTH_SHORT
                        ).show()
                        // Regresar al paso 1 para que corrija el correo
                        mostrarPaso1()
                    }
                    else -> {
                        Toast.makeText(
                            this@ForgotPasswordActivity,
                            "Error al actualizar la contraseña, intenta de nuevo",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                }
            } catch (e: Exception) {
                setLoading(false)
                Toast.makeText(
                    this@ForgotPasswordActivity,
                    "Error de conexión: ${e.message}",
                    Toast.LENGTH_SHORT
                ).show()
            }
        }
    }

    private fun irAlLogin() {
        // Cierra esta activity y vuelve al Login (que ya está en el back stack)
        finish()
        overridePendingTransition(0, 0)
    }

    private fun setLoading(loading: Boolean) {
        progressBar.visibility         = if (loading) View.VISIBLE else View.GONE
        btnGuardarContrasena.isEnabled = !loading
        btnGuardarContrasena.alpha     = if (loading) 0.6f else 1.0f
    }

    private fun togglePasswordVisibility(editText: EditText, imageView: ImageView, isVisible: Boolean) {
        if (isVisible) {
            imageView.setImageResource(R.drawable.ic_visibility)
            editText.inputType = InputType.TYPE_TEXT_VARIATION_VISIBLE_PASSWORD
        } else {
            imageView.setImageResource(R.drawable.ic_visibility_off)
            editText.inputType = InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD
        }
        editText.setSelection(editText.text.length)
    }
}