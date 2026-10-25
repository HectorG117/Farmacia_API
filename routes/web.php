<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminRecordController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\DoctorAuthController;
use App\Http\Controllers\DoctorPortalController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\ExpedienteMedicoController;
use App\Http\Controllers\HistorialTransaccionController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\ServicioMedicoController;
use App\Http\Controllers\SesionSocialController;
use App\Http\Controllers\UsuarioRolController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas
Route::get('/', [ProductoController::class, 'index'])->name('home');

Route::view('/Blog', 'SobreNosotros.blog');
Route::view('/Preguntas', 'SobreNosotros.preguntas');
Route::view('/Contacto', 'SobreNosotros.contacto');
Route::view('/Politicas', 'SobreNosotros.politicas');
Route::view('/Sucursales', 'SobreNosotros.sucursal');

Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/{producto}', [ProductoController::class, 'show'])
    ->whereNumber('producto')
    ->name('productos.show');

Route::middleware('guest:doctor')->group(function () {
    Route::get('/doctor/login', [DoctorAuthController::class, 'create'])->name('doctor.login');
    Route::post('/doctor/login', [DoctorAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('doctor.login.store');
});

Route::middleware('auth:doctor')->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/', [DoctorPortalController::class, 'index'])->name('dashboard');
    Route::post('/logout', [DoctorAuthController::class, 'destroy'])->name('logout');
    Route::put('/citas/{id}', [DoctorPortalController::class, 'updateAppointment'])
        ->whereNumber('id')
        ->name('citas.update');
    Route::get('/citas/{id}/expediente', [DoctorPortalController::class, 'editExpediente'])
        ->whereNumber('id')
        ->name('expedientes.edit');
    Route::put('/citas/{id}/expediente', [DoctorPortalController::class, 'saveExpediente'])
        ->whereNumber('id')
        ->name('expedientes.update');
});

// Invitados (Login)
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

