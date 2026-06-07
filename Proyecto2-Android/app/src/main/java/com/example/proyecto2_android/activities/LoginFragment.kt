package com.example.proyecto2_android.activities

import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.text.method.HideReturnsTransformationMethod
import android.text.method.PasswordTransformationMethod
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.admin.DashboardActivity
import com.example.proyecto2_android.activities.postulante.PerfilActivity
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.activities.network.RetrofitClient
import com.example.proyecto2_android.activities.utils.PasswordHelper
import kotlinx.coroutines.launch

class LoginFragment : Fragment() {

    private var passwordVisible = false
    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View? = inflater.inflate(R.layout.fragment_login, container, false)

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val etUsername = view.findViewById<EditText>(R.id.etUsername)
        val etPassword = view.findViewById<EditText>(R.id.etPassword)
        val btnLogin = view.findViewById<LinearLayout>(R.id.btnLogin)
        val layoutError = view.findViewById<LinearLayout>(R.id.layoutError)
        val ivToggle = view.findViewById<ImageView>(R.id.ivTogglePassword)

        val watcher = object : android.text.TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {
                layoutError.visibility = View.GONE
            }
            override fun afterTextChanged(s: android.text.Editable?) {}
        }

        etUsername.addTextChangedListener(watcher)
        etPassword.addTextChangedListener(watcher)

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

        btnLogin.setOnClickListener {
            val user = etUsername.text.toString().trim()
            val pass = etPassword.text.toString().trim()

            if (user.isEmpty() || pass.isEmpty()) {
                layoutError.visibility = View.VISIBLE
                return@setOnClickListener
            }

            lifecycleScope.launch {
                try {
                    val response = api.getUsuarios()
                    if (response.isSuccessful) {
                        val usuarios = response.body() ?: emptyList()
                        val usuario = usuarios.find { it.nombreUsuario == user }

                        if (usuario != null && PasswordHelper.verify(pass, usuario.contrasen)) {
                            // Agrega esto antes del startActivity
                            val prefs = requireContext().getSharedPreferences("careerport", Context.MODE_PRIVATE)
                            prefs.edit()
                                .putString("correo_usuario", usuario.correo)
                                .putInt("id_usuario", usuario.idUsuario ?: 0)
                                .putString("nombre_usuario", usuario.nombreUsuario)
                                .apply()

                            layoutError.visibility = View.GONE
                            if (usuario.rolUsuario == 0) {
                                startActivity(Intent(requireContext(), DashboardActivity::class.java))
                            } else {
                                startActivity(Intent(requireContext(), PerfilActivity::class.java))
                            }
                            requireActivity().finish()
                        } else {
                            layoutError.visibility = View.VISIBLE
                        }
                    } else {
                        layoutError.visibility = View.VISIBLE
                    }
                } catch (e: Exception) {
                    layoutError.visibility = View.VISIBLE
                }
            }
        }
    }
}