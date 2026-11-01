<?php

use App\Http\Controllers\CitaController;
use App\Http\Controllers\ExpedienteMedicoController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RecetaDetalleController;
use App\Http\Controllers\Roles;
use App\Models\HistorialTransaccion;
use App\Models\Producto;
use App\Models\SesionSocial;
use App\Models\UsuarioRol;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  (prefijo /api)
|--------------------------------------------------------------------------
| Cada rol tiene su propio controlador (app/Http/Controllers/Roles) y su login,
| que entrega un token Bearer de Sanctum válido solo para ese rol:
|
|   ADMIN     POST /api/admin/login     (nombre_usuario + password)
|   MÉDICO    POST /api/medico/login    (email + password)
|   PACIENTE  POST /api/paciente/login  (email + password)  ·  POST /api/paciente/registro
|
| El middleware "rol:<rol>" (App\Http\Middleware\VerificarRol) impide que el token
| de un rol use las rutas de otro.
|
| Permisos del administrador por nivel (mismos Gates que el panel web, AppServiceProvider):
|   ver-registros           -> nivel 1, 2 y 3  (index, show)
|   crear-editar-registros  -> nivel 2 y 3     (store, update)
|   eliminar-registros      -> solo nivel 3    (destroy)
*/

// ---------- Públicas ----------
// Cada login tiene su propio límite de 5 intentos por minuto (tercer parámetro de throttle)
Route::get('/productos', fn () => response()->json(Producto::all()));

Route::post('/admin/login', [Roles\AdminController::class, 'login'])->middleware('throttle:5,1,login-admin');
Route::post('/medico/login', [Roles\MedicoController::class, 'login'])->middleware('throttle:5,1,login-medico');
Route::post('/paciente/login', [Roles\PacienteController::class, 'login'])->middleware('throttle:5,1,login-paciente');
Route::post('/paciente/registro', [Roles\PacienteController::class, 'registro'])->middleware('throttle:5,1,registro-paciente');

// Ruta anterior del login de administrador (se conserva para la colección de Postman)
Route::post('/login', [Roles\AdminController::class, 'login'])->middleware('throttle:5,1,login-admin');

/*
|--------------------------------------------------------------------------
| Rol MÉDICO  (/api/medico/...)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'rol:medico'])->prefix('medico')->group(function () {
    Route::post('/logout', [Roles\MedicoController::class, 'logout']);
    Route::get('/perfil', [Roles\MedicoController::class, 'perfil']);

    Route::get('/citas', [Roles\MedicoController::class, 'citas']);
    Route::get('/citas/{id}', [Roles\MedicoController::class, 'cita'])->whereNumber('id');
    Route::put('/citas/{id}', [Roles\MedicoController::class, 'actualizarCita'])->whereNumber('id');
    Route::get('/citas/{id}/expediente', [Roles\MedicoController::class, 'expediente'])->whereNumber('id');
    Route::put('/citas/{id}/expediente', [Roles\MedicoController::class, 'guardarExpediente'])->whereNumber('id');

    Route::get('/pacientes', [Roles\MedicoController::class, 'pacientes']);

    // Sin DELETE: para anular una receta se envía "estado": "Cancelada"
    Route::get('/recetas', [Roles\MedicoController::class, 'recetas']);
    Route::post('/recetas', [Roles\MedicoController::class, 'crearReceta']);
    Route::get('/recetas/{id}', [Roles\MedicoController::class, 'receta'])->whereNumber('id');
    Route::put('/recetas/{id}', [Roles\MedicoController::class, 'actualizarReceta'])->whereNumber('id');
});

/*
|--------------------------------------------------------------------------
| Rol PACIENTE  (/api/paciente/...)  — solo su propia información
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'rol:paciente'])->prefix('paciente')->group(function () {
    Route::post('/logout', [Roles\PacienteController::class, 'logout']);
    Route::get('/perfil', [Roles\PacienteController::class, 'perfil']);
    Route::get('/resumen', [Roles\PacienteController::class, 'resumen']);

    Route::get('/citas', [Roles\PacienteController::class, 'citas']);
    Route::get('/citas/{id}', [Roles\PacienteController::class, 'cita'])->whereNumber('id');

    Route::get('/expedientes', [Roles\PacienteController::class, 'expedientes']);
    Route::get('/expedientes/{id}', [Roles\PacienteController::class, 'expediente'])->whereNumber('id');

    // Sin DELETE: el paciente solo elige qué productos comprar y cuántos
    Route::get('/recetas', [Roles\PacienteController::class, 'recetas']);
    Route::get('/recetas/{id}', [Roles\PacienteController::class, 'receta'])->whereNumber('id');
    Route::put('/recetas/{id}/compra', [Roles\PacienteController::class, 'actualizarCompra'])->whereNumber('id');
});

/*
|--------------------------------------------------------------------------
| Rol ADMIN  (/api/admin/... y los recursos /api/medicos, /api/citas, ...)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'rol:admin'])->group(function () {
    Route::post('/admin/logout', [Roles\AdminController::class, 'logout']);
    Route::get('/admin/perfil', [Roles\AdminController::class, 'perfil']);
    Route::get('/admin/dashboard', [Roles\AdminController::class, 'dashboard'])->middleware('can:ver-registros');

    // Rutas anteriores (se conservan para la colección de Postman)
    Route::post('/logout', [Roles\AdminController::class, 'logout']);
    Route::get('/me', [Roles\AdminController::class, 'perfil']);
    Route::get('/user', [Roles\AdminController::class, 'perfil']); // ruta por defecto de Sanctum

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
