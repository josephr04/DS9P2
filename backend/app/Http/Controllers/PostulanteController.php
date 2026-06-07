<?php
namespace App\Http\Controllers;
use App\Models\Postulante;
use Illuminate\Http\Request;

class PostulanteController extends Controller {
    public function index() {
        return response()->json(Postulante::all());
    }
    public function show($id) {
        $postulante = Postulante::find($id);
        if (!$postulante) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($postulante);
    }
    public function store(Request $request) {
        $postulante = Postulante::create($request->all());
        return response()->json($postulante, 201);
    }
    public function update(Request $request, $id) {
        $postulante = Postulante::find($id);
        if (!$postulante) return response()->json(['mensaje' => 'No encontrado'], 404);
        $postulante->update($request->all());
        return response()->json($postulante);
    }
    public function destroy($id) {
        $postulante = Postulante::find($id);
        if (!$postulante) return response()->json(['mensaje' => 'No encontrado'], 404);
        $postulante->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}