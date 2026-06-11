package com.example.proyecto2_android.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.models.DocumentoPostulante
import java.io.File

class DocumentosCandidatoAdapter(
    private val documentos: List<Pair<DocumentoPostulante, String>>,
    private val onVer: (String) -> Unit,
    private val onDescargar: (String) -> Unit,
    private val onDetalle: (DocumentoPostulante) -> Unit
) : RecyclerView.Adapter<DocumentosCandidatoAdapter.ViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_documento, parent, false)  // Asegúrate que el nombre sea correcto
        return ViewHolder(view)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val (documento, ruta) = documentos[position]
        holder.bind(documento, ruta, onVer, onDescargar, onDetalle)
    }

    override fun getItemCount(): Int = documentos.size

    class ViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        // Los IDs deben coincidir EXACTAMENTE con el XML
        private val ivIcono: ImageView = itemView.findViewById(R.id.ivDocumentoIcono)
        private val tvNombre: TextView = itemView.findViewById(R.id.tvDocumentoNombre)
        private val tvFecha: TextView = itemView.findViewById(R.id.tvDocumentoFecha)
        private val tvTamano: TextView = itemView.findViewById(R.id.tvDocumentoTamano)  // Sin ñ, usa "Tamano"
        private val btnVer: LinearLayout = itemView.findViewById(R.id.btnVerDocumento)
        private val btnDescargar: LinearLayout = itemView.findViewById(R.id.btnDescargarDocumento)

        fun bind(
            documento: DocumentoPostulante,
            ruta: String,
            onVer: (String) -> Unit,
            onDescargar: (String) -> Unit,
            onDetalle: (DocumentoPostulante) -> Unit
        ) {
            ivIcono.setImageResource(R.drawable.ic_document)
            tvNombre.text = documento.titulo
            tvFecha.text = documento.fechaEmision ?: "Fecha no disponible"
            itemView.setOnClickListener { onDetalle(documento) }

            // Calcular tamaño del archivo
            val file = File(ruta)
            val tamano = if (file.exists()) {
                val bytes = file.length()
                when {
                    bytes < 1024 -> "$bytes B"
                    bytes < 1024 * 1024 -> "${bytes / 1024} KB"
                    else -> "${bytes / (1024 * 1024)} MB"
                }
            } else {
                "Desconocido"
            }
            tvTamano.text = tamano  // Usa tvTamano (sin ñ)

            btnVer.setOnClickListener { onVer(ruta) }
            btnDescargar.setOnClickListener { onDescargar(ruta) }
        }
    }
}