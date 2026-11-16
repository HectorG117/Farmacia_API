<?php

use App\Http\Controllers\AdminRecordController;
use App\Http\Controllers\Api\V1;
use App\Http\Controllers\Api\V1\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Api\V1\Client\CatalogoController as ClientCatalogoController;
use App\Http\Controllers\Api\V1\Client\PedidoController as ClientPedidoController;
use App\Http\Controllers\Api\V1\Client\ProfileController as ClientProfileController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\ExpedienteMedicoController;
use App\Http\Controllers\HistorialTransaccionController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RecetaDetalleController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\Roles;
use App\Http\Controllers\ServicioMedicoController;
use App\Http\Controllers\SesionSocialController;
use App\Http\Controllers\UsuarioRolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  (prefijo /api)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| API v1 — Actividad 4.2 y Actividad 4.4 (prefijo /api/v1)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('api.v1')->group(function () {

    // =========================================================================
    // MÓDULO DEL CLIENTE (Actividad 4.4: Client API & Data Ownership)
    // Prefijo resultante: /api/v1/client/
    // =========================================================================
    Route::prefix('client')->group(function () {

        // --- Endpoints Públicos ---
        Route::post('/register', [ClientAuthController::class, 'register']);
        Route::post('/login', [ClientAuthController::class, 'login']);
        Route::get('/catalogo', [ClientCatalogoController::class, 'index']);
        Route::get('/catalogo/{id}', [ClientCatalogoController::class, 'show']);

        // --- Endpoints Privados / Protección Sanctum ---
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [ClientAuthController::class, 'logout']);

            // Gestión de Perfil Propio
            Route::get('/perfil', [ClientProfileController::class, 'show']);
            Route::match(['put', 'patch'], '/perfil', [ClientProfileController::class, 'update']);

            // Gestión de Registros / Pedidos Propios (Data Ownership)
            Route::get('/mis-registros', [ClientPedidoController::class, 'index']);
            Route::post('/mis-registros', [ClientPedidoController::class, 'store']);
            Route::get('/mis-registros/{pedido}', [ClientPedidoController::class, 'show']);
            Route::match(['put', 'patch'], '/mis-registros/{pedido}', [ClientPedidoController::class, 'update']);
            Route::delete('/mis-registros/{pedido}', [ClientPedidoController::class, 'destroy']);
        });
    });

    // =========================================================================
    // Endpoints API v1 Existentes
    // =========================================================================
    Route::post('/admin/login', [V1\AdminController::class, 'login'])
        ->middleware('throttle:5,1,login-admin-v1');

    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'rol:admin'])
        ->group(function () {
            Route::post('/logout', [V1\AdminController::class, 'logout']);

            Route::middleware('can:ver-registros')->group(function () {
                Route::get('/recursos', [V1\AdminController::class, 'indexResources']);
                Route::get('/recursos/{id}', [V1\AdminController::class, 'showResource'])->whereNumber('id');
                Route::get('/usuarios', [V1\AdminController::class, 'indexUsers']);
            });

            Route::middleware('can:crear-editar-registros')->group(function () {
                Route::post('/recursos', [V1\AdminController::class, 'storeResource']);
                Route::put('/recursos/{id}', [V1\AdminController::class, 'updateResource'])->whereNumber('id');
                Route::patch('/recursos/{id}', [V1\AdminController::class, 'updateResource'])->whereNumber('id');
            });

            Route::middleware('can:eliminar-registros')->group(function () {
                Route::delete('/recursos/{id}', [V1\AdminController::class, 'destroyResource'])->whereNumber('id');
                Route::patch('/usuarios/{id}/estado', [V1\AdminController::class, 'updateUserStatus'])->whereNumber('id');
            });
        });

    // ----- Productos (recurso con imagen) -----
    Route::controller(V1\ProductoController::class)->group(function () {
        Route::get('/productos', 'index');
        Route::post('/productos', 'store');
        Route::get('/productos/{id}', 'show');
        Route::put('/productos/{id}', 'update');
        Route::patch('/productos/{id}', 'patch');
        Route::delete('/productos/{id}', 'destroy');

        Route::post('/productos/{id}/imagen', 'subirImagen');
        Route::delete('/productos/{id}/imagen', 'eliminarImagen');
    });

    // ----- Especialidades médicas -----
    Route::controller(V1\EspecialidadController::class)->group(function () {
        Route::get('/especialidades', 'index');
        Route::post('/especialidades', 'store');
        Route::get('/especialidades/{id}', 'show');
        Route::get('/especialidades/{id}/medicos', 'medicos');
        Route::put('/especialidades/{id}', 'update');
        Route::patch('/especialidades/{id}', 'patch');
        Route::delete('/especialidades/{id}', 'destroy');
    });

    // ----- Servicios médicos -----
    Route::controller(V1\ServicioMedicoController::class)->group(function () {
        Route::get('/servicios-medicos', 'index');
        Route::post('/servicios-medicos', 'store');
        Route::get('/servicios-medicos/{id}', 'show');
        Route::put('/servicios-medicos/{id}', 'update');
        Route::patch('/servicios-medicos/{id}', 'patch');
        Route::delete('/servicios-medicos/{id}', 'destroy');
    });
});

