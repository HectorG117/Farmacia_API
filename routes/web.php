<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRecordController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ClienteAuthController;
use App\Http\Controllers\ClientePortalController;
use App\Http\Controllers\DoctorAuthController;
use App\Http\Controllers\DoctorPortalController;
use App\Http\Controllers\DoctorRecetaController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\ExpedienteMedicoController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\ServicioMedicoController;
use App\Http\Controllers\SesionSocialController;
use App\Http\Controllers\UsuarioRolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas (catálogo)
|--------------------------------------------------------------------------
*/
Route::get('/', [ProductoController::class, 'index'])->name('home');

Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/{producto}', [ProductoController::class, 'show'])
    ->whereNumber('producto')
    ->name('productos.show');

/*
|--------------------------------------------------------------------------
| Rol MÉDICO: portal médico (guard "doctor")
|--------------------------------------------------------------------------
*/
Route::middleware('guest:doctor')->group(function () {
    Route::get('/doctor/login', [DoctorAuthController::class, 'create'])->name('doctor.login');
    Route::post('/doctor/login', [DoctorAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('doctor.login.store');
});

Route::middleware('auth:doctor')->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/', [DoctorPortalController::class, 'index'])->name('dashboard');
    Route::post('/logout', [DoctorAuthController::class, 'destroy'])->name('logout');

    // Citas y expedientes de sus pacientes
    Route::put('/citas/{id}', [DoctorPortalController::class, 'updateAppointment'])->whereNumber('id')->name('citas.update');
    Route::get('/citas/{id}/expediente', [DoctorPortalController::class, 'editExpediente'])->whereNumber('id')->name('expedientes.edit');
    Route::put('/citas/{id}/expediente', [DoctorPortalController::class, 'saveExpediente'])->whereNumber('id')->name('expedientes.update');

    // Recetas que emite (sin eliminar: se cancelan cambiando su estado)
    Route::get('/recetas', [DoctorRecetaController::class, 'index'])->name('recetas.index');
    Route::get('/recetas/crear', [DoctorRecetaController::class, 'create'])->name('recetas.create');
    Route::post('/recetas', [DoctorRecetaController::class, 'store'])->name('recetas.store');
    Route::get('/recetas/{id}', [DoctorRecetaController::class, 'show'])->whereNumber('id')->name('recetas.show');
    Route::get('/recetas/{id}/editar', [DoctorRecetaController::class, 'edit'])->whereNumber('id')->name('recetas.edit');
    Route::put('/recetas/{id}', [DoctorRecetaController::class, 'update'])->whereNumber('id')->name('recetas.update');
});

/*
|--------------------------------------------------------------------------
| Rol CLIENTE (paciente): portal del paciente (guard "cliente")
| Solo ve su información; en sus recetas elige qué comprar, no puede eliminarlas.
|--------------------------------------------------------------------------
*/
Route::middleware('guest:cliente')->group(function () {
    Route::get('/cliente/login', [ClienteAuthController::class, 'create'])->name('cliente.login');
    Route::post('/cliente/login', [ClienteAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('cliente.login.store');
    Route::get('/cliente/registro', [ClienteAuthController::class, 'registroForm'])->name('cliente.registro');
    Route::post('/cliente/registro', [ClienteAuthController::class, 'registro'])
        ->middleware('throttle:5,1')
        ->name('cliente.registro.store');
});

Route::middleware('auth:cliente')->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/', [ClientePortalController::class, 'index'])->name('dashboard');
    Route::post('/logout', [ClienteAuthController::class, 'destroy'])->name('logout');

    Route::get('/citas', [ClientePortalController::class, 'citas'])->name('citas.index');
    Route::get('/citas/{id}', [ClientePortalController::class, 'cita'])->whereNumber('id')->name('citas.show');

    Route::get('/expedientes', [ClientePortalController::class, 'expedientes'])->name('expedientes.index');
    Route::get('/expedientes/{id}', [ClientePortalController::class, 'expediente'])->whereNumber('id')->name('expedientes.show');

    Route::get('/recetas', [ClientePortalController::class, 'recetas'])->name('recetas.index');
    Route::get('/recetas/{id}', [ClientePortalController::class, 'receta'])->whereNumber('id')->name('recetas.show');
    Route::get('/recetas/{id}/compra', [ClientePortalController::class, 'editarCompra'])->whereNumber('id')->name('recetas.edit');
    Route::put('/recetas/{id}/compra', [ClientePortalController::class, 'actualizarCompra'])->whereNumber('id')->name('recetas.update');
});

