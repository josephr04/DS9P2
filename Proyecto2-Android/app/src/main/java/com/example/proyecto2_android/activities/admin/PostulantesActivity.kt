package com.example.proyecto2_android.activities.admin

import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.view.View
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.AjustesActivity
import com.example.proyecto2_android.activities.network.ApiService
import com.example.proyecto2_android.activities.network.RetrofitClient
import com.example.proyecto2_android.adapters.PostulantesAdapter
import com.example.proyecto2_android.models.Candidato
import com.google.android.material.bottomnavigation.BottomNavigationView
import com.google.android.material.button.MaterialButton
import com.google.android.material.card.MaterialCardView
import kotlinx.coroutines.launch

class PostulantesActivity : AppCompatActivity() {

    private lateinit var bottomNav: BottomNavigationView
    private lateinit var etSearch: EditText
    private lateinit var btnFilters: MaterialButton
    private lateinit var btnApplyFilter: MaterialButton
    private lateinit var rvPostulantes: RecyclerView
    private lateinit var tvTotalPostulantes: TextView
    private lateinit var btnPrevious: LinearLayout
    private lateinit var btnNext: LinearLayout
    private lateinit var tvPageInfo: TextView
    private lateinit var filterPanel: MaterialCardView
    private lateinit var ivCloseFilters: ImageView
    private lateinit var ivClearSearch: ImageView
    private lateinit var progressBar: ProgressBar

    private var currentPage = 1
    private val itemsPerPage = 6
    private var allPostulantes = mutableListOf<Candidato>()
    private var filteredPostulantes = mutableListOf<Candidato>()

    private val api: ApiService by lazy {
        RetrofitClient.instance.create(ApiService::class.java)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_postulantes)

