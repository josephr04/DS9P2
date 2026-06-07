<?php
namespace App\Http\Controllers;
use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller {
    public function index() {
        return response()->json(Usuario::all());
    }
    public function show($id) {
        $usuario = Usuario::find($id);
        if (!$usuario) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($usuario);
    }
    public function store(Request $request) {
        $usuario = Usuario::create($request->all());
        return response()->json($usuario, 201);
    }
    public function update(Request $request, $id) {
        $usuario = Usuario::find($id);
        if (!$usuario) return response()->json(['mensaje' => 'No encontrado'], 404);
        $usuario->update($request->all());
        return response()->json($usuario);
    }
    public function destroy($id) {
        $usuario = Usuario::find($id);
        if (!$usuario) return response()->json(['mensaje' => 'No encontrado'], 404);
        $usuario->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
}