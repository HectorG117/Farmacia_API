<?php

namespace App\Http\Middleware;

use App\Models\Administrador;
use App\Models\Usuario;
use App\Support\RespuestaApi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe una ruta de la API a un rol: admin, medico o paciente.
 *
 * Uso en rutas:  ->middleware(['auth:sanctum', 'rol:medico'])
 *
 * Revisa dos cosas:
 *  1) El tipo de cuenta del token (administradores -> admin; usuarios -> médico o paciente).
 *  2) Que el token se haya emitido para ese rol (habilidad de Sanctum "admin", "medico" o "paciente").
 * Así un token de paciente no puede usar las rutas del médico ni las del administrador.
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string $rol): Response
    {
        $usuario = $request->user();

        $cuentaValida = match ($rol) {
            'admin'              => $usuario instanceof Administrador,
            'medico', 'paciente' => $usuario instanceof Usuario,
            default              => false,
        };

        $token = $usuario?->currentAccessToken();

        if ($usuario instanceof Administrador && ! $usuario->activo) {
            return RespuestaApi::error('La cuenta de administrador está deshabilitada.', 403);
        }

        if ($usuario instanceof Usuario && ! $usuario->activo) {
            return RespuestaApi::error('La cuenta está deshabilitada.', 403);
        }

        if (! $cuentaValida || ! $token || ! $token->can($rol)) {
            return RespuestaApi::error(
                "Tu token no tiene permiso para las rutas del rol \"{$rol}\".",
                403
            );
        }

        return $next($request);
    }
}
