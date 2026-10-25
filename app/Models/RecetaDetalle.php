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
}
