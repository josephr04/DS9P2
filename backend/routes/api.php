<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\DistritoController;
use App\Http\Controllers\CorregimientoController;
use App\Http\Controllers\EstadoCivilController;
use App\Http\Controllers\RangoAcademicoController;
use App\Http\Controllers\TipoSangreController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\DocumentoPostulanteController;
use App\Http\Controllers\GradoAcademicoDocumentoController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\RutaDocumentoController;
use App\Http\Controllers\DashboardController;

Route::apiResource('provincias', ProvinciaController::class);
Route::apiResource('distritos', DistritoController::class);
Route::apiResource('corregimientos', CorregimientoController::class);
Route::apiResource('estados-civiles', EstadoCivilController::class);
Route::apiResource('rangos-academicos', RangoAcademicoController::class);
Route::apiResource('tipos-sangre', TipoSangreController::class);

// Login y endpoints de usuario
Route::post('login', [UsuarioController::class, 'login']);
Route::put('usuarios/{id}/cambiar-correo', [UsuarioController::class, 'cambiarCorreo']);
Route::post('usuarios/reset-contrasena', [UsuarioController::class, 'resetContrasena']);
Route::put('usuarios/{id}/cambiar-usuario', [UsuarioController::class, 'cambiarUsuario']);
Route::post('usuarios/{id}/cambiar-contrasena', [UsuarioController::class, 'cambiarContrasena']);
Route::apiResource('usuarios', UsuarioController::class);

// Postulantes
Route::get('/postulantes/usuario/{idUsuario}', [PostulanteController::class, 'showPorUsuario']);
Route::apiResource('postulantes', PostulanteController::class);
Route::get('postulantes/{id}', [PostulanteController::class, 'show']);

// Documentos
Route::get('documentos-postulante/por-usuario/{id}', [DocumentoPostulanteController::class, 'porUsuario']);
Route::get('documentos-postulante/por-postulante/{id}', [DocumentoPostulanteController::class, 'getPorPostulante']);
Route::apiResource('documentos-postulante', DocumentoPostulanteController::class);

// Otros catálogos
Route::apiResource('grados-academicos', GradoAcademicoDocumentoController::class);
Route::apiResource('instituciones', InstitucionController::class);
Route::apiResource('rutas-documento', RutaDocumentoController::class);

// Corregimientos por distrito
Route::get('/corregimientos/codigo/{codigo}', [CorregimientoController::class, 'porCodigo']);
Route::get('corregimientos/por-distrito/{codigo}', [CorregimientoController::class, 'porDistrito']);

// Dashboard
Route::get('dashboard/stats', [DashboardController::class, 'getStats']);