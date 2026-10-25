<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Recetas médicas (responde JSON para la API).
 * La receta se crea/actualiza junto con su arreglo "productos" (receta_detalle).
 * Para manejar renglones sueltos usa RecetaDetalleController.
 */
class RecetaController extends Controller
{
    // GET /api/recetas?estado=&medico_id=&paciente_id=
    public function index(Request $request): JsonResponse
    {
        $recetas = Receta::with(['medico.usuario', 'paciente'])
            ->withCount('detalles')
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('medico_id'), fn ($q) => $q->where('medico_id', $request->medico_id))
            ->when($request->filled('paciente_id'), fn ($q) => $q->where('paciente_id', $request->paciente_id))
            ->latest('fecha_emision')
            ->get();

        return response()->json(['data' => $recetas]);
    }

    // POST /api/recetas
    public function store(Request $request): JsonResponse
    {
        $datos = $this->validar($request, soloProductosActivos: true);

        $receta = DB::transaction(function () use ($datos) {
            $receta = Receta::create($this->datosEncabezado($datos));
            $receta->detalles()->createMany($this->datosDetalles($datos));

            return $receta;
        });

        return response()->json([
            'mensaje' => '¡Receta médica registrada correctamente!',
            'data'    => $this->conRelaciones($receta),
        ], 201);
    }

    // GET /api/recetas/{receta}
    public function show(int $receta): JsonResponse
    {
        $registro = Receta::find($receta);

        if (!$registro) {
            return $this->noEncontrada();
        }

        return response()->json(['data' => $this->conRelaciones($registro)]);
    }

    // PUT/PATCH /api/recetas/{receta}  (reemplaza el listado completo de productos)
    public function update(Request $request, int $receta): JsonResponse
    {
        $registro = Receta::find($receta);

        if (!$registro) {
            return $this->noEncontrada();
        }

        $datos = $this->validar($request, soloProductosActivos: false);

        DB::transaction(function () use ($registro, $datos) {
            $registro->update($this->datosEncabezado($datos));
            $registro->detalles()->delete();
            $registro->detalles()->createMany($this->datosDetalles($datos));
        });

        return response()->json([
            'mensaje' => '¡Receta actualizada correctamente!',
            'data'    => $this->conRelaciones($registro->fresh()),
        ]);
    }

    // DELETE /api/recetas/{receta}  (sus detalles se eliminan en cascada)
    public function destroy(int $receta): JsonResponse
    {
        $registro = Receta::find($receta);

        if (!$registro) {
            return $this->noEncontrada();
        }

        $registro->delete();

        return response()->json(['mensaje' => "La receta #{$receta} fue eliminada."]);
    }

    // ------------------------------------------------------------------

    private function noEncontrada(): JsonResponse
    {
        return response()->json(['mensaje' => 'La receta solicitada no existe.'], 404);
    }

    // Carga médico, paciente y productos, e incluye el total estimado en el JSON
    private function conRelaciones(Receta $receta): Receta
    {
        return $receta->load(['medico.usuario', 'medico.especialidad', 'paciente', 'detalles.producto'])
            ->append('total_estimado');
    }

    private function validar(Request $request, bool $soloProductosActivos): array
    {
        $existeProducto = Rule::exists('productos', 'id');

        if ($soloProductosActivos) {
            $existeProducto->whereNull('deleted_at');
        }

        return $request->validate([
            'medico_id'              => 'required|integer|exists:medicos,id_medico',
            'paciente_id'            => 'required|integer|exists:usuarios,id_usuario',
            'fecha_emision'          => 'required|date|before_or_equal:today',
            'diagnostico'            => 'nullable|string|max:255',
            'indicaciones_generales' => 'nullable|string|max:2000',
            'estado'                 => ['required', Rule::in(Receta::ESTADOS)],

            'productos'                 => 'required|array|min:1',
            'productos.*.producto_id'   => ['required', 'integer', 'distinct', $existeProducto],
            'productos.*.cantidad'      => 'required|integer|min:1|max:99',
            'productos.*.dosis'         => 'required|string|max:100',
            'productos.*.frecuencia'    => 'required|string|max:100',
            'productos.*.duracion'      => 'required|string|max:100',
            'productos.*.instrucciones' => 'nullable|string|max:500',
        ], [
            'productos.required'               => 'Agrega al menos un producto a la receta.',
            'productos.min'                    => 'Agrega al menos un producto a la receta.',
            'productos.*.producto_id.distinct' => 'Un mismo producto no puede aparecer dos veces en la receta.',
            'fecha_emision.before_or_equal'    => 'La fecha de emisión no puede ser futura.',
        ], [
            'medico_id'                 => 'médico',
            'paciente_id'               => 'paciente',
            'fecha_emision'             => 'fecha de emisión',
            'indicaciones_generales'    => 'indicaciones generales',
            'productos.*.producto_id'   => 'producto',
            'productos.*.cantidad'      => 'cantidad',
            'productos.*.dosis'         => 'dosis',
            'productos.*.frecuencia'    => 'frecuencia',
            'productos.*.duracion'      => 'duración',
            'productos.*.instrucciones' => 'instrucciones',
        ]);
    }

    private function datosEncabezado(array $datos): array
    {
        return collect($datos)->only([
            'medico_id', 'paciente_id', 'fecha_emision',
            'diagnostico', 'indicaciones_generales', 'estado',
        ])->all();
    }

    private function datosDetalles(array $datos): array
    {
        return collect($datos['productos'])
            ->map(fn ($p) => [
                'producto_id'   => $p['producto_id'],
                'cantidad'      => $p['cantidad'],
                'dosis'         => $p['dosis'],
                'frecuencia'    => $p['frecuencia'],
                'duracion'      => $p['duracion'],
                'instrucciones' => $p['instrucciones'] ?? null,
            ])
            ->values()
            ->all();
    }
}