// ---------- Rutas Públicas de Legacy/Panel ----------
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show'])->whereNumber('id');

Route::post('/admin/login', [Roles\AdminController::class, 'login'])->middleware('throttle:5,1,login-admin');
Route::post('/medico/login', [Roles\MedicoController::class, 'login'])->middleware('throttle:5,1,login-medico');
Route::post('/paciente/login', [Roles\PacienteController::class, 'login'])->middleware('throttle:5,1,login-paciente');
Route::post('/paciente/registro', [Roles\PacienteController::class, 'registro'])->middleware('throttle:5,1,registro-paciente');

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

    Route::get('/recetas', [Roles\MedicoController::class, 'recetas']);
    Route::post('/recetas', [Roles\MedicoController::class, 'crearReceta']);
    Route::get('/recetas/{id}', [Roles\MedicoController::class, 'receta'])->whereNumber('id');
    Route::put('/recetas/{id}', [Roles\MedicoController::class, 'actualizarReceta'])->whereNumber('id');
});

/*
|--------------------------------------------------------------------------
| Rol PACIENTE  (/api/paciente/...)
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

    Route::get('/recetas', [Roles\PacienteController::class, 'recetas']);
    Route::get('/recetas/{id}', [Roles\PacienteController::class, 'receta'])->whereNumber('id');
    Route::put('/recetas/{id}/compra', [Roles\PacienteController::class, 'actualizarCompra'])->whereNumber('id');
});

/*
|--------------------------------------------------------------------------
| Rol ADMIN  (/api/admin/... y recursos varios)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'rol:admin'])->group(function () {
    Route::post('/admin/logout', [Roles\AdminController::class, 'logout']);
    Route::get('/admin/perfil', [Roles\AdminController::class, 'perfil']);
    Route::get('/admin/dashboard', [Roles\AdminController::class, 'dashboard'])->middleware('can:ver-registros');

    Route::post('/logout', [Roles\AdminController::class, 'logout']);
    Route::get('/me', [Roles\AdminController::class, 'perfil']);
    Route::get('/user', [Roles\AdminController::class, 'perfil']);

    $numericos = [
        'medico'     => '[0-9]+',
        'cita'       => '[0-9]+',
        'expediente' => '[0-9]+',
        'receta'     => '[0-9]+',
        'detalle'    => '[0-9]+',
    ];

    $modulos = ['usuarios', 'carritos', 'carrito-detalles', 'users'];

    // ===== NIVEL 1, 2 Y 3: Consulta =====
    Route::middleware('can:ver-registros')->group(function () use ($numericos, $modulos) {
        Route::apiResource('medicos', MedicoController::class)->only(['index', 'show'])->where($numericos);
        Route::apiResource('citas', CitaController::class)->only(['index', 'show'])->where($numericos);
        Route::apiResource('expedientes', ExpedienteMedicoController::class)->only(['index', 'show'])->where($numericos);
        Route::apiResource('recetas', RecetaController::class)->only(['index', 'show'])->where($numericos);
        Route::apiResource('recetas.detalles', RecetaDetalleController::class)->only(['index', 'show'])->where($numericos);

        Route::get('/especialidades', [EspecialidadController::class, 'index']);
        Route::get('/especialidades/{id}', [EspecialidadController::class, 'show'])->whereNumber('id');
        Route::get('/servicios-medicos', [ServicioMedicoController::class, 'index']);
        Route::get('/servicios-medicos/{id}', [ServicioMedicoController::class, 'show'])->whereNumber('id');
        Route::get('/usuario-roles', [UsuarioRolController::class, 'index']);
        Route::get('/sesiones-sociales', [SesionSocialController::class, 'index']);
        Route::get('/sesiones-sociales/{id}', [SesionSocialController::class, 'show'])->whereNumber('id');
        Route::get('/historial-transacciones', [HistorialTransaccionController::class, 'index']);
        Route::get('/historial-transacciones/{id}', [HistorialTransaccionController::class, 'show'])->whereNumber('id');

        foreach ($modulos as $modulo) {
            Route::get("/{$modulo}", [AdminRecordController::class, 'index'])->name("admin.{$modulo}.index");
            Route::get("/{$modulo}/{id}", [AdminRecordController::class, 'show'])->whereNumber('id')->name("admin.{$modulo}.show");
        }
    });

    // ===== NIVEL 2 Y 3: Crear y editar =====
    Route::middleware('can:crear-editar-registros')->group(function () use ($numericos, $modulos) {
        Route::apiResource('medicos', MedicoController::class)->only(['store', 'update'])->where($numericos);
        Route::apiResource('citas', CitaController::class)->only(['store', 'update'])->where($numericos);
        Route::apiResource('expedientes', ExpedienteMedicoController::class)->only(['store', 'update'])->where($numericos);
        Route::apiResource('recetas', RecetaController::class)->only(['store', 'update'])->where($numericos);
        Route::apiResource('recetas.detalles', RecetaDetalleController::class)->only(['store', 'update'])->where($numericos);

        Route::post('/productos', [ProductoController::class, 'store']);
        Route::put('/productos/{id}', [ProductoController::class, 'update'])->whereNumber('id');

        Route::post('/especialidades', [EspecialidadController::class, 'store']);
        Route::put('/especialidades/{id}', [EspecialidadController::class, 'update'])->whereNumber('id');
        Route::post('/servicios-medicos', [ServicioMedicoController::class, 'store']);
        Route::put('/servicios-medicos/{id}', [ServicioMedicoController::class, 'update'])->whereNumber('id');
        Route::post('/usuario-roles', [UsuarioRolController::class, 'store']);
        Route::post('/sesiones-sociales', [SesionSocialController::class, 'store']);
        Route::post('/historial-transacciones', [HistorialTransaccionController::class, 'store']);
        Route::put('/historial-transacciones/{id}', [HistorialTransaccionController::class, 'update'])->whereNumber('id');

        foreach ($modulos as $modulo) {
            Route::post("/{$modulo}", [AdminRecordController::class, 'store'])->name("admin.{$modulo}.store");
            Route::put("/{$modulo}/{id}", [AdminRecordController::class, 'update'])->whereNumber('id')->name("admin.{$modulo}.update");
        }
    });

    // ===== SOLO NIVEL 3: Eliminar =====
    Route::middleware('can:eliminar-registros')->group(function () use ($numericos, $modulos) {
        Route::apiResource('medicos', MedicoController::class)->only(['destroy'])->where($numericos);
        Route::apiResource('citas', CitaController::class)->only(['destroy'])->where($numericos);
        Route::apiResource('expedientes', ExpedienteMedicoController::class)->only(['destroy'])->where($numericos);
        Route::apiResource('recetas', RecetaController::class)->only(['destroy'])->where($numericos);
        Route::apiResource('recetas.detalles', RecetaDetalleController::class)->only(['destroy'])->where($numericos);

        Route::get('/productos/papelera', [ProductoController::class, 'papelera']);
        Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->whereNumber('id');
        Route::put('/productos/{id}/restaurar', [ProductoController::class, 'restore'])->whereNumber('id');
        Route::delete('/productos/{id}/definitivo', [ProductoController::class, 'forceDestroy'])->whereNumber('id');

        Route::delete('/especialidades/{id}', [EspecialidadController::class, 'destroy'])->whereNumber('id');
        Route::delete('/servicios-medicos/{id}', [ServicioMedicoController::class, 'destroy'])->whereNumber('id');
        Route::delete('/usuario-roles/{id_usuario}/{id_rol}', [UsuarioRolController::class, 'destroy'])->whereNumber(['id_usuario', 'id_rol']);
        Route::delete('/sesiones-sociales/{id}', [SesionSocialController::class, 'destroy'])->whereNumber('id');
        Route::delete('/historial-transacciones/{id}', [HistorialTransaccionController::class, 'destroy'])->whereNumber('id');

        foreach ($modulos as $modulo) {
            Route::delete("/{$modulo}/{id}", [AdminRecordController::class, 'destroy'])->whereNumber('id')->name("admin.{$modulo}.destroy");
        }

        Route::apiResource('roles', RolController::class)->parameters(['roles' => 'id'])->where(['id' => '[0-9]+']);
        Route::apiResource('logs', LogController::class)->only(['index', 'show', 'store', 'destroy'])->parameters(['logs' => 'id'])->where(['id' => '[0-9]+']);
    });
});