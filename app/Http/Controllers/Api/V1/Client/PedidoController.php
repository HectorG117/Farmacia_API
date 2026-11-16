<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\PedidoStoreRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Consulta la lista exclusiva de pedidos del cliente autenticado (Eloquent Scope).
     * Endpoint: GET /api/v1/client/mis-registros
     */
    public function index(Request $request): JsonResponse
    {
        $pedidos = Pedido::where('user_id', $request->user()->id)->get();

        return response()->json([
            'data' => PedidoResource::collection($pedidos),
        ], 200);
    }

    /**
     * Crea un nuevo pedido asignando el user_id desde el backend.
     * Endpoint: POST /api/v1/client/mis-registros
     */
    public function store(PedidoStoreRequest $request): JsonResponse
    {
        $pedido = $request->user()->pedidos()->create([
            'concepto' => $request->concepto,
            'monto'    => $request->monto,
            'detalles' => $request->detalles,
            'estado'   => 'pendiente',
        ]);

        return response()->json([
            'message' => 'Pedido creado exitosamente.',
            'data'    => new PedidoResource($pedido),
        ], 201);
    }

    /**
     * Muestra el detalle de un pedido propio (Valida propiedad vía Policy).
     * Endpoint: GET /api/v1/client/mis-registros/{pedido}
     */
    public function show(Pedido $pedido): JsonResponse
    {
        $this->authorize('view', $pedido);

        return response()->json([
            'data' => new PedidoResource($pedido),
        ], 200);
    }

    /**
     * Actualiza un pedido propio (Valida propiedad vía Policy).
     * Endpoint: PUT/PATCH /api/v1/client/mis-registros/{pedido}
     */
    public function update(Request $request, Pedido $pedido): JsonResponse
    {
        $this->authorize('update', $pedido);

        $pedido->update($request->only(['concepto', 'monto', 'detalles']));

        return response()->json([
            'message' => 'Pedido actualizado correctamente.',
            'data'    => new PedidoResource($pedido),
        ], 200);
    }

    /**
     * Cancela/Elimina un pedido propio (Valida propiedad y estado cancelable vía Policy).
     * Endpoint: DELETE /api/v1/client/mis-registros/{pedido}
     */
    public function destroy(Pedido $pedido): JsonResponse
    {
        $this->authorize('delete', $pedido);

        $pedido->delete();

        return response()->json([
            'message' => 'Pedido cancelado/eliminado correctamente.',
        ], 200);
    }
}