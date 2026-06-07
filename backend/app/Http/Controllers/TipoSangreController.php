<?php
namespace App\Http\Controllers;
use App\Models\TipoSangre;
use Illuminate\Http\Request;

class TipoSangreController extends Controller {
    public function index() {
        return response()->json(TipoSangre::all());
    }
    public function show($id) {
        $tipoSangre = TipoSangre::find($id);
        if (!$tipoSangre) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($tipoSangre);
    }
    public function store(Request $request) {
        $tipoSangre = TipoSangre::create($request->all());
        return response()->json($tipoSangre, 201);
    }
    public function update(Request $request, $id) {
        $tipoSangre = TipoSangre::find($id);
        if (!$tipoSangre) return response()->json(['mensaje' => 'No encontrado'], 404);
        $tipoSangre->update($request->all());
        return response()->json($tipoSangre);
    }
    public function destroy($id) {
        $tipoSangre = TipoSangre::find($id);
        if (!$tipoSangre) return response()->json(['mensaje' => 'No encontrado'], 404);
        $tipoSangre->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}