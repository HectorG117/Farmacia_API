<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminProductoResource;
use App\Models\Producto;
use App\Support\RespuestaApi;
use Illuminate\Http\JsonResponse;

/**
 * Catálogo público del Área del Cliente (Actividad 4.4 / 5.3).
 * Estas rutas están FUERA del grupo auth:sanctum: no requieren token.
 */
class CatalogoController extends Controller
{
    /**
     * Consulta pública del catálogo de productos disponibles.
     * Endpoint: GET /api/v1/client/catalogo
     * (Los productos con borrado lógico no se incluyen gracias a SoftDeletes.)
     */
    public function index(): JsonResponse
    {
        $productos = Producto::orderBy('nombre')->get();

        return response()->json([
            'data' => AdminProductoResource::collection($productos),
        ], 200);
    }

    /**
     * Muestra el detalle de un producto del catálogo por su ID.
     * Endpoint: GET /api/v1/client/catalogo/{id}
     *
     * Además del producto devuelve:
     *  - galeria:      lista de URLs de imagen del producto
     *  - relacionados: hasta 4 productos de la misma categoría
     */
    public function show(int $id): JsonResponse
    {
        $producto = Producto::find($id);

        if (! $producto) {
            return RespuestaApi::error('El producto solicitado no existe o ya no está disponible', 404);
        }

        $recurso = (new AdminProductoResource($producto))->resolve();

        $relacionados = Producto::where('categoria', $producto->categoria)
            ->where('id', '!=', $producto->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return response()->json([
            'data' => array_merge($recurso, [
                'galeria'    => array_values(array_filter([$recurso['imagen_url']])),
                'disponible' => $producto->stock > 0,
            ]),
            'relacionados' => AdminProductoResource::collection($relacionados),
        ], 200);
    }
}