// Panel Administrativo Protegido por Guardia admin
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

    // ==========================================
    // NIVEL 1, 2 Y 3: Consultas / Lectura
    // ==========================================
    Route::middleware('can:ver-registros')->group(function () {
        Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');

        // Médicos, citas y expedientes (consulta)
        Route::get('/admin/medicos', [MedicoController::class, 'index'])->name('admin.medicos.index');
        Route::get('/admin/medicos/{medico}', [MedicoController::class, 'show'])->whereNumber('medico')->name('admin.medicos.show');
        Route::get('/admin/citas', [CitaController::class, 'index'])->name('admin.citas.index');
        Route::get('/admin/citas/{cita}', [CitaController::class, 'show'])->whereNumber('cita')->name('admin.citas.show');
        Route::get('/admin/expedientes', [ExpedienteMedicoController::class, 'index'])->name('admin.expedientes.index');
        Route::get('/admin/expedientes/{expediente}', [ExpedienteMedicoController::class, 'show'])->whereNumber('expediente')->name('admin.expedientes.show');

        // Recetas médicas (consulta)
        Route::get('/admin/recetas-medicas', [RecetaController::class, 'index'])->name('admin.recetas-medicas.index');
        Route::get('/admin/recetas-medicas/{receta}', [RecetaController::class, 'show'])
            ->whereNumber('receta')
            ->name('admin.recetas-medicas.show');

        // Módulos dinámicos de consulta
        Route::get('/admin/usuario-roles', [UsuarioRolController::class, 'index'])->name('admin.usuario-roles.index');
        Route::get('/admin/historial-transacciones', [HistorialTransaccionController::class, 'index'])->name('admin.historial-transacciones.index');
        Route::get('/admin/sesiones-sociales', [SesionSocialController::class, 'index'])->name('admin.sesiones-sociales.index');

        $managedModules = ['usuarios', 'carritos', 'carrito-detalles', 'users'];
        foreach ($managedModules as $module) {
            Route::get("/admin/{$module}", [AdminRecordController::class, 'index'])->name("admin.{$module}.index");
        }
    });

    // ==========================================
    // NIVEL 2 Y 3: Crear y Editar
    // ==========================================
    Route::middleware('can:crear-editar-registros')->group(function () {
        // Productos
        Route::get('/productos/crear', [ProductoController::class, 'create'])->name('productos.create');
        Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
        Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');

        // Recetas médicas (crear y editar)
        Route::get('/admin/recetas-medicas/crear', [RecetaController::class, 'create'])->name('admin.recetas-medicas.create');
        Route::post('/admin/recetas-medicas', [RecetaController::class, 'store'])->name('admin.recetas-medicas.store');
        Route::get('/admin/recetas-medicas/{receta}/editar', [RecetaController::class, 'edit'])
            ->whereNumber('receta')
            ->name('admin.recetas-medicas.edit');
        Route::put('/admin/recetas-medicas/{receta}', [RecetaController::class, 'update'])
            ->whereNumber('receta')
            ->name('admin.recetas-medicas.update');

        // Módulos dinámicos (Creación y Edición)
        Route::get('/admin/usuario-roles/crear', [UsuarioRolController::class, 'create'])->name('admin.usuario-roles.create');
        Route::post('/admin/usuario-roles', [UsuarioRolController::class, 'store'])->name('admin.usuario-roles.store');

        Route::get('/admin/historial-transacciones/crear', [HistorialTransaccionController::class, 'create'])->name('admin.historial-transacciones.create');
        Route::post('/admin/historial-transacciones', [HistorialTransaccionController::class, 'store'])->name('admin.historial-transacciones.store');

        Route::get('/admin/sesiones-sociales/crear', [SesionSocialController::class, 'create'])->name('admin.sesiones-sociales.create');
        Route::post('/admin/sesiones-sociales', [SesionSocialController::class, 'store'])->name('admin.sesiones-sociales.store');

        // Médicos, citas y expedientes (crear y editar)
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

        $managedModules = ['usuarios', 'carritos', 'carrito-detalles', 'users'];
        foreach ($managedModules as $module) {
            Route::get("/admin/{$module}/crear", [AdminRecordController::class, 'create'])->name("admin.{$module}.create");
            Route::post("/admin/{$module}", [AdminRecordController::class, 'store'])->name("admin.{$module}.store");
            Route::get("/admin/{$module}/{id}/editar", [AdminRecordController::class, 'edit'])->name("admin.{$module}.edit");
            Route::put("/admin/{$module}/{id}", [AdminRecordController::class, 'update'])->name("admin.{$module}.update");
        }
    });

    // ==========================================
    // SOLO NIVEL 3: Eliminar y Gestión de Cuentas / Roles / Logs
    // ==========================================
    Route::middleware('can:eliminar-registros')->group(function () {
        // Papelera y eliminación de productos
        Route::get('/productos/eliminados', [ProductoController::class, 'papelera'])->name('productos.papelera');
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
        Route::put('/productos/{producto}/restaurar', [ProductoController::class, 'restore'])->name('productos.restore');
        Route::delete('/productos/{producto}/eliminar-definitivo', [ProductoController::class, 'forceDestroy'])->name('productos.forceDestroy');

        // Médicos, citas y expedientes (eliminar)
        Route::delete('/admin/medicos/{medico}', [MedicoController::class, 'destroy'])->whereNumber('medico')->name('admin.medicos.destroy');
        Route::delete('/admin/citas/{cita}', [CitaController::class, 'destroy'])->whereNumber('cita')->name('admin.citas.destroy');
        Route::delete('/admin/expedientes/{expediente}', [ExpedienteMedicoController::class, 'destroy'])->whereNumber('expediente')->name('admin.expedientes.destroy');

        // Recetas médicas (eliminar)
        Route::delete('/admin/recetas-medicas/{receta}', [RecetaController::class, 'destroy'])
            ->whereNumber('receta')
            ->name('admin.recetas-medicas.destroy');

        // Eliminaciones dinámicas
        Route::delete('/admin/usuario-roles/{id_usuario}/{id_rol}', [UsuarioRolController::class, 'destroy'])->name('admin.usuario-roles.destroy');
        Route::delete('/admin/historial-transacciones/{id}', [HistorialTransaccionController::class, 'destroy'])->name('admin.historial-transacciones.destroy');
        Route::delete('/admin/sesiones-sociales/{id}', [SesionSocialController::class, 'destroy'])->name('admin.sesiones-sociales.destroy');

        $managedModules = ['usuarios', 'carritos', 'carrito-detalles', 'users'];
        foreach ($managedModules as $module) {
            Route::delete("/admin/{$module}/{id}", [AdminRecordController::class, 'destroy'])->name("admin.{$module}.destroy");
        }

        // Recursos administrativos exclusivos del Nivel 3
        Route::resource('admin/roles', RolController::class)->names('admin.roles');
        Route::resource('admin/logs', LogController::class)->names('admin.logs');
    });

    // Recursos compartidos (Especialidades y Servicios Médicos controlados internamente o mediante métodos implícitos)
    Route::resource('admin/especialidades', EspecialidadController::class)->names('admin.especialidades');
    Route::resource('admin/servicios-medicos', ServicioMedicoController::class)->names('admin.servicios-medicos');
});