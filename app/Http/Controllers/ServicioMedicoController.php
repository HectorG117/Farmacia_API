<?php

namespace App\Http\Controllers;

use App\Models\ServicioMedico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Servicios médicos (responde JSON para la API).
 */
class ServicioMedicoController extends Controller
{
    // GET /api/servicios-medicos
    public function index(): JsonResponse
    {
        return response()->json(['data' => ServicioMedico::all()]);
    }

    // GET /api/servicios-medicos/{id}
    public function show(int $id): JsonResponse
    {
        $servicio = ServicioMedico::find($id);

        return $servicio
            ? response()->json(['data' => $servicio])
            : $this->noEncontrado();
    }

    // POST /api/servicios-medicos
    public function store(Request $request): JsonResponse
    {
        $servicio = ServicioMedico::create($this->validar($request));

        return response()->json([
            'mensaje' => 'Servicio médico creado exitosamente.',
            'data'    => $servicio,
        ], 201);
    }

    // PUT /api/servicios-medicos/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $servicio = ServicioMedico::find($id);

        if (!$servicio) {
            return $this->noEncontrado();
        }

        $servicio->update($this->validar($request));

        return response()->json([
            'mensaje' => 'Servicio médico actualizado exitosamente.',
            'data'    => $servicio,
        ]);
    }

    // DELETE /api/servicios-medicos/{id}
    public function destroy(int $id): JsonResponse
    {
        $servicio = ServicioMedico::find($id);

        if (!$servicio) {
            return $this->noEncontrado();
        }

        // La llave foránea de "carrito_detalle" impide borrar un servicio que está en carritos
        if (DB::table('carrito_detalle')->where('id_servicio', $servicio->id_servicio)->exists()) {
            return response()->json([
                'mensaje' => "No se puede eliminar \"{$servicio->nombre}\" porque está en carritos de compra.",
            ], 409);
        }

        $servicio->delete();

        return response()->json(['mensaje' => 'Servicio médico eliminado exitosamente.']);
    }

    // ------------------------------------------------------------------

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'costo'       => 'required|numeric|min:0',
        ]);
    }

    private function noEncontrado(): JsonResponse
    {
        return response()->json(['mensaje' => 'El servicio médico solicitado no existe.'], 404);
    }
}
