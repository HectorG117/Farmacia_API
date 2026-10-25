<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\RecetaDetalle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Renglones (productos) de una receta: /api/recetas/{receta}/detalles
 * Permite agregar, editar o quitar un producto sin reenviar toda la receta.
 */
class RecetaDetalleController extends Controller
{
    // GET /api/recetas/{receta}/detalles
    public function index(int $receta): JsonResponse
    {
        $registro = Receta::find($receta);

        if (!$registro) {
            return $this->recetaNoEncontrada();
        }

        return response()->json([
            'data' => $registro->detalles()->with('producto')->get(),
        ]);
    }

    // POST /api/recetas/{receta}/detalles
    public function store(Request $request, int $receta): JsonResponse
    {
        $registro = Receta::find($receta);

        if (!$registro) {
            return $this->recetaNoEncontrada();
        }

        $datos = $this->validar($request, $registro);

        $detalle = $registro->detalles()->create($datos);

        return response()->json([
            'mensaje' => 'Producto agregado a la receta.',
            'data'    => $detalle->load('producto'),
        ], 201);
    }

    // GET /api/recetas/{receta}/detalles/{detalle}
    public function show(int $receta, int $detalle): JsonResponse
    {
        $registro = $this->buscar($receta, $detalle);

        if (!$registro) {
            return $this->detalleNoEncontrado();
        }

        return response()->json(['data' => $registro->load('producto')]);
    }

    // PUT/PATCH /api/recetas/{receta}/detalles/{detalle}
    public function update(Request $request, int $receta, int $detalle): JsonResponse
    {
        $registro = $this->buscar($receta, $detalle);

        if (!$registro) {
            return $this->detalleNoEncontrado();
        }

        $datos = $this->validar($request, $registro->receta, $registro);

        $registro->update($datos);

        return response()->json([
            'mensaje' => 'Producto de la receta actualizado.',
            'data'    => $registro->fresh('producto'),
        ]);
    }

    // DELETE /api/recetas/{receta}/detalles/{detalle}
    public function destroy(int $receta, int $detalle): JsonResponse
    {
        $registro = $this->buscar($receta, $detalle);

        if (!$registro) {
            return $this->detalleNoEncontrado();
        }

        // Una receta debe conservar al menos un producto (igual que en el formulario web)
        if ($registro->receta->detalles()->count() <= 1) {
            return response()->json([
                'mensaje' => 'La receta debe tener al menos un producto. Si ya no se necesita, elimina la receta completa.',
            ], 409);
        }

        $registro->delete();

        return response()->json(['mensaje' => 'Producto quitado de la receta.']);
    }

    // ------------------------------------------------------------------

    // El detalle debe pertenecer a la receta indicada en la URL
    private function buscar(int $receta, int $detalle): ?RecetaDetalle
    {
        return RecetaDetalle::with('receta')
            ->where('id_receta', $receta)
            ->where('id_detalle', $detalle)
            ->first();
    }

    private function recetaNoEncontrada(): JsonResponse
    {
        return response()->json(['mensaje' => 'La receta solicitada no existe.'], 404);
    }

    private function detalleNoEncontrado(): JsonResponse
    {
        return response()->json(['mensaje' => 'Ese producto no existe en la receta indicada.'], 404);
    }

    private function validar(Request $request, Receta $receta, ?RecetaDetalle $detalle = null): array
    {
        $existeProducto = Rule::exists('productos', 'id');

        // Al agregar o cambiar de producto solo se permiten productos activos
        if (!$detalle || (int) $request->input('producto_id') !== (int) $detalle->producto_id) {
            $existeProducto->whereNull('deleted_at');
        }

        return $request->validate([
            'producto_id'   => [
                'required', 'integer', $existeProducto,
                // Un producto no puede repetirse dentro de la misma receta
                Rule::unique('receta_detalle', 'producto_id')
                    ->where('id_receta', $receta->id_receta)
                    ->ignore($detalle?->id_detalle, 'id_detalle'),
            ],
            'cantidad'      => 'required|integer|min:1|max:99',
            'dosis'         => 'required|string|max:100',
            'frecuencia'    => 'required|string|max:100',
            'duracion'      => 'required|string|max:100',
            'instrucciones' => 'nullable|string|max:500',
        ], [
            'producto_id.unique' => 'Ese producto ya está en la receta.',
            'producto_id.exists' => 'El producto no existe o está en la papelera.',
        ], [
            'producto_id' => 'producto',
            'duracion'    => 'duración',
        ]);
    }
}
