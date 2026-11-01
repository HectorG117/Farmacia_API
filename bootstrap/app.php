<?php

use App\Support\RespuestaApi;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // API: restringe rutas a un rol (admin, medico o paciente) -> ->middleware('rol:medico')
            'rol' => \App\Http\Middleware\VerificarRol::class,

            // API v1 (actividad 4.2): Accept JSON + respuestas 400 / 413 / 415 antes del controlador
            'api.v1' => \App\Http\Middleware\PrepararSolicitudApi::class,
        ]);

        // Sin vistas ni páginas de login: a quien no envía un token válido se le responde 401 en JSON
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ninguna respuesta es una vista: todos los errores (401, 403, 404, 405, 413, 422, 500...)
        // se regresan como JSON con su código HTTP, aunque no se envíe "Accept: application/json".
        $exceptions->shouldRenderJsonWhen(fn () => true);

        // Formato estándar { success, message, data, errors } para TODOS los errores, incluidos
        // los que ocurren antes de llegar al controlador: ruta inexistente (404), método no
        // permitido (405), solicitud demasiado grande (413, PostTooLargeException), errores de
        // validación (422) y errores internos (500). El detalle técnico se sigue registrando en
        // storage/logs/laravel.log, pero nunca se envía al cliente (aunque APP_DEBUG=true).
        $exceptions->render(function (Throwable $e, Request $request) {
            // Respuestas ya armadas a propósito (por ejemplo desde un FormRequest) se respetan
            if ($e instanceof HttpResponseException) {
                return null;
            }

            return RespuestaApi::desdeExcepcion($e);
        });
    })->create();
