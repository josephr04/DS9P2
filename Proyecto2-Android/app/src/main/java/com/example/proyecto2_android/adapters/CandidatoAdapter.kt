// ─── adapters/CandidatoAdapter.kt ────────────────────────────────────────────
package com.example.proyecto2_android.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.models.Candidato

class CandidatoAdapter(
    private val items: List<Candidato>,
    private val onVerDetalle: (Candidato) -> Unit
) : RecyclerView.Adapter<CandidatoAdapter.ViewHolder>() {

    inner class ViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val tvNombre: TextView     = itemView.findViewById(R.id.tvCandidatoNombre)
        val tvPosicion: TextView   = itemView.findViewById(R.id.tvCandidatoPosicion)
        val tvFecha: TextView      = itemView.findViewById(R.id.tvCandidatoFecha)
        val btnDetalle: LinearLayout = itemView.findViewById(R.id.btnVerDetalle)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_candidato_reciente, parent, false)
        return ViewHolder(view)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val candidato = items[position]
        holder.tvNombre.text   = candidato.nombre
        holder.tvPosicion.text = candidato.posicion
        holder.tvFecha.text    = "Applied on ${candidato.fechaAplicacion}"
        holder.btnDetalle.setOnClickListener { onVerDetalle(candidato) }
    }

    override fun getItemCount() = items.size
}
 