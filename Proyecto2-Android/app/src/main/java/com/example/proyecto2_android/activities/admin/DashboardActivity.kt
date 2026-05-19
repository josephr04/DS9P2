package com.example.proyecto2_android.activities.admin

import android.os.Bundle
import android.view.MenuItem
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.example.proyecto2_android.R
import com.example.proyecto2_android.adapters.CandidatoAdapter
import com.example.proyecto2_android.models.Candidato
import com.google.android.material.bottomnavigation.BottomNavigationView
import androidx.recyclerview.widget.RecyclerView
import android.widget.TextView

class DashboardActivity : AppCompatActivity() {

    private lateinit var rvCandidatos: RecyclerView
    private lateinit var tvVerTodos: TextView
    private lateinit var bottomNav: BottomNavigationView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_dashboard)

        rvCandidatos = findViewById(R.id.rvCandidatosRecientes)
        tvVerTodos   = findViewById(R.id.tvVerTodos)
        bottomNav    = findViewById(R.id.bottomNavAdmin)

        setupRecyclerView()
        setupBottomNav()

        tvVerTodos.setOnClickListener {
            // TODO: navegar a lista completa de postulantes
        }
    }

    private fun setupRecyclerView() {
        // Datos de ejemplo — reemplaza con tu fuente real
        val candidatos = listOf(
            Candidato("Ana García Méndez",  "Senior Software Engineer", "Oct 12, 2023"),
            Candidato("Carlos Ruiz Zepeda", "Project Manager",          "Oct 10, 2023"),
            Candidato("Roberto Valdez",     "UX Designer",              "Oct 09, 2023")
        )

        rvCandidatos.layoutManager = LinearLayoutManager(this)
        rvCandidatos.adapter = CandidatoAdapter(candidatos) { candidato ->
            // TODO: abrir detalle del candidato
        }
    }

    private fun setupBottomNav() {
        // Marca Dashboard como seleccionado al entrar
        bottomNav.selectedItemId = R.id.nav_dashboard

        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_dashboard    -> true   // ya estamos aquí
                R.id.nav_postulantes  -> {
                    // TODO: navegar a PostulantesActivity
                    true
                }
                R.id.nav_ajustes      -> {
                    // TODO: navegar a AjustesActivity
                    true
                }
                else -> false
            }
        }
    }
}