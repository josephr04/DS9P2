package com.example.proyecto2_android.activities.admin

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.adapters.DocumentosCandidatoAdapter
import com.example.proyecto2_android.models.Candidato
import com.example.proyecto2_android.models.DocumentoCandidato
import com.google.android.material.card.MaterialCardView
import de.hdodenhof.circleimageview.CircleImageView

class PerfilCandidatoActivity : AppCompatActivity() {

    private lateinit var ivBack: ImageView
    private lateinit var ivAvatar: CircleImageView
    private lateinit var tvNombre: TextView
    private lateinit var tvPosicion: TextView
    private lateinit var tabInfoPersonal: LinearLayout
    private lateinit var tabDocumentos: LinearLayout
    private lateinit var viewIndicatorInfo: View
    private lateinit var viewIndicatorDocs: View
    private lateinit var layoutInfoPersonal: LinearLayout
    private lateinit var layoutDocumentos: LinearLayout
    private lateinit var rvDocumentos: RecyclerView
    private lateinit var tvTotalArchivos: TextView

    private var candidato: Candidato? = null
    private var documentosList = mutableListOf<DocumentoCandidato>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_perfil_candidato)

        initViews()
        recibirDatos()
        setupTabs()
        cargarDocumentos()
        setupRecyclerView()
    }

    private fun initViews() {
        ivBack = findViewById(R.id.ivBack)
        ivAvatar = findViewById(R.id.ivAvatar)
        tvNombre = findViewById(R.id.tvNombreCandidato)
        tvPosicion = findViewById(R.id.tvPosicionCandidato)
        tabInfoPersonal = findViewById(R.id.tabInfoPersonal)
        tabDocumentos = findViewById(R.id.tabDocumentos)
        viewIndicatorInfo = findViewById(R.id.viewIndicatorInfo)
        viewIndicatorDocs = findViewById(R.id.viewIndicatorDocs)
        layoutInfoPersonal = findViewById(R.id.layoutInfoPersonal)
        layoutDocumentos = findViewById(R.id.layoutDocumentos)
        rvDocumentos = findViewById(R.id.rvDocumentos)
        tvTotalArchivos = findViewById(R.id.tvTotalArchivos)

        ivBack.setOnClickListener {
            finish()
            overridePendingTransition(0, 0)
        }
    }

    private fun recibirDatos() {
        candidato = intent.getSerializableExtra("candidato") as? Candidato

        candidato?.let {
            tvNombre.text = it.nombre
            tvPosicion.text = it.posicion
            it.avatarResId?.let { resId ->
                ivAvatar.setImageResource(resId)
            }
            cargarDatosPersonales(it)
        }
    }

    private fun cargarDatosPersonales(candidato: Candidato) {
        // Información Personal
        findViewById<TextView>(R.id.tvFirstName).text = "Ana"
        findViewById<TextView>(R.id.tvMiddleName).text = "-"
        findViewById<TextView>(R.id.tvLastName).text = "García"
        findViewById<TextView>(R.id.tvSecondLastName).text = "Méndez"
        findViewById<TextView>(R.id.tvCedula).text = "01-0234-05678"
        findViewById<TextView>(R.id.tvGender).text = "Femenino"
        findViewById<TextView>(R.id.tvBirthDate).text = "15/05/1994"
        findViewById<TextView>(R.id.tvMaritalStatus).text = "Soltero/a"
        findViewById<TextView>(R.id.tvBloodType).text = "O+"
        findViewById<TextView>(R.id.tvAcademicLevel).text = "Licenciatura"

        // Información de Contacto
        findViewById<TextView>(R.id.tvPrimaryPhone).text = "225-4321"
        findViewById<TextView>(R.id.tvSecondaryPhone).text = "-"
        findViewById<TextView>(R.id.tvPrimaryCell).text = "6789-4321"
        findViewById<TextView>(R.id.tvSecondaryCell).text = "-"
        findViewById<TextView>(R.id.tvEmail).text = "ana.garcia@example.com"

        // Dirección
        findViewById<TextView>(R.id.tvProvince).text = "Panamá"
        findViewById<TextView>(R.id.tvDistrict).text = "Panamá"
        findViewById<TextView>(R.id.tvCorregimiento).text = "Bella Vista"
        findViewById<TextView>(R.id.tvUrbanization).text = "El Cangrejo"
        findViewById<TextView>(R.id.tvStreet).text = "Calle 52 Este"
        findViewById<TextView>(R.id.tvHouseBuilding).text = "PH Sky Tower, Apt 402"
        findViewById<TextView>(R.id.tvAdditionalDetails).text = "Behind store X, blue gate."

        // Postulación
        findViewById<TextView>(R.id.tvVacancyApplied).text = "Desarrollador Frontend Senior"
    }

    private fun cargarDocumentos() {
        documentosList = mutableListOf(
            DocumentoCandidato(
                nombre = "University_Diploma_AnaG.pdf",
                fecha = "Oct 12, 2023",
                tamaño = "2.4 MB",
                icono = R.drawable.ic_document
            ),
            DocumentoCandidato(
                nombre = "Google_Cloud_Architect_Cert.pdf",
                fecha = "Oct 14, 2023",
                tamaño = "1.1 MB",
                icono = R.drawable.ic_document
            ),
            DocumentoCandidato(
                nombre = "Curriculum_Vitae_Updated.docx",
                fecha = "Oct 10, 2023",
                tamaño = "840 KB",
                icono = R.drawable.ic_document
            ),
            DocumentoCandidato(
                nombre = "Recommendation_Letter_Symphony.pdf",
                fecha = "Oct 15, 2023",
                tamaño = "1.5 MB",
                icono = R.drawable.ic_document
            )
        )
        tvTotalArchivos.text = "${documentosList.size} Files Total"
    }

    private fun setupRecyclerView() {
        rvDocumentos.layoutManager = LinearLayoutManager(this)
        rvDocumentos.adapter = DocumentosCandidatoAdapter(documentosList) { documento ->
            // Aquí puedes abrir el documento
            android.widget.Toast.makeText(this, "Abrir: ${documento.nombre}", android.widget.Toast.LENGTH_SHORT).show()
        }
    }

    private fun setupTabs() {
        tabInfoPersonal.setOnClickListener {
            cambiarTab(0)
        }
        tabDocumentos.setOnClickListener {
            cambiarTab(1)
        }
    }

    private fun cambiarTab(index: Int) {
        when (index) {
            0 -> {
                // Tab Información Personal
                layoutInfoPersonal.visibility = View.VISIBLE
                layoutDocumentos.visibility = View.GONE
                viewIndicatorInfo.visibility = View.VISIBLE
                viewIndicatorDocs.visibility = View.INVISIBLE
                tabInfoPersonal.setBackgroundColor(ContextCompat.getColor(this, R.color.surface))
                tabDocumentos.setBackgroundColor(ContextCompat.getColor(this, R.color.surface))
            }
            1 -> {
                // Tab Documentos
                layoutInfoPersonal.visibility = View.GONE
                layoutDocumentos.visibility = View.VISIBLE
                viewIndicatorInfo.visibility = View.INVISIBLE
                viewIndicatorDocs.visibility = View.VISIBLE
                tabInfoPersonal.setBackgroundColor(ContextCompat.getColor(this, R.color.surface))
                tabDocumentos.setBackgroundColor(ContextCompat.getColor(this, R.color.surface))
            }
        }
    }
}