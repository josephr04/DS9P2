package com.example.proyecto2_android.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto2_android.R
import com.example.proyecto2_android.models.Candidato
import de.hdodenhof.circleimageview.CircleImageView

class PostulantesAdapter(
    private val postulantes: List<Candidato>,
    private val onItemClick: (Candidato) -> Unit
) : RecyclerView.Adapter<PostulantesAdapter.ViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_candidato_reciente, parent, false)
        return ViewHolder(view)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val postulante = postulantes[position]
        holder.bind(postulante, onItemClick)
    }

    override fun getItemCount(): Int = postulantes.size

    class ViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val ivAvatar: CircleImageView = itemView.findViewById(R.id.ivCandidatoAvatar)
        private val tvNombre: TextView = itemView.findViewById(R.id.tvCandidatoNombre)
        private val tvPosicion: TextView = itemView.findViewById(R.id.tvCandidatoPosicion)
        private val tvFecha: TextView = itemView.findViewById(R.id.tvCandidatoFecha)
        private val ivMenu: ImageView = itemView.findViewById(R.id.ivMenuCandidato)
        private val btnVerDetalle: LinearLayout = itemView.findViewById(R.id.btnVerDetalle)

        fun bind(candidato: Candidato, onItemClick: (Candidato) -> Unit) {
            tvNombre.text = candidato.nombre
            tvPosicion.text = candidato.posicion
            ivAvatar.setImageResource(candidato.avatarResId ?: R.drawable.ic_person)

            btnVerDetalle.setOnClickListener {
                onItemClick(candidato)
            }

            ivMenu.setOnClickListener {
                // Mostrar menú de opciones (editar, eliminar, etc.)
                android.widget.Toast.makeText(
                    itemView.context,
                    "Opciones para ${candidato.nombre}",
                    android.widget.Toast.LENGTH_SHORT
                ).show()
            }
        }
    }
}