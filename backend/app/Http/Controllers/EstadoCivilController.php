<?php
namespace App\Http\Controllers;
use App\Models\EstadoCivil;
use Illuminate\Http\Request;

class EstadoCivilController extends Controller {
    public function index() {
        return response()->json(EstadoCivil::all());
    }
    public function show($id) {
        $estadoCivil = EstadoCivil::find($id);
        if (!$estadoCivil) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($estadoCivil);
    }
    public function store(Request $request) {
        $estadoCivil = EstadoCivil::create($request->all());
        return response()->json($estadoCivil, 201);
    }
    public function update(Request $request, $id) {
        $estadoCivil = EstadoCivil::find($id);
        if (!$estadoCivil) return response()->json(['mensaje' => 'No encontrado'], 404);
        $estadoCivil->update($request->all());
        return response()->json($estadoCivil);
    }
    public function destroy($id) {
        $estadoCivil = EstadoCivil::find($id);
        if (!$estadoCivil) return response()->json(['mensaje' => 'No encontrado'], 404);
        $estadoCivil->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}