        initViews()
        setupBottomNav()
        setupSearchAndFilters()
        cargarPostulantesDesdeAPI()
        setupPagination()
    }

    private fun initViews() {
        bottomNav = findViewById(R.id.bottomNavPostulantes)
        etSearch = findViewById(R.id.etSearchPostulantes)
        btnFilters = findViewById(R.id.btnFilters)
        btnApplyFilter = findViewById(R.id.btnApplyFilter)
        rvPostulantes = findViewById(R.id.rvPostulantes)
        tvTotalPostulantes = findViewById(R.id.tvTotalPostulantes)
        btnPrevious = findViewById(R.id.btnPrevious)
        btnNext = findViewById(R.id.btnNext)
        tvPageInfo = findViewById(R.id.tvPageInfo)
        filterPanel = findViewById(R.id.filterPanel)
        ivCloseFilters = findViewById(R.id.ivCloseFilters)
        ivClearSearch = findViewById(R.id.ivClearSearch)
        progressBar = findViewById(R.id.progressBarPostulantes)
    }

    private fun setupBottomNav() {
        bottomNav.selectedItemId = R.id.nav_postulantes

        bottomNav.setOnItemSelectedListener { item: MenuItem ->
            when (item.itemId) {
                R.id.nav_dashboard -> {
                    val intent = Intent(this, DashboardActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    finish()
                    true
                }
                R.id.nav_postulantes -> true
                R.id.nav_ajustes -> {
                    val intent = Intent(this, AjustesActivity::class.java)
                    intent.putExtra("origen", "admin")
                    intent.flags = Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                    startActivity(intent)
                    overridePendingTransition(0, 0)
                    finish()
                    true
                }
                else -> false
            }
        }
    }

    private fun setupSearchAndFilters() {
        btnFilters.setOnClickListener {
            if (filterPanel.visibility == View.GONE) {
                filterPanel.visibility = View.VISIBLE
            } else {
                filterPanel.visibility = View.GONE
            }
        }

        ivCloseFilters.setOnClickListener {
            filterPanel.visibility = View.GONE
        }

        ivClearSearch.setOnClickListener {
            etSearch.text.clear()
            aplicarFiltros()
        }

        btnApplyFilter.setOnClickListener {
            aplicarFiltros()
            filterPanel.visibility = View.GONE
        }

        etSearch.setOnEditorActionListener { _, _, _ ->
            aplicarFiltros()
            true
        }
    }

    private fun cargarPostulantesDesdeAPI() {
        progressBar.visibility = View.VISIBLE

        lifecycleScope.launch {
            try {
                val response = api.getPostulantes()

                progressBar.visibility = View.GONE

                if (response.isSuccessful) {
                    val postulantes = response.body() ?: emptyList()
                    allPostulantes.clear()

                    for (postulante in postulantes) {
                        // Construir nombre completo correctamente
                        val nombreCompleto = buildString {
                            append(postulante["nombre"] as? String ?: "")
                            val nombre2 = postulante["nombre2"] as? String
                            if (!nombre2.isNullOrBlank()) append(" $nombre2")
                            val apellido = postulante["apellido"] as? String
                            if (!apellido.isNullOrBlank()) append(" $apellido")
                            val apellido2 = postulante["apellido2"] as? String
                            if (!apellido2.isNullOrBlank()) append(" $apellido2")
                        }.trim()

                        val nombreFinal = if (nombreCompleto.isBlank()) {
                            "Postulante #${postulante["idPostulante"]}"
                        } else {
                            nombreCompleto
                        }

                        // Obtener el rango académico del campo correcto
                        val rangoAcademico = (postulante["rangoAcademico"] as? Number)?.toInt() ?: 0
                        val perfil = when (rangoAcademico) {
                            1 -> "DIPLOMADO"
                            2 -> "TECNICO"
                            3 -> "LICENCIATURA"
                            4 -> "POSTGRADO"
                            5 -> "MAESTRIA"
                            6 -> "DOCTORADO"
                            else -> "Postulante"
                        }

                        allPostulantes.add(
                            Candidato(
                                nombre = nombreFinal,
                                posicion = perfil,
                                fecha = "",
                                avatarResId = R.drawable.ic_person,
                                idPostulante = (postulante["idPostulante"] as? Number)?.toInt() ?: 0,
                                idUsuario = (postulante["idUsuario"] as? Number)?.toInt() ?: 0
                            )
                        )
                    }

                    filteredPostulantes = allPostulantes.toMutableList()
                    tvTotalPostulantes.text = "${filteredPostulantes.size}"
                    currentPage = 1
                    updateRecyclerView()
                } else {
                    Toast.makeText(this@PostulantesActivity, "Error al cargar postulantes", Toast.LENGTH_SHORT).show()
                    cargarDatosPrueba()
                }
            } catch (e: Exception) {
                progressBar.visibility = View.GONE
                Toast.makeText(this@PostulantesActivity, "Error de conexión: ${e.message}", Toast.LENGTH_SHORT).show()
                cargarDatosPrueba()
            }
        }
    }

    private fun cargarDatosPrueba() {
        allPostulantes = mutableListOf(
            Candidato("Ana García Méndez", "LICENCIATURA", "", R.drawable.ic_person),
            Candidato("Carlos Ruiz Zepeda", "MAESTRIA", "", R.drawable.ic_person),
            Candidato("Roberto Valdez", "TECNICO", "", R.drawable.ic_person)
        )
        filteredPostulantes = allPostulantes.toMutableList()
        tvTotalPostulantes.text = "${filteredPostulantes.size}"
        updateRecyclerView()
    }

    private fun aplicarFiltros() {
        val query = etSearch.text.toString().trim().lowercase()

        filteredPostulantes = if (query.isEmpty()) {
            allPostulantes.toMutableList()
        } else {
            allPostulantes.filter { candidato ->
                candidato.nombre.lowercase().contains(query) ||
                        candidato.posicion.lowercase().contains(query)
            }.toMutableList()
        }

        tvTotalPostulantes.text = "${filteredPostulantes.size}"
        currentPage = 1
        updateRecyclerView()

        ivClearSearch.visibility = if (query.isNotEmpty()) View.VISIBLE else View.GONE
    }

    private fun setupPagination() {
        updatePaginationButtons()

        btnPrevious.setOnClickListener {
            if (currentPage > 1) {
                currentPage--
                updateRecyclerView()
            }
        }

        btnNext.setOnClickListener {
            val totalPages = if (filteredPostulantes.isEmpty()) 1 else (filteredPostulantes.size + itemsPerPage - 1) / itemsPerPage
            if (currentPage < totalPages) {
                currentPage++
                updateRecyclerView()
            }
        }
    }

    private fun updateRecyclerView() {
        val startIndex = (currentPage - 1) * itemsPerPage
        val endIndex = minOf(startIndex + itemsPerPage, filteredPostulantes.size)
        val pageItems = if (startIndex < filteredPostulantes.size) {
            filteredPostulantes.subList(startIndex, endIndex)
        } else {
            emptyList()
        }

        rvPostulantes.layoutManager = LinearLayoutManager(this)
        rvPostulantes.adapter = PostulantesAdapter(pageItems) { candidato ->
            val intent = Intent(this, PerfilCandidatoActivity::class.java)
            intent.putExtra("idPostulante", candidato.idPostulante)
            intent.putExtra("idUsuario", candidato.idUsuario)
            intent.putExtra("nombre", candidato.nombre)
            startActivity(intent)
            overridePendingTransition(0, 0)
        }

        updatePaginationButtons()
    }

    private fun updatePaginationButtons() {
        val totalPages = if (filteredPostulantes.isEmpty()) 1 else (filteredPostulantes.size + itemsPerPage - 1) / itemsPerPage
        tvPageInfo.text = "Página $currentPage de $totalPages"

        btnPrevious.isEnabled = currentPage > 1
        btnNext.isEnabled = currentPage < totalPages && filteredPostulantes.isNotEmpty()

        btnPrevious.alpha = if (btnPrevious.isEnabled) 1.0f else 0.5f
        btnNext.alpha = if (btnNext.isEnabled) 1.0f else 0.5f
    }

    override fun onResume() {
        super.onResume()
        bottomNav.selectedItemId = R.id.nav_postulantes
    }
}