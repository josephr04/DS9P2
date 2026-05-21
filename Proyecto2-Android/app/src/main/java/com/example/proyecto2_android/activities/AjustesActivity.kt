package com.example.proyecto2_android.activities

import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.LinearLayout
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import com.example.proyecto2_android.LoginActivity
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.admin.DashboardActivity
import com.example.proyecto2_android.activities.admin.PostulantesActivity
import com.example.proyecto2_android.activities.postulante.*
import com.google.android.material.bottomnavigation.BottomNavigationView
import com.google.android.material.button.MaterialButton

class AjustesActivity : AppCompatActivity() {

    private lateinit var bottomNav: BottomNavigationView
    private lateinit var btnCerrarSesion: MaterialButton
    private lateinit var itemCambiarNombre: LinearLayout
    private lateinit var itemCambiarCorreo: LinearLayout
    private lateinit var itemCambiarContrasena: LinearLayout
    private var origen: String = "admin"

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_ajustes)

        bottomNav             = findViewById(R.id.bottomNavAjustes)
        btnCerrarSesion       = findViewById(R.id.btnCerrarSesion)
        itemCambiarNombre     = findViewById(R.id.itemCambiarNombre)
        itemCambiarCorreo     = findViewById(R.id.itemCambiarCorreo)
        itemCambiarContrasena = findViewById(R.id.itemCambiarContrasena)

        origen = intent.getStringExtra("origen") ?: "admin"

        setupBottomNav()
        setupClicks()
    }

    private fun setupBottomNav() {
        bottomNav.menu.clear()

        if (origen == "postulante") {
            bottomNav.inflateMenu(R.menu.menu_bottom_nav_postulante)
        } else {
            bottomNav.inflateMenu(R.menu.menu_bottom_nav_admin)
        }

        bottomNav.selectedItemId = R.id.nav_ajustes

        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_dashboard -> {
                    val intent = Intent(this, DashboardActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    finish() // Cerrar AjustesActivity al navegar
                    true
                }
                R.id.nav_perfil -> {
                    val intent = Intent(this, PerfilActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    finish() // Cerrar AjustesActivity al navegar
                    true
                }
                R.id.nav_postulantes -> {
                    // Navegar a PostulantesActivity (solo visible para admin)
                    val intent = Intent(this, PostulantesActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    finish()
                    true
                }
                R.id.nav_documentos -> {
                    val intent = Intent(this, DocumentosActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    finish() // Cerrar AjustesActivity al navegar
                    true
                }
                R.id.nav_ajustes -> true
                else -> false
            }
        }
    }

    override fun onResume() {
        super.onResume()
        // Asegurar que el item seleccionado sea el correcto
        if (::bottomNav.isInitialized) {
            bottomNav.selectedItemId = R.id.nav_ajustes
        }
    }

    private fun setupClicks() {
        // Cambiar Nombre de Usuario
        itemCambiarNombre.setOnClickListener {
            val intent = Intent(this, CambiarUsuarioActivity::class.java)
            intent.putExtra("origen", origen)
            startActivity(intent)
            overridePendingTransition(0, 0)
            // No usar finish() aquí para poder regresar con el botón de atrás
        }

        // Cambiar Correo Electrónico
        itemCambiarCorreo.setOnClickListener {
            val intent = Intent(this, CambiarCorreoActivity::class.java)
            intent.putExtra("origen", origen)
            startActivity(intent)
            overridePendingTransition(0, 0)
            // No usar finish() aquí para poder regresar con el botón de atrás
        }

        // Cambiar Contraseña
        itemCambiarContrasena.setOnClickListener {
            val intent = Intent(this, CambiarContrasenaActivity::class.java)
            intent.putExtra("origen", origen)
            startActivity(intent)
            overridePendingTransition(0, 0)
            // No usar finish() aquí para poder regresar con el botón de atrás
        }

        // Cerrar Sesión
        btnCerrarSesion.setOnClickListener {
            AlertDialog.Builder(this)
                .setTitle("Cerrar Sesión")
                .setMessage("¿Estás seguro que deseas cerrar sesión?")
                .setPositiveButton("Sí") { _, _ -> irAlLogin() }
                .setNegativeButton("Cancelar", null)
                .show()
        }
    }

    private fun irAlLogin() {
        val intent = Intent(this, LoginActivity::class.java)
        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        startActivity(intent)
        finish()
    }
}