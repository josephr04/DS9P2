<?php
namespace App\Http\Controllers;
use App\Models\Distrito;
use Illuminate\Http\Request;

class DistritoController extends Controller {
    public function index() {
        return response()->json(Distrito::all());
    }
    public function show($id) {
        $distrito = Distrito::find($id);
        if (!$distrito) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($distrito);
    }
    public function store(Request $request) {
        $distrito = Distrito::create($request->all());
        return response()->json($distrito, 201);
    }
    public function update(Request $request, $id) {
        $distrito = Distrito::find($id);
        if (!$distrito) return response()->json(['mensaje' => 'No encontrado'], 404);
        $distrito->update($request->all());
        return response()->json($distrito);
    }
    public function destroy($id) {
        $distrito = Distrito::find($id);
        if (!$distrito) return response()->json(['mensaje' => 'No encontrado'], 404);
        $distrito->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}