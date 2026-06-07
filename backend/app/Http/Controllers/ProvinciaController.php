<?php
namespace App\Http\Controllers;
use App\Models\Provincia;
use Illuminate\Http\Request;

class ProvinciaController extends Controller {
    public function index() {
        return response()->json(Provincia::all());
    }
    public function show($id) {
        $provincia = Provincia::find($id);
        if (!$provincia) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($provincia);
    }
    public function store(Request $request) {
        $provincia = Provincia::create($request->all());
        return response()->json($provincia, 201);
    }
    public function update(Request $request, $id) {
        $provincia = Provincia::find($id);
        if (!$provincia) return response()->json(['mensaje' => 'No encontrado'], 404);
        $provincia->update($request->all());
        return response()->json($provincia);
    }
    public function destroy($id) {
        $provincia = Provincia::find($id);
        if (!$provincia) return response()->json(['mensaje' => 'No encontrado'], 404);
        $provincia->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}