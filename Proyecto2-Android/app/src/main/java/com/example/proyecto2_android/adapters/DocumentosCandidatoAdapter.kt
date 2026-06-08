package com.example.proyecto2_android.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.models.DocumentoCandidato

class DocumentosCandidatoAdapter(
    private val documentos: List<DocumentoCandidato>,
    private val onItemClick: (DocumentoCandidato) -> Unit
) : RecyclerView.Adapter<DocumentosCandidatoAdapter.ViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_documento, parent, false)
        return ViewHolder(view)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val documento = documentos[position]
        holder.bind(documento, onItemClick)
    }

    override fun getItemCount(): Int = documentos.size

    class ViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val ivIcono: ImageView = itemView.findViewById(R.id.ivDocumentoIcono)
        private val tvNombre: TextView = itemView.findViewById(R.id.tvDocumentoNombre)
        private val tvFecha: TextView = itemView.findViewById(R.id.tvDocumentoFecha)
        private val tvTamaño: TextView = itemView.findViewById(R.id.tvDocumentoTamano)
        private val btnDescargar: ImageView = itemView.findViewById(R.id.btnDescargarDocumento)
        private val layoutDocumento: LinearLayout = itemView.findViewById(R.id.layoutDocumento)

        fun bind(documento: DocumentoCandidato, onItemClick: (DocumentoCandidato) -> Unit) {
            ivIcono.setImageResource(documento.icono)
            tvNombre.text = documento.nombre
            tvFecha.text = documento.fecha
            tvTamaño.text = documento.tamaño

            layoutDocumento.setOnClickListener {
                onItemClick(documento)
            }

            btnDescargar.setOnClickListener {
                onItemClick(documento)
            }
        }
    }
}