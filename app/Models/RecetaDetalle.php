<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecetaDetalle extends Model
{
    protected $table = 'receta_detalle';

    protected $primaryKey = 'id_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_receta',
        'producto_id',
        'cantidad',
        'dosis',
        'frecuencia',
        'duracion',
        'instrucciones',
        'comprar',
        'cantidad_compra',
    ];

    protected $casts = [
        'comprar'         => 'boolean',
        'cantidad_compra' => 'integer',
    ];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(
            Receta::class,
            'id_receta',
            'id_receta'
        );
    }

    // Se incluyen productos en papelera para que una receta antigua siga mostrándolos
    public function producto(): BelongsTo
    {
        return $this->belongsTo(
            Producto::class,
            'producto_id',
            'id'
        )->withTrashed();
    }

    // Máximo que el paciente puede comprar: lo recetado, limitado por la existencia
    public function getMaximoCompraAttribute(): int
    {
        $producto = $this->producto;

        if (!$producto || $producto->trashed()) {
            return 0;
        }

        return max(0, min((int) $this->cantidad, (int) $producto->stock));
    }

    public function getSubtotalCompraAttribute(): float
    {
        return $this->comprar ? $this->cantidad_compra * (float) ($this->producto->precio ?? 0) : 0.0;
    }
}
