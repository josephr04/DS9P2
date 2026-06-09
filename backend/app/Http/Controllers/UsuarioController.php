<?php
namespace App\Http\Controllers;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        // Asegurar que la contraseña se guarde con SHA-256
        $data = $request->all();
        if (isset($data['contrasen'])) {
            $data['contrasen'] = hash('sha256', $data['contrasen']);
        }
        $usuario = Usuario::create($data);
        return response()->json($usuario, 201);
    }
    
    public function update(Request $request, $id) {
        $usuario = Usuario::find($id);
        if (!$usuario) return response()->json(['mensaje' => 'No encontrado'], 404);
        
        $data = $request->all();
        // Si se actualiza la contraseña, aplicamos SHA-256
        if (isset($data['contrasen'])) {
            $data['contrasen'] = hash('sha256', $data['contrasen']);
        }
        
        $usuario->update($data);
        return response()->json($usuario);
    }
    
    public function destroy($id) {
        $usuario = Usuario::find($id);
        if (!$usuario) return response()->json(['mensaje' => 'No encontrado'], 404);
        $usuario->delete();
        return response()->json(['mensaje' => 'Eliminado correctamente']);
    }
    
    public function cambiarContrasena(Request $request, $id) {
        if (empty($request->contrasena_actual)) {
            return response()->json(['mensaje' => 'La contraseña actual es obligatoria'], 401);
        }
        
        if (empty($request->nueva_contrasena)) {
            return response()->json(['mensaje' => 'La nueva contraseña es obligatoria'], 401);
        }
        
        if (strlen($request->nueva_contrasena) < 6) {
            return response()->json(['mensaje' => 'La nueva contraseña debe tener al menos 6 caracteres'], 401);
        }

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        // Verificar contraseña actual con SHA-256
        $hashActual = hash('sha256', $request->contrasena_actual);
        
        if ($hashActual !== $usuario->contrasen) {
            return response()->json(['mensaje' => 'La contraseña actual es incorrecta'], 401);
        }

        // Guardar nueva contraseña con SHA-256
        $usuario->contrasen = hash('sha256', $request->nueva_contrasena);
        $usuario->save();

        return response()->json(['mensaje' => 'Contraseña actualizada correctamente']);
    }

    public function cambiarUsuario(Request $request, $id) {
        // Validaciones
        if (empty($request->nuevo_usuario)) {
            return response()->json(['mensaje' => 'El nombre de usuario es obligatorio'], 401);
        }
        
        if (strlen($request->nuevo_usuario) < 5) {
            return response()->json(['mensaje' => 'El nombre de usuario debe tener al menos 5 caracteres'], 401);
        }
        
        if (strpos($request->nuevo_usuario, ' ') !== false) {
            return response()->json(['mensaje' => 'El nombre de usuario no puede contener espacios'], 401);
        }
        
        // Verificar si el usuario ya existe
        $usuarioExistente = Usuario::where('nombreUsuario', $request->nuevo_usuario)
            ->where('idUsuario', '!=', $id)
            ->first();
        
        if ($usuarioExistente) {
            return response()->json(['mensaje' => 'El nombre de usuario ya está en uso'], 409);
        }
        
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }
        
        // Guardar el nuevo nombre de usuario
        $usuario->nombreUsuario = $request->nuevo_usuario;
        $usuario->save();
        
        return response()->json([
            'mensaje' => 'Nombre de usuario actualizado correctamente',
            'nuevo_usuario' => $usuario->nombreUsuario
        ]);
    }

    public function cambiarCorreo(Request $request, $id) {
        // Validaciones
        if (empty($request->nuevo_correo)) {
            return response()->json(['mensaje' => 'El correo electrónico es obligatorio'], 401);
        }
        
        if (!filter_var($request->nuevo_correo, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['mensaje' => 'Ingresa un correo electrónico válido'], 401);
        }
        
        if (empty($request->confirmar_correo)) {
            return response()->json(['mensaje' => 'Debes confirmar el correo electrónico'], 401);
        }
        
        if ($request->nuevo_correo !== $request->confirmar_correo) {
            return response()->json(['mensaje' => 'Los correos electrónicos no coinciden'], 401);
        }
        
        // Verificar si el correo ya está en uso por otro usuario
        $usuarioExistente = Usuario::where('correo', $request->nuevo_correo)
            ->where('idUsuario', '!=', $id)
            ->first();
        
        if ($usuarioExistente) {
            return response()->json(['mensaje' => 'El correo electrónico ya está en uso'], 409);
        }
        
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }
        
        // Verificar que el nuevo correo sea diferente al actual
        if ($usuario->correo === $request->nuevo_correo) {
            return response()->json(['mensaje' => 'El nuevo correo debe ser diferente al actual'], 401);
        }
        
        // Guardar el nuevo correo
        $usuario->correo = $request->nuevo_correo;
        $usuario->save();
        
        return response()->json([
            'mensaje' => 'Correo electrónico actualizado correctamente',
            'nuevo_correo' => $usuario->correo
        ]);
    }

    public function login(Request $request) {
        // Validaciones
        $request->validate([
            'nombre_usuario' => 'required|string',
            'contrasena' => 'required|string'
        ]);
        
        // Buscar usuario por nombre de usuario
        $usuario = Usuario::where('nombreUsuario', $request->nombre_usuario)->first();
        
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Credenciales incorrectas'
            ], 401);
        }
        
        // Verificar contraseña con SHA-256
        $hashIngresado = hash('sha256', $request->contrasena);
        
        if ($hashIngresado !== $usuario->contrasen) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Credenciales incorrectas'
            ], 401);
        }
        
        // Opcional: Generar token (recomendado)
        // $token = bin2hex(random_bytes(32));
        
        // Devolver datos del usuario
        return response()->json([
            'success' => true,
            'mensaje' => 'Login exitoso',
            'usuario' => [
                'idUsuario' => $usuario->idUsuario,
                'nombreUsuario' => $usuario->nombreUsuario,
                'correo' => $usuario->correo,
                'rolUsuario' => $usuario->rolUsuario
            ]
        ]);
    }

    public function resetContrasena(Request $request)
    {
        if (empty($request->correo)) {
            return response()->json(['mensaje' => 'El correo es obligatorio'], 400);
        }
    
        if (empty($request->nueva_contrasena)) {
            return response()->json(['mensaje' => 'La nueva contraseña es obligatoria'], 400);
        }
    
        if (strlen($request->nueva_contrasena) < 6) {
            return response()->json(['mensaje' => 'La contraseña debe tener al menos 6 caracteres'], 400);
        }
    
        $usuario = Usuario::where('correo', $request->correo)->first();
    
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No existe una cuenta con ese correo'
            ], 404);
        }
    
        // Mismo hash que usa el resto del sistema
        $usuario->contrasen = hash('sha256', $request->nueva_contrasena);
        $usuario->save();
    
        return response()->json([
            'success' => true,
            'mensaje' => 'Contraseña actualizada correctamente'
        ], 200);
    }
}