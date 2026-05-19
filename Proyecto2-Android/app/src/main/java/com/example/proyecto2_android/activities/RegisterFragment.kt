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

class RegisterFragment : Fragment() {

    private var passwordVisible = false
    private var confirmPasswordVisible = false

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        return inflater.inflate(R.layout.fragment_register, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val etPassword = view.findViewById<EditText>(R.id.etPassword)
        val etConfirm = view.findViewById<EditText>(R.id.etConfirmPassword)
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
    }
}