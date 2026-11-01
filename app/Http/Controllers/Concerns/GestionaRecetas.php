<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Producto;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Lógica de recetas compartida por el panel administrativo y el portal médico.
 */
trait GestionaRecetas
{
    // Productos activos + los que ya estaban en la receta aunque hoy estén en la papelera
    protected function productosParaFormulario(?Receta $receta = null)
    {
        $idsEnReceta = $receta ? $receta->detalles->pluck('producto_id') : collect();

        return Producto::withTrashed()
            ->where(fn ($q) => $q->whereNull('deleted_at')->orWhereIn('id', $idsEnReceta))
            ->orderBy('nombre')
            ->get();
    }

    /**
     * @param bool $soloProductosActivos al crear no se permiten productos en papelera
     * @param bool $conMedico            el médico del portal no elige médico: se toma de su sesión
     */
    protected function validarReceta(Request $request, bool $soloProductosActivos, bool $conMedico = true): array
    {
        $existeProducto = Rule::exists('productos', 'id');

        if ($soloProductosActivos) {
            $existeProducto->whereNull('deleted_at');
        }

        $reglas = [
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
        ];

        if ($conMedico) {
            $reglas['medico_id'] = 'required|integer|exists:medicos,id_medico';
        }

        return $request->validate($reglas, [
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

    protected function datosEncabezado(array $datos): array
    {
        return collect($datos)->only([
            'medico_id', 'paciente_id', 'fecha_emision',
            'diagnostico', 'indicaciones_generales', 'estado',
        ])->all();
    }

    /**
     * Reemplaza los productos de la receta. Si el paciente ya había elegido comprar
     * alguno que se conserva, su selección se mantiene (ajustada a la nueva cantidad).
     */
    protected function guardarDetalles(Receta $receta, array $datos): void
    {
        $seleccionPrevia = $receta->detalles()->get()->keyBy('producto_id');

        $receta->detalles()->delete();

        $receta->detalles()->createMany(
            collect($datos['productos'])->map(function ($p) use ($seleccionPrevia) {
                $previo = $seleccionPrevia->get((int) $p['producto_id']);
                $comprar = (bool) ($previo?->comprar);

                return [
                    'producto_id'     => $p['producto_id'],
                    'cantidad'        => $p['cantidad'],
                    'dosis'           => $p['dosis'],
                    'frecuencia'      => $p['frecuencia'],
                    'duracion'        => $p['duracion'],
                    'instrucciones'   => $p['instrucciones'] ?? null,
                    'comprar'         => $comprar,
                    'cantidad_compra' => $comprar ? min((int) $previo->cantidad_compra, (int) $p['cantidad']) : 0,
                ];
            })->values()->all()
        );
    }
}
