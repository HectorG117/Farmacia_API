<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API: restringe rutas a un rol (admin, medico o paciente) -> ->middleware('rol:medico')
        $middleware->alias([
            'rol' => \App\Http\Middleware\VerificarRol::class,
        ]);

        // Sin vistas ni páginas de login: a quien no envía un token válido se le responde 401 en JSON
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ninguna respuesta es una vista: todos los errores (401, 403, 404, 405, 422, 500...)
        // se regresan como JSON con su código HTTP, aunque no se envíe "Accept: application/json".
        $exceptions->shouldRenderJsonWhen(fn () => true);
    })->create();
