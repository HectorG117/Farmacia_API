<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas web
|--------------------------------------------------------------------------
| Farmacia API no regresa vistas: todo se consume como JSON desde /api
| (ver routes/api.php). La raíz solo informa cómo usar la API.
*/

Route::get('/', fn () => response()->json([
    'nombre'  => config('app.name'),
    'mensaje' => 'Esta aplicación es una API: todas las rutas están en /api y responden JSON.',
    'login'   => [
        'admin'    => url('/api/admin/login'),
        'medico'   => url('/api/medico/login'),
        'paciente' => url('/api/paciente/login'),
    ],
]));
