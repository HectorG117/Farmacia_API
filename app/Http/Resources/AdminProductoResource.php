<?php

namespace App\Http\Resources;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Producto $producto */
        $producto = $this->resource;

        return [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'precio' => (float) $producto->precio,
            'stock' => (int) $producto->stock,
            'categoria' => $producto->categoria,
            'imagen' => $producto->imagen,
            'imagen_url' => $this->imagenUrl($producto->imagen),
            'eliminado' => $producto->trashed(),
            'created_at' => $producto->created_at?->toIso8601String(),
            'updated_at' => $producto->updated_at?->toIso8601String(),
            'deleted_at' => $producto->deleted_at?->toIso8601String(),
        ];
    }

    private function imagenUrl(?string $ruta): ?string
    {
        if (! $ruta) {
            return null;
        }

        if (Str::startsWith($ruta, ['http://', 'https://'])) {
            return $ruta;
        }

        return Storage::disk('public')->exists($ruta)
            ? Storage::disk('public')->url($ruta)
            : asset($ruta);
    }
}
