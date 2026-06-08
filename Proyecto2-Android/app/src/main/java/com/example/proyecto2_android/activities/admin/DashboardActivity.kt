package com.example.proyecto2_android.activities.admin

import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.view.View
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import androidx.lifecycle.lifecycleScope
import com.example.proyecto2_android.R
import com.example.proyecto2_android.adapters.CandidatoAdapter
import com.example.proyecto2_android.activities.AjustesActivity
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.activities.network.RetrofitClient
import com.example.proyecto2_android.models.Candidato
import com.google.android.material.bottomnavigation.BottomNavigationView
import kotlinx.coroutines.launch

class DashboardActivity : AppCompatActivity() {

    private lateinit var rvCandidatos: RecyclerView
    private lateinit var tvVerTodos: TextView
    private lateinit var bottomNav: BottomNavigationView

    // Views para estadísticas
    private lateinit var tvTotalApplicants: TextView
    private lateinit var tvTotalDocuments: TextView
    private lateinit var tvPostulantesListos: TextView
    private lateinit var tvEdadPromedio: TextView

    private lateinit var progressBar: ProgressBar

    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_dashboard)

        initViews()
        setupBottomNav()
        cargarEstadisticas()

        tvVerTodos.setOnClickListener {
            val intent = Intent(this, PostulantesActivity::class.java)
            startActivity(intent)
            overridePendingTransition(0, 0)
        }
    }

    private fun initViews() {
        rvCandidatos = findViewById(R.id.rvCandidatosRecientes)
        tvVerTodos = findViewById(R.id.tvVerTodos)
        bottomNav = findViewById(R.id.bottomNavAjustes)
        progressBar = findViewById(R.id.progressBarDashboard)

        // Inicializar estadísticas
        tvTotalApplicants = findViewById(R.id.tvTotalApplicants)
        tvTotalDocuments = findViewById(R.id.tvTotalDocuments)
        tvPostulantesListos = findViewById(R.id.tvPostulantesListos)
        tvEdadPromedio = findViewById(R.id.tvEdadPromedio)

        // Mostrar valores por defecto mientras carga
        tvTotalApplicants.text = "..."
        tvTotalDocuments.text = "..."
        tvPostulantesListos.text = "..."
        tvEdadPromedio.text = "..."

        // Mostrar datos de prueba mientras carga
        setupRecyclerViewConDatosPrueba()
    }

    private fun cargarEstadisticas() {
        progressBar.visibility = View.VISIBLE

        lifecycleScope.launch {
            try {
                val response = api.getDashboardStats()

                progressBar.visibility = View.GONE

                if (response.isSuccessful && response.body()?.get("success") == true) {
                    val data = response.body()?.get("data") as? Map<*, *>

                    if (data != null) {
                        val totalPostulantes = (data["totalPostulantes"] as? Number)?.toInt() ?: 0
                        val totalDocumentos = (data["totalDocumentos"] as? Number)?.toInt() ?: 0
                        val postulantesListos = (data["postulantesListos"] as? Number)?.toInt() ?: 0
                        val edadPromedio = (data["edadPromedio"] as? Number)?.toInt() ?: 0

                        // Actualizar valores
                        tvTotalApplicants.text = totalPostulantes.toString()
                        tvTotalDocuments.text = totalDocumentos.toString()
                        tvPostulantesListos.text = postulantesListos.toString()
                        tvEdadPromedio.text = "$edadPromedio años"

                        // Cargar postulantes recientes
                        val recientes = data["postulantesRecientes"] as? List<*>
                        if (recientes != null && recientes.isNotEmpty()) {
                            setupRecyclerViewConDatosReales(recientes)
                        } else {
                            setupRecyclerViewConDatosPrueba()
                        }
                    } else {
                        setupRecyclerViewConDatosPrueba()
                    }
                } else {
                    mostrarErrorYCargarPrueba()
                }
            } catch (e: Exception) {
                progressBar.visibility = View.GONE
                mostrarErrorYCargarPrueba()
                Toast.makeText(this@DashboardActivity, "Error de conexión: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun setupRecyclerViewConDatosReales(recientes: List<*>) {
        val candidatos = mutableListOf<Candidato>()

        for (item in recientes) {
            val map = item as? Map<*, *>
            if (map != null) {
                val nombre = map["nombreCompleto"] as? String ?: ""
                val perfil = map["perfil"] as? String ?: "Postulante"
                val idPostulante = (map["idPostulante"] as? Number)?.toInt() ?: 0
                val idUsuario = (map["idUsuario"] as? Number)?.toInt() ?: 0

                val vacante = when (perfil) {
                    "LICENCIATURA" -> "Analista de Sistemas"
                    "MAESTRIA" -> "Project Manager"
                    "TECNICO" -> "Soporte Técnico"
                    else -> "Vacante disponible"
                }
                candidatos.add(Candidato(nombre, vacante, "", null, idPostulante, idUsuario))
            }
        }

        if (candidatos.isEmpty()) {
            setupRecyclerViewConDatosPrueba()
        } else {
            rvCandidatos.layoutManager = LinearLayoutManager(this)
            rvCandidatos.adapter = CandidatoAdapter(candidatos) { candidato ->
                val intent = Intent(this, PerfilCandidatoActivity::class.java)
                intent.putExtra("idPostulante", candidato.idPostulante)
                intent.putExtra("idUsuario", candidato.idUsuario)
                intent.putExtra("nombre", candidato.nombre)
                startActivity(intent)
                overridePendingTransition(0, 0)
            }
        }
    }

    private fun setupRecyclerViewConDatosPrueba() {
        val candidatos = listOf(
            Candidato("Ana García Méndez", "Analista de Sistemas", ""),
            Candidato("Carlos Ruiz Zepeda", "Project Manager", ""),
            Candidato("Roberto Valdez", "Soporte Técnico", "")
        )
        rvCandidatos.layoutManager = LinearLayoutManager(this)
        rvCandidatos.adapter = CandidatoAdapter(candidatos) { candidato ->
            Toast.makeText(this, "Ver detalle de: ${candidato.nombre}", Toast.LENGTH_SHORT).show()
        }
    }

    private fun mostrarErrorYCargarPrueba() {
        tvTotalApplicants.text = "0"
        tvTotalDocuments.text = "0"
        tvPostulantesListos.text = "0"
        tvEdadPromedio.text = "0 años"
        setupRecyclerViewConDatosPrueba()
    }

    private fun setupBottomNav() {
        bottomNav.selectedItemId = R.id.nav_dashboard

        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_dashboard -> true
                R.id.nav_postulantes -> {
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
        cargarEstadisticas()
    }
}