package com.example.proyecto2_android.activities.postulante

import android.os.Bundle
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.proyecto2_android.R

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

    private var contrasenaActualVisible = false
    private var nuevaContrasenaVisible = false
    private var confirmarContrasenaVisible = false

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
    }

    private fun setupListeners() {
        // Botón de atrás
        btnBack.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }

        // Toggle contraseña actual
        ivToggleActual.setOnClickListener {
            contrasenaActualVisible = !contrasenaActualVisible
            togglePasswordVisibility(etContrasenaActual, ivToggleActual, contrasenaActualVisible)
        }

        // Toggle nueva contraseña
        ivToggleNueva.setOnClickListener {
            nuevaContrasenaVisible = !nuevaContrasenaVisible
            togglePasswordVisibility(etNuevaContrasena, ivToggleNueva, nuevaContrasenaVisible)
        }

        // Toggle confirmar contraseña
        ivToggleConfirmar.setOnClickListener {
            confirmarContrasenaVisible = !confirmarContrasenaVisible
            togglePasswordVisibility(etConfirmarContrasena, ivToggleConfirmar, confirmarContrasenaVisible)
        }

        // Botón Actualizar
        btnActualizar.setOnClickListener {
            val contrasenaActual = etContrasenaActual.text.toString().trim()
            val nuevaContrasena = etNuevaContrasena.text.toString().trim()
            val confirmarContrasena = etConfirmarContrasena.text.toString().trim()

            when {
                contrasenaActual.isEmpty() -> {
                    Toast.makeText(this, "Ingresa tu contraseña actual", Toast.LENGTH_SHORT).show()
                }
                nuevaContrasena.isEmpty() -> {
                    Toast.makeText(this, "Ingresa una nueva contraseña", Toast.LENGTH_SHORT).show()
                }
                nuevaContrasena.length < 6 -> {
                    Toast.makeText(this, "La contraseña debe tener mínimo 6 caracteres", Toast.LENGTH_SHORT).show()
                }
                nuevaContrasena != confirmarContrasena -> {
                    Toast.makeText(this, "Las contraseñas no coinciden", Toast.LENGTH_SHORT).show()
                }
                else -> {
                    Toast.makeText(this, "Contraseña actualizada correctamente", Toast.LENGTH_LONG).show()
                    finish()
                }
            }
        }

        // Botón Cancelar
        btnCancelar.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }
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