<?php
namespace App\Http\Controllers;
use App\Models\DocumentoPostulante;
use Illuminate\Http\Request;

class DocumentoPostulanteController extends Controller {
    public function index() {
        return response()->json(DocumentoPostulante::all());
    }
    
    public function show($id) {
        $doc = DocumentoPostulante::find($id);
        if (!$doc) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($doc);
    }
    
    public function store(Request $request) {
        $doc = DocumentoPostulante::create($request->all());
        return response()->json($doc, 201);
    }
    
    public function update(Request $request, $id) {
        $doc = DocumentoPostulante::find($id);
        if (!$doc) return response()->json(['mensaje' => 'No encontrado'], 404);
        $doc->update($request->all());
        return response()->json($doc);
    }
    
    public function destroy($id) {
        $doc = DocumentoPostulante::find($id);
        if (!$doc) return response()->json(['mensaje' => 'No encontrado'], 404);
        $doc->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
    
    // Para el endpoint: documentos-postulante/por-usuario/{id}
    public function porUsuario($idUsuario) {
        $docs = DocumentoPostulante::where('idUsuario', $idUsuario)->get();
        return response()->json($docs);
    }
    
    // Para el endpoint: documentos-postulante/por-postulante/{id}
    public function getPorPostulante($idPostulante) {
        $docs = DocumentoPostulante::where('idPostulante', $idPostulante)->get();
        return response()->json($docs);
    }
}