<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Postulante;
use App\Models\DocumentoPostulante;
use App\Models\RutaDocumento;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        try {
            // Total de postulantes
            $totalPostulantes = Postulante::count();
            
            // Total de documentos (con ruta física)
            $totalDocumentos = RutaDocumento::count();
            
            // Postulantes Listos (con perfil completo Y documentos)
            $postulantesListos = $this->calcularPostulantesListos();
            
            // Edad promedio
            $edadPromedio = $this->calcularEdadPromedio();
            
            // Postulantes recientes
            $postulantesRecientes = Postulante::orderBy('idPostulante', 'desc')
                ->limit(5)
                ->get()
                ->map(function($postulante) {
                    $nombreCompleto = trim(
                        ($postulante->nombre ?? '') . ' ' . 
                        ($postulante->nombre2 ?? '') . ' ' . 
                        ($postulante->apellido ?? '') . ' ' . 
                        ($postulante->apellido2 ?? '')
                    );
                    
                    return [
                        'idPostulante' => $postulante->idPostulante,
                        'nombreCompleto' => $nombreCompleto ?: 'Postulante #' . $postulante->idPostulante,
                        'fechaRegistro' => 'ID: ' . $postulante->idPostulante,
                        'perfil' => $this->getRangoNombre($postulante->rangoAcademico),
                        'edad' => $this->calcularEdad($postulante->fechaNacimiento),
                        'tieneDocumentos' => $this->tieneDocumentos($postulante->idPostulante),
                        'perfilCompleto' => $this->tienePerfilCompleto($postulante)
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'totalPostulantes' => $totalPostulantes,
                    'totalDocumentos' => $totalDocumentos,
                    'postulantesListos' => $postulantesListos,
                    'edadPromedio' => $edadPromedio,
                    'postulantesRecientes' => $postulantesRecientes
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verifica si un postulante tiene el perfil completo
     */
    private function tienePerfilCompleto($postulante)
    {
        $camposObligatorios = [
            'nombre',
            'apellido', 
            'prefijo',
            'tomo',
            'asiento',
            'genero',
            'fechaNacimiento',
            'celular',
            'correoPostulante'
        ];
        
        foreach ($camposObligatorios as $campo) {
            if (empty($postulante->$campo)) {
                return false;
            }
        }
        
        // Verificar que tenga documentos (corregido)
        return $this->tieneDocumentos($postulante->idPostulante);
    }
    
    /**
     * Verifica si un postulante tiene documentos subidos
     * CORREGIDO: Busca en documento_postulante por idPostulante
     */
    private function tieneDocumentos($idPostulante)
    {
        // Primero buscar si tiene documentos en la tabla documento_postulante
        $documentos = DocumentoPostulante::where('idPostulante', $idPostulante)->get();
        
        if ($documentos->isEmpty()) {
            return false;
        }
        
        // Verificar que al menos un documento tenga ruta física
        foreach ($documentos as $documento) {
            $tieneRuta = RutaDocumento::where('idDocumentoPostulante', $documento->idDocumentoPostulante)->exists();
            if ($tieneRuta) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Calcula los postulantes listos
     */
    private function calcularPostulantesListos()
    {
        try {
            $postulantes = Postulante::all();
            $contador = 0;
            
            foreach ($postulantes as $postulante) {
                if ($this->tienePerfilCompleto($postulante)) {
                    $contador++;
                }
            }
            
            return $contador;
            
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Calcula la edad promedio
     */
    private function calcularEdadPromedio()
    {
        try {
            $postulantes = Postulante::whereNotNull('fechaNacimiento')->get();
            
            if ($postulantes->isEmpty()) {
                return 0;
            }
            
            $totalEdades = 0;
            $contador = 0;
            
            foreach ($postulantes as $postulante) {
                $edad = $this->calcularEdad($postulante->fechaNacimiento);
                if ($edad > 0) {
                    $totalEdades += $edad;
                    $contador++;
                }
            }
            
            return $contador > 0 ? round($totalEdades / $contador) : 0;
            
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Calcula la edad
     */
    private function calcularEdad($fechaNacimiento)
    {
        if (empty($fechaNacimiento)) {
            return 0;
        }
        
        try {
            $fecha = new \DateTime($fechaNacimiento);
            $hoy = new \DateTime();
            return $hoy->diff($fecha)->y;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Obtiene el nombre del rango académico
     */
    private function getRangoNombre($idRango)
    {
        $rangos = [
            1 => 'DIPLOMADO',
            2 => 'TECNICO',
            3 => 'LICENCIATURA',
            4 => 'POSTGRADO',
            5 => 'MAESTRIA',
            6 => 'DOCTORADO'
        ];
        return $rangos[$idRango] ?? 'Postulante';
    }
}