package com.example.proyecto2_android.activities.admin

import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.adapters.CandidatoAdapter
import com.example.proyecto2_android.models.Candidato
import com.google.android.material.bottomnavigation.BottomNavigationView
import com.example.proyecto2_android.activities.AjustesActivity

class DashboardActivity : AppCompatActivity() {

    private lateinit var rvCandidatos: RecyclerView
    private lateinit var tvVerTodos: TextView
    private lateinit var bottomNav: BottomNavigationView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_dashboard)

        rvCandidatos = findViewById(R.id.rvCandidatosRecientes)
        tvVerTodos   = findViewById(R.id.tvVerTodos)
        bottomNav    = findViewById(R.id.bottomNavAjustes)

        setupRecyclerView()
        setupBottomNav()

        tvVerTodos.setOnClickListener {
            // Ir a la pantalla de postulantes completa
            val intent = Intent(this, PostulantesActivity::class.java)
            startActivity(intent)
            overridePendingTransition(0, 0)
        }
    }

    private fun setupRecyclerView() {
        val candidatos = listOf(
            Candidato("Ana García Méndez",  "Senior Software Engineer", "Oct 12, 2023"),
            Candidato("Carlos Ruiz Zepeda", "Project Manager",          "Oct 10, 2023"),
            Candidato("Roberto Valdez",     "UX Designer",              "Oct 09, 2023")
        )
        rvCandidatos.layoutManager = LinearLayoutManager(this)
        rvCandidatos.adapter = CandidatoAdapter(candidatos) { candidato ->
            // Aquí puedes abrir el detalle del candidato
            android.widget.Toast.makeText(this, "Ver detalle de: ${candidato.nombre}", android.widget.Toast.LENGTH_SHORT).show()
        }
    }

    private fun setupBottomNav() {
        bottomNav.selectedItemId = R.id.nav_dashboard

        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_dashboard -> true
                R.id.nav_postulantes -> {
                    // Iniciar PostulantesActivity
                    val intent = Intent(this, PostulantesActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    true
                }
                R.id.nav_ajustes -> {
                    val intent = Intent(this, AjustesActivity::class.java)
                    intent.putExtra("origen", "admin")
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    true
                }
                else -> false
            }
        }
    }

    override fun onResume() {
        super.onResume()
        bottomNav.selectedItemId = R.id.nav_dashboard
    }
}