package com.example.proyecto2_android.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.models.DocumentoPostulante

class DocumentoAdapter(
    private val lista: MutableList<DocumentoPostulante>,
    private val onVer: (DocumentoPostulante) -> Unit,
    private val onDescargar: (DocumentoPostulante) -> Unit,
    private val onEliminar: (DocumentoPostulante) -> Unit,
    private val onDetalle: (DocumentoPostulante) -> Unit  // ← nuevo
) : RecyclerView.Adapter<DocumentoAdapter.VH>() {

    inner class VH(val v: View) : RecyclerView.ViewHolder(v) {
        val nombre: TextView = v.findViewById(R.id.tvDocumentoNombre)
        val tipo: TextView       = v.findViewById(R.id.tvDocumentoTipo)
        val fecha: TextView      = v.findViewById(R.id.tvDocumentoFecha)
        val btnVer: ImageView    = v.findViewById(R.id.btnVerDocumento)
        val btnDesc: ImageView   = v.findViewById(R.id.btnDescargarDocumento)
        val btnElim: ImageView = v.findViewById(R.id.btnEliminarDocumento)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int) =
        VH(LayoutInflater.from(parent.context).inflate(R.layout.item_documento_candidato, parent, false))

    override fun getItemCount() = lista.size

    override fun onBindViewHolder(h: VH, pos: Int) {
        val doc = lista[pos]
        h.nombre.text = doc.titulo
        h.tipo.text   = "Documento"
        h.fecha.text  = "Subido el ${doc.fechaEmision}"
        h.btnVer.setOnClickListener  { onVer(doc) }
        h.btnDesc.setOnClickListener { onDescargar(doc) }
        h.btnElim.setOnClickListener { onEliminar(doc) }
        h.v.setOnClickListener       { onDetalle(doc) }  // ← nuevo
    }

    fun eliminar(pos: Int) {
        lista.removeAt(pos)
        notifyItemRemoved(pos)
    }
}