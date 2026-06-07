<?php
namespace App\Http\Controllers;
use App\Models\RangoAcademico;
use Illuminate\Http\Request;

class RangoAcademicoController extends Controller {
    public function index() {
        return response()->json(RangoAcademico::all());
    }
    public function show($id) {
        $rango = RangoAcademico::find($id);
        if (!$rango) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($rango);
    }
    public function store(Request $request) {
        $rango = RangoAcademico::create($request->all());
        return response()->json($rango, 201);
    }
    public function update(Request $request, $id) {
        $rango = RangoAcademico::find($id);
        if (!$rango) return response()->json(['mensaje' => 'No encontrado'], 404);
        $rango->update($request->all());
        return response()->json($rango);
    }
    public function destroy($id) {
        $rango = RangoAcademico::find($id);
        if (!$rango) return response()->json(['mensaje' => 'No encontrado'], 404);
        $rango->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}