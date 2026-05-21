package com.example.proyecto2_android.activities.admin

import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.EditText
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.activities.AjustesActivity
import com.example.proyecto2_android.adapters.PostulantesAdapter
import com.example.proyecto2_android.models.Candidato
import com.google.android.material.bottomnavigation.BottomNavigationView
import com.google.android.material.button.MaterialButton
import com.google.android.material.card.MaterialCardView

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

    private var currentPage = 1
    private val itemsPerPage = 6
    private var allPostulantes = mutableListOf<Candidato>()
    private var filteredPostulantes = mutableListOf<Candidato>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_postulantes)

        initViews()
        setupBottomNav()
        setupSearchAndFilters()
        loadSampleData()
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
        // Mostrar/ocultar panel de filtros
        btnFilters.setOnClickListener {
            if (filterPanel.visibility == android.view.View.GONE) {
                filterPanel.visibility = android.view.View.VISIBLE
            } else {
                filterPanel.visibility = android.view.View.GONE
            }
        }

        // Cerrar panel de filtros
        ivCloseFilters.setOnClickListener {
            filterPanel.visibility = android.view.View.GONE
        }

        // Limpiar búsqueda
        ivClearSearch.setOnClickListener {
            etSearch.text.clear()
            aplicarFiltros()
        }

        // Aplicar filtros
        btnApplyFilter.setOnClickListener {
            aplicarFiltros()
            filterPanel.visibility = android.view.View.GONE
        }

        // Búsqueda en tiempo real
        etSearch.setOnEditorActionListener { _, _, _ ->
            aplicarFiltros()
            true
        }
    }

    private fun loadSampleData() {
        allPostulantes = mutableListOf(
            Candidato("Ana García Méndez", "Senior Software Engineer", "Oct 12, 2023", R.drawable.ic_person),
            Candidato("Marco Aurelio", "Desarrollador Fullstack", "Oct 12, 2023", R.drawable.ic_person),
            Candidato("Lucia Rojas", "Senior Product Designer", "Oct 10, 2023", R.drawable.ic_person),
            Candidato("Santiago Vargas", "Data Analyst", "Oct 09, 2023", R.drawable.ic_person),
            Candidato("Elena Méndez", "Marketing Lead", "Oct 08, 2023", R.drawable.ic_person),
            Candidato("Javier Paredes", "DevOps Engineer", "Oct 07, 2023", R.drawable.ic_person),
            Candidato("Adriana Flores", "Technical Recruiter", "Oct 06, 2023", R.drawable.ic_person),
            Candidato("Carlos Ruiz", "Backend Developer", "Oct 05, 2023", R.drawable.ic_person),
            Candidato("Maria Gonzalez", "Frontend Developer", "Oct 04, 2023", R.drawable.ic_person),
            Candidato("Fernando Diaz", "QA Engineer", "Oct 03, 2023", R.drawable.ic_person),
            Candidato("Valentina Silva", "Product Manager", "Oct 02, 2023", R.drawable.ic_person),
            Candidato("Andres Lopez", "Mobile Developer", "Oct 01, 2023", R.drawable.ic_person),
            Candidato("Carolina Ruiz", "UX Researcher", "Sep 30, 2023", R.drawable.ic_person)
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

        // Mostrar/ocultar botón de limpiar búsqueda
        ivClearSearch.visibility = if (query.isNotEmpty()) android.view.View.VISIBLE else android.view.View.GONE
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
            val totalPages = (filteredPostulantes.size + itemsPerPage - 1) / itemsPerPage
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
            // Navegar al perfil completo del candidato
            val intent = Intent(this, PerfilCandidatoActivity::class.java)
            intent.putExtra("candidato", candidato)
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

        // Cambiar opacidad cuando está deshabilitado
        btnPrevious.alpha = if (btnPrevious.isEnabled) 1.0f else 0.5f
        btnNext.alpha = if (btnNext.isEnabled) 1.0f else 0.5f
    }

    override fun onResume() {
        super.onResume()
        bottomNav.selectedItemId = R.id.nav_postulantes
    }
}