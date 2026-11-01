<?php

namespace App\Http\Middleware;

use App\Support\RespuestaApi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware del grupo /api/v1 (alias "api.v1" en bootstrap/app.php).
 *
 * Revisa la solicitud ANTES de que llegue al controlador:
 *
 *  1. Marca la petición como "espera JSON" (Accept: application/json),
 *     para que Laravel nunca responda con una página HTML.
 *  2. 415 Unsupported Media Type -> el cuerpo no es JSON, multipart/form-data
 *     ni x-www-form-urlencoded (por ejemplo text/plain o application/xml).
 *  3. 400 Bad Request -> el cuerpo dice ser JSON pero está mal formado
 *     o no es un objeto ({...}).
 *  4. 413 Content Too Large -> PHP rechazó un archivo por superar
 *     upload_max_filesize (php.ini). El caso de post_max_size lo atiende
 *     el manejador de excepciones (PostTooLargeException).
 *
 * No exige sesión ni token: los endpoints de v1 son públicos en esta práctica.
 */
class PrepararSolicitudApi
{
    private const TIPOS_ADMITIDOS = [
        'application/json',
        'multipart/form-data',
        'application/x-www-form-urlencoded',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        if (in_array($request->getRealMethod(), ['POST', 'PUT', 'PATCH'], true) && $this->tieneCuerpo($request)) {
            $tipo = strtolower(trim(explode(';', (string) $request->header('Content-Type'))[0]));

            if (!$this->esTipoAdmitido($tipo)) {
                return RespuestaApi::error(
                    'El tipo de contenido enviado no es admitido',
                    415,
                    ['content_type' => ['Usa application/json, o multipart/form-data cuando envíes una imagen.']]
                );
            }

            if ($request->isJson() && ($error = $this->errorJson($request)) !== null) {
                return RespuestaApi::error('El cuerpo de la solicitud no es un JSON válido', 400, ['json' => [$error]]);
            }

            if ($campo = $this->archivoDemasiadoGrande($request->allFiles())) {
                return RespuestaApi::error(
                    'El archivo o la solicitud es demasiado grande',
                    413,
                    [$campo => ['El archivo supera el tamaño máximo que admite el servidor.']]
                );
            }
        }

        return $next($request);
    }

    private function tieneCuerpo(Request $request): bool
    {
        return (int) $request->header('Content-Length', 0) > 0
            || $request->header('Transfer-Encoding') !== null
            || count($request->allFiles()) > 0;
    }

    private function esTipoAdmitido(string $tipo): bool
    {
        // Acepta también variantes como application/vnd.api+json
        return in_array($tipo, self::TIPOS_ADMITIDOS, true) || str_ends_with($tipo, '+json');
    }

    private function errorJson(Request $request): ?string
    {
        $contenido = trim($request->getContent());

        if ($contenido === '') {
            return null;
        }

        $decodificado = json_decode($contenido, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'JSON mal formado: ' . json_last_error_msg() . '.';
        }

        if (!is_array($decodificado)) {
            return 'El cuerpo debe ser un objeto JSON, por ejemplo {"nombre": "..."}.';
        }

        return null;
    }

    // Devuelve el nombre del primer campo cuyo archivo PHP rechazó por tamaño
    private function archivoDemasiadoGrande(array $archivos, string $prefijo = ''): ?string
    {
        foreach ($archivos as $campo => $archivo) {
            $nombre = $prefijo === '' ? (string) $campo : "{$prefijo}.{$campo}";

            if (is_array($archivo)) {
                if ($encontrado = $this->archivoDemasiadoGrande($archivo, $nombre)) {
                    return $encontrado;
                }
                continue;
            }

            if ($archivo instanceof UploadedFile
                && in_array($archivo->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                return $nombre;
            }
        }

        return null;
    }
}
