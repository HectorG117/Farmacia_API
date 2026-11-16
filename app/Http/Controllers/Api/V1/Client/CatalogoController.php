<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminProductoResource;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;

class CatalogoController extends Controller
{
    /**
     * Consulta pública del catálogo de productos disponibles.
     * Endpoint: GET /api/v1/client/catalogo
     */
    public function index(): JsonResponse
    {
        $productos = Producto::all();

        return response()->json([
            'data' => AdminProductoResource::collection($productos),
        ], 200);
    }

    /**
     * Muestra el detalle de un producto del catálogo por su ID.
     * Endpoint: GET /api/v1/client/catalogo/{id}
     */
    public function show(int $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        return response()->json([
            'data' => new AdminProductoResource($producto),
        ], 200);
    }
}