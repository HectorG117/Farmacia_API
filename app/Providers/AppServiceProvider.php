<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Regla: Consultar información (Nivel 1, 2 y 3)
        Gate::define('ver-registros', function ($user) {
            return in_array($user->nivel, [1, 2, 3]);
        });

        // Regla: Crear y editar registros (Nivel 2 y 3)
        Gate::define('crear-editar-registros', function ($user) {
            return in_array($user->nivel, [2, 3]);
        });

        // Regla: Eliminar registros y administrar cuentas (Solo Nivel 3)
        Gate::define('eliminar-registros', function ($user) {
            return (int) $user->nivel === 3;
        });
    }
}