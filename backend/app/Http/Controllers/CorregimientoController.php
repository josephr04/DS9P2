<?php
namespace App\Http\Controllers;
use App\Models\Corregimiento;
use Illuminate\Http\Request;

class CorregimientoController extends Controller {
    public function index() {
        return response()->json(Corregimiento::all());
    }
    public function show($id) {
        $corregimiento = Corregimiento::find($id);
        if (!$corregimiento) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($corregimiento);
    }
    public function store(Request $request) {
        $corregimiento = Corregimiento::create($request->all());
        return response()->json($corregimiento, 201);
    }
    public function update(Request $request, $id) {
        $corregimiento = Corregimiento::find($id);
        if (!$corregimiento) return response()->json(['mensaje' => 'No encontrado'], 404);
        $corregimiento->update($request->all());
        return response()->json($corregimiento);
    }
    public function destroy($id) {
        $corregimiento = Corregimiento::find($id);
        if (!$corregimiento) return response()->json(['mensaje' => 'No encontrado'], 404);
        $corregimiento->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}