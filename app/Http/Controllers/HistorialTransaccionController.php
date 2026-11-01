<?php

namespace App\Http\Controllers;

use App\Models\HistorialTransaccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Historial de transacciones (responde JSON para la API).
 */
class HistorialTransaccionController extends Controller
{
    // GET /api/historial-transacciones
    public function index(): JsonResponse
    {
        $transacciones = HistorialTransaccion::with(['usuario', 'carrito'])
            ->orderByDesc('id_transaccion')
            ->get();

        return response()->json(['data' => $transacciones]);
    }

    // GET /api/historial-transacciones/{id}
    public function show(int $id): JsonResponse
    {
        $transaccion = HistorialTransaccion::with(['usuario', 'carrito'])->find($id);

        return $transaccion ? response()->json(['data' => $transaccion]) : $this->noEncontrada();
    }

    // POST /api/historial-transacciones  (si no se envía fecha se usa la actual)
    public function store(Request $request): JsonResponse
    {
        $datos = $this->validar($request);

        $transaccion = HistorialTransaccion::create([...$datos, 'fecha' => $datos['fecha'] ?? now()]);

        return response()->json([
            'mensaje' => 'Transacción registrada.',
            'data'    => $transaccion->load(['usuario', 'carrito']),
        ], 201);
    }

    // PUT /api/historial-transacciones/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $transaccion = HistorialTransaccion::find($id);

        if (!$transaccion) {
            return $this->noEncontrada();
        }

        $datos = $this->validar($request);
        $transaccion->update($datos);

        return response()->json([
            'mensaje' => 'Transacción actualizada.',
            'data'    => $transaccion->fresh(['usuario', 'carrito']),
        ]);
    }

    // DELETE /api/historial-transacciones/{id}
    public function destroy(int $id): JsonResponse
    {
        $transaccion = HistorialTransaccion::find($id);

        if (!$transaccion) {
            return $this->noEncontrada();
        }

        $transaccion->delete();

        return response()->json(['mensaje' => 'Transacción eliminada.']);
    }

    // ------------------------------------------------------------------

    private function validar(Request $request): array
    {
        return $request->validate([
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario',
            'id_carrito' => 'required|integer|exists:carrito,id_carrito',
            'total'      => 'required|numeric|min:0',
            'estado'     => 'required|string|max:30',
            'fecha'      => 'nullable|date',
        ]);
    }

    private function noEncontrada(): JsonResponse
    {
        return response()->json(['mensaje' => 'La transacción solicitada no existe.'], 404);
    }
}
