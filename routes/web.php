<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Web
|--------------------------------------------------------------------------
|
| Definición de rutas web para la aplicación Farmacia API.
|
*/

// 1. Ruta raíz informativa de la API
Route::get('/', fn () => response()->json([
    'nombre'  => config('app.name'),
    'mensaje' => 'Esta aplicación es una API: todas las rutas están en /api y responden JSON.',
    'login'   => [
        'admin'    => url('/api/admin/login'),
        'medico'   => url('/api/medico/login'),
        'paciente' => url('/api/paciente/login'),
    ],
]));

// 2. Ruta para el Panel Administrativo
Route::get('/admin/{any?}', function () {
    try {
        return view('dashboard');
    } catch (\Throwable $e) {
        // Muestra el detalle real del error en pantalla si falla la plantilla
        return response("Error en la vista: " . $e->getMessage(), 500);
    }
})->where('any', '.*');