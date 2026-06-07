package com.example.proyecto2_android.activities.utils

import java.security.MessageDigest

object PasswordHelper {
    fun hash(password: String): String {
        val md = MessageDigest.getInstance("SHA-256")
        val digest = md.digest(password.toByteArray())
        return digest.fold("") { str, it -> str + "%02x".format(it) }
    }

    fun verify(password: String, hash: String): Boolean {
        return hash(password) == hash
    }
}