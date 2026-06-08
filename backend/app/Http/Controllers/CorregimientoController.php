<?php
namespace App\Http\Controllers;
use App\Models\Corregimiento;
use Illuminate\Http\Request;

class CorregimientoController extends Controller {
    public function index() {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $corregimientos = Corregimiento::select(
            'codigo_corregimiento', 
            'codigo_distrito', 
            'nombre_corregimiento',
            'codigo_provincia'
        )->get();

        return response()->json($corregimientos);
    }
    // NUEVO: filtrar por distrito
    public function porDistrito($codigo) {
        $corregimientos = Corregimiento::select(
            'codigo_corregimiento', 
            'codigo_distrito', 
            'nombre_corregimiento',
            'codigo_provincia'
        )->where('codigo_distrito', $codigo)->get();
        
        return response()->json($corregimientos);
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