/*
|--------------------------------------------------------------------------
| Rol ADMIN: panel administrativo (guard "admin", permisos por nivel)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

    // Módulos genéricos administrados por AdminRecordController
    $managedModules = ['usuarios', 'carritos', 'carrito-detalles', 'historial-transacciones', 'users'];

    // ==========================================
    // NIVEL 1, 2 Y 3: Consultas / Lectura
    // ==========================================
    Route::middleware('can:ver-registros')->group(function () use ($managedModules) {
        Route::get('/admin', AdminDashboardController::class)->name('admin.dashboard');

        // Médicos, citas y expedientes
        Route::get('/admin/medicos', [MedicoController::class, 'index'])->name('admin.medicos.index');
        Route::get('/admin/medicos/{medico}', [MedicoController::class, 'show'])->whereNumber('medico')->name('admin.medicos.show');
        Route::get('/admin/citas', [CitaController::class, 'index'])->name('admin.citas.index');
        Route::get('/admin/citas/{cita}', [CitaController::class, 'show'])->whereNumber('cita')->name('admin.citas.show');
        Route::get('/admin/expedientes', [ExpedienteMedicoController::class, 'index'])->name('admin.expedientes.index');
        Route::get('/admin/expedientes/{expediente}', [ExpedienteMedicoController::class, 'show'])->whereNumber('expediente')->name('admin.expedientes.show');

        // Recetas médicas
        Route::get('/admin/recetas-medicas', [RecetaController::class, 'index'])->name('admin.recetas-medicas.index');
        Route::get('/admin/recetas-medicas/{receta}', [RecetaController::class, 'show'])->whereNumber('receta')->name('admin.recetas-medicas.show');

        // Catálogos
        Route::get('/admin/especialidades', [EspecialidadController::class, 'index'])->name('admin.especialidades.index');
        Route::get('/admin/servicios-medicos', [ServicioMedicoController::class, 'index'])->name('admin.servicios-medicos.index');

        // Otros módulos
        Route::get('/admin/usuario-roles', [UsuarioRolController::class, 'index'])->name('admin.usuario-roles.index');
        Route::get('/admin/sesiones-sociales', [SesionSocialController::class, 'index'])->name('admin.sesiones-sociales.index');

        foreach ($managedModules as $module) {
            Route::get("/admin/{$module}", [AdminRecordController::class, 'index'])->name("admin.{$module}.index");
        }
    });

    // ==========================================
    // NIVEL 2 Y 3: Crear y Editar
    // ==========================================
    Route::middleware('can:crear-editar-registros')->group(function () use ($managedModules) {
        // Productos
        Route::get('/productos/crear', [ProductoController::class, 'create'])->name('productos.create');
        Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
        Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');

        // Recetas médicas
        Route::get('/admin/recetas-medicas/crear', [RecetaController::class, 'create'])->name('admin.recetas-medicas.create');
        Route::post('/admin/recetas-medicas', [RecetaController::class, 'store'])->name('admin.recetas-medicas.store');
        Route::get('/admin/recetas-medicas/{receta}/editar', [RecetaController::class, 'edit'])->whereNumber('receta')->name('admin.recetas-medicas.edit');
        Route::put('/admin/recetas-medicas/{receta}', [RecetaController::class, 'update'])->whereNumber('receta')->name('admin.recetas-medicas.update');

        // Médicos, citas y expedientes
        Route::get('/admin/medicos/crear', [MedicoController::class, 'create'])->name('admin.medicos.create');
        Route::post('/admin/medicos', [MedicoController::class, 'store'])->name('admin.medicos.store');
        Route::get('/admin/medicos/{medico}/editar', [MedicoController::class, 'edit'])->whereNumber('medico')->name('admin.medicos.edit');
        Route::put('/admin/medicos/{medico}', [MedicoController::class, 'update'])->whereNumber('medico')->name('admin.medicos.update');
        Route::get('/admin/citas/crear', [CitaController::class, 'create'])->name('admin.citas.create');
        Route::post('/admin/citas', [CitaController::class, 'store'])->name('admin.citas.store');
        Route::get('/admin/citas/{cita}/editar', [CitaController::class, 'edit'])->whereNumber('cita')->name('admin.citas.edit');
        Route::put('/admin/citas/{cita}', [CitaController::class, 'update'])->whereNumber('cita')->name('admin.citas.update');
        Route::get('/admin/expedientes/crear', [ExpedienteMedicoController::class, 'create'])->name('admin.expedientes.create');
        Route::post('/admin/expedientes', [ExpedienteMedicoController::class, 'store'])->name('admin.expedientes.store');
        Route::get('/admin/expedientes/{expediente}/editar', [ExpedienteMedicoController::class, 'edit'])->whereNumber('expediente')->name('admin.expedientes.edit');
        Route::put('/admin/expedientes/{expediente}', [ExpedienteMedicoController::class, 'update'])->whereNumber('expediente')->name('admin.expedientes.update');

        // Catálogos
        Route::get('/admin/especialidades/crear', [EspecialidadController::class, 'create'])->name('admin.especialidades.create');
        Route::post('/admin/especialidades', [EspecialidadController::class, 'store'])->name('admin.especialidades.store');
        Route::get('/admin/especialidades/{id}/editar', [EspecialidadController::class, 'edit'])->whereNumber('id')->name('admin.especialidades.edit');
        Route::put('/admin/especialidades/{id}', [EspecialidadController::class, 'update'])->whereNumber('id')->name('admin.especialidades.update');
        Route::get('/admin/servicios-medicos/crear', [ServicioMedicoController::class, 'create'])->name('admin.servicios-medicos.create');
        Route::post('/admin/servicios-medicos', [ServicioMedicoController::class, 'store'])->name('admin.servicios-medicos.store');
        Route::get('/admin/servicios-medicos/{id}/editar', [ServicioMedicoController::class, 'edit'])->whereNumber('id')->name('admin.servicios-medicos.edit');
        Route::put('/admin/servicios-medicos/{id}', [ServicioMedicoController::class, 'update'])->whereNumber('id')->name('admin.servicios-medicos.update');

        // Otros módulos
        Route::get('/admin/usuario-roles/crear', [UsuarioRolController::class, 'create'])->name('admin.usuario-roles.create');
        Route::post('/admin/usuario-roles', [UsuarioRolController::class, 'store'])->name('admin.usuario-roles.store');
        Route::get('/admin/sesiones-sociales/crear', [SesionSocialController::class, 'create'])->name('admin.sesiones-sociales.create');
        Route::post('/admin/sesiones-sociales', [SesionSocialController::class, 'store'])->name('admin.sesiones-sociales.store');

        foreach ($managedModules as $module) {
            Route::get("/admin/{$module}/crear", [AdminRecordController::class, 'create'])->name("admin.{$module}.create");
            Route::post("/admin/{$module}", [AdminRecordController::class, 'store'])->name("admin.{$module}.store");
            Route::get("/admin/{$module}/{id}/editar", [AdminRecordController::class, 'edit'])->whereNumber('id')->name("admin.{$module}.edit");
            Route::put("/admin/{$module}/{id}", [AdminRecordController::class, 'update'])->whereNumber('id')->name("admin.{$module}.update");
        }
    });

    // ==========================================
    // SOLO NIVEL 3: Eliminar y Gestión de Cuentas / Roles / Logs
    // ==========================================
    Route::middleware('can:eliminar-registros')->group(function () use ($managedModules) {
        // Papelera y eliminación de productos
        Route::get('/productos/eliminados', [ProductoController::class, 'papelera'])->name('productos.papelera');
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
        Route::put('/productos/{producto}/restaurar', [ProductoController::class, 'restore'])->name('productos.restore');
        Route::delete('/productos/{producto}/eliminar-definitivo', [ProductoController::class, 'forceDestroy'])->name('productos.forceDestroy');

        // Médicos, citas, expedientes y recetas
        Route::delete('/admin/medicos/{medico}', [MedicoController::class, 'destroy'])->whereNumber('medico')->name('admin.medicos.destroy');
        Route::delete('/admin/citas/{cita}', [CitaController::class, 'destroy'])->whereNumber('cita')->name('admin.citas.destroy');
        Route::delete('/admin/expedientes/{expediente}', [ExpedienteMedicoController::class, 'destroy'])->whereNumber('expediente')->name('admin.expedientes.destroy');
        Route::delete('/admin/recetas-medicas/{receta}', [RecetaController::class, 'destroy'])->whereNumber('receta')->name('admin.recetas-medicas.destroy');

        // Catálogos
        Route::delete('/admin/especialidades/{id}', [EspecialidadController::class, 'destroy'])->whereNumber('id')->name('admin.especialidades.destroy');
        Route::delete('/admin/servicios-medicos/{id}', [ServicioMedicoController::class, 'destroy'])->whereNumber('id')->name('admin.servicios-medicos.destroy');

        // Otros módulos
        Route::delete('/admin/usuario-roles/{id_usuario}/{id_rol}', [UsuarioRolController::class, 'destroy'])->name('admin.usuario-roles.destroy');
        Route::delete('/admin/sesiones-sociales/{id}', [SesionSocialController::class, 'destroy'])->name('admin.sesiones-sociales.destroy');

        foreach ($managedModules as $module) {
            Route::delete("/admin/{$module}/{id}", [AdminRecordController::class, 'destroy'])->whereNumber('id')->name("admin.{$module}.destroy");
        }

        // Recursos exclusivos del Nivel 3
        Route::resource('admin/roles', RolController::class)->except(['show'])->names('admin.roles');
        Route::resource('admin/logs', LogController::class)->only(['index', 'create', 'store', 'destroy'])->names('admin.logs');
    });
});
