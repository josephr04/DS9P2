package com.example.proyecto2_android.activities

import android.os.Bundle
import android.text.method.HideReturnsTransformationMethod
import android.text.method.PasswordTransformationMethod
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.EditText
import android.widget.ImageView
import androidx.fragment.app.Fragment
import com.example.proyecto2_android.R

import android.content.Intent
import android.widget.LinearLayout
import com.example.proyecto2_android.activities.admin.DashboardActivity
import com.example.proyecto2_android.activities.postulante.PerfilActivity

class LoginFragment : Fragment() {

    private var passwordVisible = false

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        return inflater.inflate(R.layout.fragment_login, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val etUsername = view.findViewById<EditText>(R.id.etUsername)
        val etPassword = view.findViewById<EditText>(R.id.etPassword)
        val btnLogin = view.findViewById<LinearLayout>(R.id.btnLogin)
        val layoutError = view.findViewById<LinearLayout>(R.id.layoutError)
        val ivToggle = view.findViewById<ImageView>(R.id.ivTogglePassword)

        // Toggle contraseña
        ivToggle.setOnClickListener {
            passwordVisible = !passwordVisible
            if (passwordVisible) {
                etPassword.transformationMethod = HideReturnsTransformationMethod.getInstance()
                ivToggle.setImageResource(R.drawable.ic_visibility)
            } else {
                etPassword.transformationMethod = PasswordTransformationMethod.getInstance()
                ivToggle.setImageResource(R.drawable.ic_visibility_off)
            }
            etPassword.setSelection(etPassword.text.length)
        }

        // Login hardcodeado temporal
        btnLogin.setOnClickListener {
            val user = etUsername.text.toString().trim()
            val pass = etPassword.text.toString().trim()

            when {
                user == "admin" && pass == "123" -> {
                    // Ir al Dashboard del admin
                    startActivity(Intent(requireContext(), DashboardActivity::class.java))
                    requireActivity().finish()
                }
                user == "usuario" && pass == "123" -> {
                    // Ir al Perfil del postulante
                    startActivity(Intent(requireContext(), PerfilActivity::class.java))
                    requireActivity().finish()
                }
                else -> {
                    layoutError.visibility = View.VISIBLE
                }
            }
        }
    }
}