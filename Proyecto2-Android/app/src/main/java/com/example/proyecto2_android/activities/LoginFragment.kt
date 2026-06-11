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
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.admin.DashboardActivity
import com.example.proyecto2_android.activities.postulante.PerfilActivity
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.activities.network.RetrofitClient
import kotlinx.coroutines.launch
import org.json.JSONObject

class LoginFragment : Fragment() {

    private var passwordVisible = false
    private lateinit var progressBar: ProgressBar

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
        progressBar = view.findViewById(R.id.progressBar)

        // ── NUEVO: link de olvidaste contraseña ──
        val tvForgotPassword = view.findViewById<TextView>(R.id.tvForgotPassword)
        tvForgotPassword.setOnClickListener {
            startActivity(Intent(requireContext(), ForgotPasswordActivity::class.java))
            requireActivity().overridePendingTransition(0, 0)
        }

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

            setLoading(true)

            lifecycleScope.launch {
                try {
                    val request = mapOf(
                        "nombre_usuario" to user,
                        "contrasena" to pass
                    )

                    val response = api.login(request)

                    setLoading(false)

                    if (response.isSuccessful) {
                        val responseBody = response.body()
                        val success = responseBody?.get("success") as? Boolean ?: false

                        if (success) {
                            val usuarioData = responseBody?.get("usuario") as? Map<*, *>

                            if (usuarioData != null) {
                                val idUsuario = (usuarioData["idUsuario"] as? Number)?.toInt() ?: 0
                                val nombreUsuario = usuarioData["nombreUsuario"] as? String ?: ""
                                val correo = usuarioData["correo"] as? String ?: ""
                                val rolUsuario = (usuarioData["rolUsuario"] as? Number)?.toInt() ?: 1

                                val prefs = requireContext().getSharedPreferences("careerport", Context.MODE_PRIVATE)
                                prefs.edit()
                                    .putInt("id_usuario", idUsuario)
                                    .putString("nombre_usuario", nombreUsuario)
                                    .putString("correo_usuario", correo)
                                    .putInt("rol_usuario", rolUsuario)
                                    .apply()

                                layoutError.visibility = View.GONE

                                if (rolUsuario == 0) {
                                    startActivity(Intent(requireContext(), DashboardActivity::class.java))
                                } else {
                                    startActivity(Intent(requireContext(), PerfilActivity::class.java))
                                }
                                requireActivity().finish()
                            } else {
                                layoutError.visibility = View.VISIBLE
                                Toast.makeText(requireContext(), "Error al obtener datos del usuario", Toast.LENGTH_SHORT).show()
                            }
                        } else {
                            val mensaje = responseBody?.get("mensaje") as? String ?: "Credenciales incorrectas"
                            layoutError.visibility = View.VISIBLE
                            Toast.makeText(requireContext(), mensaje, Toast.LENGTH_SHORT).show()
                        }
                    } else {
                        val errorBody = response.errorBody()?.string()
                        val mensaje = if (!errorBody.isNullOrEmpty()) {
                            try {
                                val json = JSONObject(errorBody)
                                json.getString("mensaje")
                            } catch (e: Exception) {
                                "Error al iniciar sesión"
                            }
                        } else {
                            "Error al iniciar sesión"
                        }
                        layoutError.visibility = View.VISIBLE
                        Toast.makeText(requireContext(), mensaje, Toast.LENGTH_SHORT).show()
                    }
                } catch (e: Exception) {
                    setLoading(false)
                    layoutError.visibility = View.VISIBLE
                    Toast.makeText(requireContext(), "Error de conexión: ${e.message}", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    private fun setLoading(loading: Boolean) {
        if (::progressBar.isInitialized) {
            progressBar.visibility = if (loading) View.VISIBLE else View.GONE
        }
        val btnLogin = view?.findViewById<LinearLayout>(R.id.btnLogin)
        btnLogin?.isEnabled = !loading
        btnLogin?.alpha = if (loading) 0.6f else 1.0f
    }
}