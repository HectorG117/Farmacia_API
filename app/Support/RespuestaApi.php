<?php

namespace App\Support;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

/**
 * Estructura JSON estandarizada de la API (actividad 4.2).
 *
 * Todas las respuestas con cuerpo llevan SIEMPRE los cuatro campos:
 *
 *   {
 *     "success": true|false,
 *     "message": "Texto descriptivo",
 *     "data":    {...} | [...] | null,
 *     "errors":  {...} | null
 *   }
 *
 * La usan los controladores de /api/v1 y el manejador global de
 * excepciones (bootstrap/app.php), para que los errores que ocurren
 * antes de llegar al controlador tengan exactamente el mismo formato.
 */
class RespuestaApi
{
    // Respuesta exitosa (200 consulta/actualización, 201 creación)
    public static function exito(string $mensaje, mixed $data = null, int $codigo = 200): JsonResponse
    {
        return self::armar(true, $mensaje, $data, null, $codigo);
    }

    // Respuesta de error (400, 404, 405, 409, 413, 415, 422, 500...)
    public static function error(string $mensaje, int $codigo, mixed $errores = null, array $headers = []): JsonResponse
    {
        return self::armar(false, $mensaje, null, $errores, $codigo, $headers);
    }

    /**
     * Convierte cualquier excepción en la respuesta JSON estándar.
     * Nunca expone trazas, consultas SQL, rutas internas ni credenciales:
     * el detalle técnico queda solo en storage/logs/laravel.log.
     */
    public static function desdeExcepcion(Throwable $e): JsonResponse
    {
        // Errores de validación lanzados con $request->validate() (rutas anteriores a v1)
        if ($e instanceof ValidationException) {
            return self::error('Los datos enviados no son válidos', 422, $e->errors());
        }

        // Rutas protegidas con Sanctum (fuera de /api/v1)
        if ($e instanceof AuthenticationException) {
            return self::error('Debes iniciar sesión para acceder a este recurso', 401);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            $permitidos = $e->getHeaders()['Allow'] ?? null;

            return self::error(
                'El método HTTP utilizado no está permitido para este endpoint',
                405,
                $permitidos ? ['metodos_permitidos' => array_map('trim', explode(',', $permitidos))] : null,
                $e->getHeaders()
            );
        }

        if ($e instanceof HttpExceptionInterface) {
            $codigo = $e->getStatusCode();

            return self::error(self::mensajePorCodigo($codigo), $codigo, null, $e->getHeaders());
        }

        // Cualquier otra cosa (QueryException, errores de PHP, etc.) es un error interno
        return self::error('Ocurrió un error interno al procesar la solicitud', 500);
    }

    public static function mensajePorCodigo(int $codigo): string
    {
        return match ($codigo) {
            400     => 'La solicitud es incorrecta',
            401     => 'Debes iniciar sesión para acceder a este recurso',
            403     => 'No tienes permiso para realizar esta acción',
            404     => 'El recurso o endpoint solicitado no existe',
            405     => 'El método HTTP utilizado no está permitido para este endpoint',
            409     => 'La operación entra en conflicto con el estado actual del recurso',
            413     => 'El archivo o la solicitud es demasiado grande',
            415     => 'El tipo de contenido enviado no es admitido',
            419     => 'La sesión expiró, vuelve a intentarlo',
            422     => 'Los datos enviados no son válidos',
            429     => 'Demasiadas solicitudes, espera un momento e inténtalo de nuevo',
            503     => 'El servicio no está disponible en este momento',
            default => $codigo >= 500
                ? 'Ocurrió un error interno al procesar la solicitud'
                : 'No se pudo procesar la solicitud',
        };
    }

    private static function armar(bool $exito, string $mensaje, mixed $data, mixed $errores, int $codigo, array $headers = []): JsonResponse
    {
        return response()->json([
            'success' => $exito,
            'message' => $mensaje,
            'data'    => $data,
            'errors'  => $errores,
        ], $codigo, $headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
