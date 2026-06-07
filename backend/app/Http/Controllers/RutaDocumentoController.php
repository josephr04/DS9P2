<?php
namespace App\Http\Controllers;
use App\Models\RutaDocumento;
use Illuminate\Http\Request;

class RutaDocumentoController extends Controller {
    public function index() {
        return response()->json(RutaDocumento::all());
    }
    public function show($id) {
        $ruta = RutaDocumento::find($id);
        if (!$ruta) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($ruta);
    }
    public function store(Request $request) {
        $ruta = RutaDocumento::create($request->all());
        return response()->json($ruta, 201);
    }
    public function update(Request $request, $id) {
        $ruta = RutaDocumento::find($id);
        if (!$ruta) return response()->json(['mensaje' => 'No encontrado'], 404);
        $ruta->update($request->all());
        return response()->json($ruta);
    }
    public function destroy($id) {
        $ruta = RutaDocumento::find($id);
        if (!$ruta) return response()->json(['mensaje' => 'No encontrado'], 404);
        $ruta->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}