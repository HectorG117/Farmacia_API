<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminProductoResource;
use App\Models\Especialidad;
use App\Models\Producto;
use App\Models\ServicioMedico;
use App\Support\RespuestaApi;
use Illuminate\Http\JsonResponse;

/**
 * Secciones informativas públicas (Actividad 5.3).
 * Endpoint: GET /api/v1/client/informacion   (sin token)
 *
 * Reúne en una sola respuesta los datos abiertos que usa el cliente final
 * para su página de inicio y la sección "Nosotros": contadores del catálogo,
 * categorías activas, productos destacados, avisos dinámicos, servicios
 * médicos activos y especialidades disponibles.
 */
class InformacionController extends Controller
{
    private const POCAS_PIEZAS = 5;

    public function index(): JsonResponse
    {
        $productos = Producto::all();

        // ---------- Categorías activas ----------
        $categorias = $productos->groupBy('categoria')->map(function ($grupo, $nombre) {
            $conImagen = $grupo->first(fn ($p) => ! empty($p->imagen));

            return [
                'nombre'      => $nombre,
                'total'       => $grupo->count(),
                'disponibles' => $grupo->where('stock', '>', 0)->count(),
                'imagen_url'  => $conImagen
                    ? (new AdminProductoResource($conImagen))->resolve()['imagen_url']
                    : null,
            ];
        })->sortByDesc('total')->values();

        // ---------- Destacados: con existencias, los de mayor stock ----------
        $destacados = $productos->where('stock', '>', 0)->sortByDesc('stock')->take(4)->values();

        // ---------- Avisos / novedades generados con datos reales ----------
        $avisos = [];

        $nuevo = $productos->sortByDesc('created_at')->first();
        if ($nuevo) {
            $avisos[] = [
                'tipo'        => 'novedad',
                'titulo'      => 'Nuevo en el catálogo',
                'mensaje'     => "Ya puedes encontrar {$nuevo->nombre} en la categoría {$nuevo->categoria}.",
                'producto_id' => $nuevo->id,
            ];
        }

        foreach ($productos->filter(fn ($p) => $p->stock > 0 && $p->stock <= self::POCAS_PIEZAS)->take(2) as $p) {
            $avisos[] = [
                'tipo'        => 'existencias',
                'titulo'      => '¡Últimas piezas!',
                'mensaje'     => "Quedan solo {$p->stock} piezas de {$p->nombre}.",
                'producto_id' => $p->id,
            ];
        }

        $masEconomico = $productos->where('stock', '>', 0)->sortBy('precio')->first();
        if ($masEconomico) {
            $avisos[] = [
                'tipo'        => 'promocion',
                'titulo'      => 'Precio bajo',
                'mensaje'     => "{$masEconomico->nombre} desde $" . number_format((float) $masEconomico->precio, 2) . '.',
                'producto_id' => $masEconomico->id,
            ];
        }

        // ---------- Servicios y especialidades ----------
        $servicios = ServicioMedico::where('activo', true)->orderBy('nombre')->get()
            ->map(fn ($s) => [
                'id'          => $s->id_servicio,
                'nombre'      => $s->nombre,
                'descripcion' => $s->descripcion,
                'costo'       => (float) $s->costo,
            ]);

        $especialidades = Especialidad::withCount('medicos')->orderBy('nombre')->get()
            ->map(fn ($e) => [
                'id'          => $e->id_especialidad,
                'nombre'      => $e->nombre,
                'descripcion' => $e->descripcion,
                'medicos'     => $e->medicos_count,
            ]);

        return RespuestaApi::exito('Información pública de la farmacia', [
            'resumen' => [
                'total_productos'   => $productos->count(),
                'disponibles'       => $productos->where('stock', '>', 0)->count(),
                'agotados'          => $productos->where('stock', '<=', 0)->count(),
                'total_categorias'  => $categorias->count(),
                'unidades_en_stock' => (int) $productos->sum('stock'),
                'precio_minimo'     => $productos->count() ? (float) $productos->min('precio') : null,
                'precio_maximo'     => $productos->count() ? (float) $productos->max('precio') : null,
                'servicios_activos' => $servicios->count(),
                'especialidades'    => $especialidades->count(),
            ],
            'categorias'     => $categorias,
            'destacados'     => AdminProductoResource::collection($destacados)->resolve(),
            'avisos'         => $avisos,
            'servicios'      => $servicios,
            'especialidades' => $especialidades,
            'generado_en'    => now()->toIso8601String(),
        ]);
    }
}
