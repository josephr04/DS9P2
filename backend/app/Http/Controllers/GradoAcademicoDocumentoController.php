<?php
namespace App\Http\Controllers;
use App\Models\GradoAcademicoDocumento;
use Illuminate\Http\Request;

class GradoAcademicoDocumentoController extends Controller {
    public function index() {
        return response()->json(GradoAcademicoDocumento::all());
    }
    public function show($id) {
        $grado = GradoAcademicoDocumento::find($id);
        if (!$grado) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($grado);
    }
    public function store(Request $request) {
        $grado = GradoAcademicoDocumento::create($request->all());
        return response()->json($grado, 201);
    }
    public function update(Request $request, $id) {
        $grado = GradoAcademicoDocumento::find($id);
        if (!$grado) return response()->json(['mensaje' => 'No encontrado'], 404);
        $grado->update($request->all());
        return response()->json($grado);
    }
    public function destroy($id) {
        $grado = GradoAcademicoDocumento::find($id);
        if (!$grado) return response()->json(['mensaje' => 'No encontrado'], 404);
        $grado->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}