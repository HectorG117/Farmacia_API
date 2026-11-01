<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\RespuestaApi;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * Base de los controladores de /api/v1.
 *
 * Reúne lo que comparten todos los recursos para no duplicar lógica:
 * respuestas con el formato estándar, comprobación del identificador,
 * y los mensajes de validación en español que usa Validator::make().
 */
abstract class ApiController extends Controller
{
    protected function exito(string $mensaje, mixed $data = null, int $codigo = 200): JsonResponse
    {
        return RespuestaApi::exito($mensaje, $data, $codigo);
    }

    protected function error(string $mensaje, int $codigo, mixed $errores = null): JsonResponse
    {
        return RespuestaApi::error($mensaje, $codigo, $errores);
    }

    // 404 cuando find() devuelve null
    protected function noEncontrado(string $mensaje): JsonResponse
    {
        return $this->error($mensaje, 404);
    }

    // 422 con el detalle de cada campo
    protected function errorValidacion(ValidatorContract $validator): JsonResponse
    {
        return $this->error('Los datos enviados no son válidos', 422, $validator->errors());
    }

    /**
     * 400 cuando el {id} de la URL no es un entero positivo (por ejemplo "abc" o "-3").
     * Devuelve null si el identificador es correcto.
     */
    protected function idInvalido(string $id): ?JsonResponse
    {
        if (ctype_digit($id) && (int) $id > 0 && strlen($id) <= 18) {
            return null;
        }

        return $this->error(
            'El identificador proporcionado no es válido',
            400,
            ['id' => ['El identificador debe ser un número entero positivo.']]
        );
    }

    // 400 cuando un PATCH llega sin ninguno de los campos que se pueden modificar
    protected function sinCamposParaActualizar(array $permitidos): JsonResponse
    {
        return $this->error(
            'No se envió ningún campo para actualizar',
            400,
            ['campos_permitidos' => $permitidos]
        );
    }

    /**
     * Validator::make() con los mensajes en español del proyecto.
     * Cada controlador manda sus reglas y los nombres "bonitos" de sus campos.
     */
    protected function validador(array $datos, array $reglas, array $atributos = [], array $mensajes = []): ValidatorContract
    {
        return Validator::make($datos, $reglas, $mensajes + $this->mensajesBase(), $atributos);
    }

    protected function mensajesBase(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string'   => 'El campo :attribute debe ser texto.',
            'numeric'  => 'El campo :attribute debe ser un valor numérico.',
            'integer'  => 'El campo :attribute debe ser un número entero.',
            'boolean'  => 'El campo :attribute debe ser verdadero o falso (true/false o 1/0).',
            'decimal'  => 'El campo :attribute debe tener como máximo :decimal decimales.',
            'regex'    => 'El formato del campo :attribute no es válido.',
            'in'       => 'El valor seleccionado para :attribute no es válido.',
            'unique'   => 'Ya existe un registro con ese :attribute.',
            'exists'   => 'El :attribute seleccionado no existe.',
            'file'     => 'El campo :attribute debe ser un archivo.',
            'image'    => 'El archivo :attribute debe ser una imagen.',
            'mimes'    => 'El archivo :attribute debe ser de tipo: :values.',
            'uploaded' => 'No se pudo subir el archivo :attribute.',
            'dimensions' => 'La imagen :attribute no tiene dimensiones válidas (máximo 4000 x 4000 píxeles).',
            'prohibited' => 'El campo :attribute no se puede enviar en esta operación.',
            'min' => [
                'numeric' => 'El campo :attribute debe ser mayor o igual a :min.',
                'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
                'file'    => 'El archivo :attribute debe pesar al menos :min KB.',
                'array'   => 'El campo :attribute debe tener al menos :min elementos.',
            ],
            'max' => [
                'numeric' => 'El campo :attribute no debe ser mayor que :max.',
                'string'  => 'El campo :attribute no debe exceder :max caracteres.',
                'file'    => 'El archivo :attribute no debe pesar más de :max KB.',
                'array'   => 'El campo :attribute no debe tener más de :max elementos.',
            ],
        ];
    }
}
