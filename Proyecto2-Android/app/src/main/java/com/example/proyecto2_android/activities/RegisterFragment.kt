package com.example.proyecto2_android.activities

import android.os.Bundle
import android.text.method.HideReturnsTransformationMethod
import android.text.method.PasswordTransformationMethod
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import com.example.proyecto2_android.R
import com.example.proyecto2_android.models.Usuario
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.activities.network.RetrofitClient
import com.example.proyecto2_android.activities.utils.PasswordHelper
import kotlinx.coroutines.launch
import androidx.viewpager2.widget.ViewPager2

class RegisterFragment : Fragment() {

    private var passwordVisible = false
    private var confirmPasswordVisible = false
    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View? = inflater.inflate(R.layout.fragment_register, container, false)

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val etUsername = view.findViewById<EditText>(R.id.etUsername)
        val etEmail = view.findViewById<EditText>(R.id.etEmail)
        val etPassword = view.findViewById<EditText>(R.id.etPassword)
        val etConfirm = view.findViewById<EditText>(R.id.etConfirmPassword)
        val btnRegister = view.findViewById<LinearLayout>(R.id.btnRegister)
        val ivTogglePassword = view.findViewById<ImageView>(R.id.ivTogglePassword)
        val ivToggleConfirm = view.findViewById<ImageView>(R.id.ivToggleConfirmPassword)

        ivTogglePassword.setOnClickListener {
            passwordVisible = !passwordVisible
            if (passwordVisible) {
                etPassword.transformationMethod = HideReturnsTransformationMethod.getInstance()
                ivTogglePassword.setImageResource(R.drawable.ic_visibility)
            } else {
                etPassword.transformationMethod = PasswordTransformationMethod.getInstance()
                ivTogglePassword.setImageResource(R.drawable.ic_visibility_off)
            }
            etPassword.setSelection(etPassword.text.length)
        }

        ivToggleConfirm.setOnClickListener {
            confirmPasswordVisible = !confirmPasswordVisible
            if (confirmPasswordVisible) {
                etConfirm.transformationMethod = HideReturnsTransformationMethod.getInstance()
                ivToggleConfirm.setImageResource(R.drawable.ic_visibility)
            } else {
                etConfirm.transformationMethod = PasswordTransformationMethod.getInstance()
                ivToggleConfirm.setImageResource(R.drawable.ic_visibility_off)
            }
            etConfirm.setSelection(etConfirm.text.length)
        }

        btnRegister.setOnClickListener {
            val username = etUsername.text.toString().trim()
            val email = etEmail.text.toString().trim()
            val password = etPassword.text.toString().trim()
            val confirm = etConfirm.text.toString().trim()

            if (username.isEmpty() || email.isEmpty() || password.isEmpty() || confirm.isEmpty()) {
                Toast.makeText(requireContext(), "Completa todos los campos", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            if (password != confirm) {
                Toast.makeText(requireContext(), "Las contraseñas no coinciden", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            val nuevoUsuario = Usuario(
                rolUsuario = 1,
                nombreUsuario = username,
                contrasen = PasswordHelper.hash(password),
                correo = email
            )

            lifecycleScope.launch {
                try {
                    val response = api.registrarUsuario(nuevoUsuario)
                    if (response.isSuccessful) {
                        Toast.makeText(requireContext(), "Registro exitoso", Toast.LENGTH_SHORT).show()
                        etUsername.text.clear()
                        etEmail.text.clear()
                        etPassword.text.clear()
                        etConfirm.text.clear()
                        requireActivity().findViewById<ViewPager2>(R.id.viewPager).currentItem = 0
                    } else {
                        Toast.makeText(requireContext(), "Error al registrar", Toast.LENGTH_SHORT).show()
                    }
                } catch (e: Exception) {
                    Toast.makeText(requireContext(), "Error de conexión", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }
}