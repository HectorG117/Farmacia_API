<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API: restringe rutas a un rol (admin, medico o paciente) -> ->middleware('rol:medico')
        $middleware->alias([
            'rol' => \App\Http\Middleware\VerificarRol::class,
        ]);

        // Antes: App\Http\Middleware\Authenticate (Laravel 10)
        // Invitados que intentan entrar al portal médico o al del paciente van a su login; el resto al de administración.
        $middleware->redirectGuestsTo(fn (Request $request) => match (true) {
            $request->is('doctor', 'doctor/*') => route('doctor.login'),
            $request->is('cliente', 'cliente/*') => route('cliente.login'),
            default => route('login'),
        });

        // Antes: App\Http\Middleware\RedirectIfAuthenticated (Laravel 10)
        // Usuarios ya autenticados que visitan un login se envían a su panel.
        $middleware->redirectUsersTo(fn (Request $request) => match (true) {
            $request->is('doctor', 'doctor/*') => route('doctor.dashboard'),
            $request->is('cliente', 'cliente/*') => route('cliente.dashboard'),
            default => route('admin.dashboard'),
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Las rutas /api/* siempre responden JSON (errores 401, 403, 404, 422...),
        // aunque el cliente no envíe el encabezado "Accept: application/json".
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
