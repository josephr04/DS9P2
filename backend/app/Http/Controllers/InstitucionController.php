<?php
namespace App\Http\Controllers;
use App\Models\Institucion;
use Illuminate\Http\Request;

class InstitucionController extends Controller {
    public function index() {
        return response()->json(Institucion::all());
    }
    public function show($id) {
        $institucion = Institucion::find($id);
        if (!$institucion) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($institucion);
    }
    public function store(Request $request) {
        $institucion = Institucion::create($request->all());
        return response()->json($institucion, 201);
    }
    public function update(Request $request, $id) {
        $institucion = Institucion::find($id);
        if (!$institucion) return response()->json(['mensaje' => 'No encontrado'], 404);
        $institucion->update($request->all());
        return response()->json($institucion);
    }
    public function destroy($id) {
        $institucion = Institucion::find($id);
        if (!$institucion) return response()->json(['mensaje' => 'No encontrado'], 404);
        $institucion->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}