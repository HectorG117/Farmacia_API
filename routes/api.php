<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ExpedienteMedicoController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RecetaDetalleController;
use App\Models\HistorialTransaccion;
use App\Models\Producto;
use App\Models\SesionSocial;
use App\Models\UsuarioRol;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  (prefijo /api)
|--------------------------------------------------------------------------
| Autenticación: login de administrador -> token Bearer de Sanctum.
| Permisos por nivel (mismos Gates que el panel web, AppServiceProvider):
|   ver-registros           -> nivel 1, 2 y 3  (index, show)
|   crear-editar-registros  -> nivel 2 y 3     (store, update)
|   eliminar-registros      -> solo nivel 3    (destroy)
*/

// ---------- Públicas ----------
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::get('/productos', fn () => response()->json(Producto::all()));

// ---------- Protegidas con token ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']); // ruta por defecto de Sanctum

    $numericos = [
        'medico'     => '[0-9]+',
        'cita'       => '[0-9]+',
        'expediente' => '[0-9]+',
        'receta'     => '[0-9]+',
        'detalle'    => '[0-9]+',
    ];

    // ===== NIVEL 1, 2 Y 3: Consulta =====
    Route::middleware('can:ver-registros')->group(function () use ($numericos) {
        Route::apiResource('medicos', MedicoController::class)->only(['index', 'show'])->where($numericos);
        Route::apiResource('citas', CitaController::class)->only(['index', 'show'])->where($numericos);
        Route::apiResource('expedientes', ExpedienteMedicoController::class)->only(['index', 'show'])->where($numericos);
        Route::apiResource('recetas', RecetaController::class)->only(['index', 'show'])->where($numericos);
        Route::apiResource('recetas.detalles', RecetaDetalleController::class)->only(['index', 'show'])->where($numericos);

        // Consultas que ya existían
        Route::get('/usuario-roles', fn () => response()->json(
            UsuarioRol::with(['usuario', 'rol'])->get()
        ));
        Route::get('/historial-transacciones', fn () => response()->json(
            HistorialTransaccion::with(['usuario', 'carrito'])->get()
        ));
        Route::get('/sesiones-sociales', fn () => response()->json(
            SesionSocial::with('usuario')->get()
        ));
    });

    // ===== NIVEL 2 Y 3: Crear y editar =====
    Route::middleware('can:crear-editar-registros')->group(function () use ($numericos) {
        Route::apiResource('medicos', MedicoController::class)->only(['store', 'update'])->where($numericos);
        Route::apiResource('citas', CitaController::class)->only(['store', 'update'])->where($numericos);
        Route::apiResource('expedientes', ExpedienteMedicoController::class)->only(['store', 'update'])->where($numericos);
        Route::apiResource('recetas', RecetaController::class)->only(['store', 'update'])->where($numericos);
        Route::apiResource('recetas.detalles', RecetaDetalleController::class)->only(['store', 'update'])->where($numericos);
    });

    // ===== SOLO NIVEL 3: Eliminar =====
    Route::middleware('can:eliminar-registros')->group(function () use ($numericos) {
        Route::apiResource('medicos', MedicoController::class)->only(['destroy'])->where($numericos);
        Route::apiResource('citas', CitaController::class)->only(['destroy'])->where($numericos);
        Route::apiResource('expedientes', ExpedienteMedicoController::class)->only(['destroy'])->where($numericos);
        Route::apiResource('recetas', RecetaController::class)->only(['destroy'])->where($numericos);
        Route::apiResource('recetas.detalles', RecetaDetalleController::class)->only(['destroy'])->where($numericos);
    });
});
