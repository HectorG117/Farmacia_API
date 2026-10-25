<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarritoDetalle extends Model
{
    protected $table = 'carrito_detalle';

    protected $primaryKey = 'id_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_carrito',
        'id_servicio',
        'cantidad',
    ];

    public function carrito(): BelongsTo
    {
        return $this->belongsTo(
            Carrito::class,
            'id_carrito',
            'id_carrito'
        );
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(
            ServicioMedico::class,
            'id_servicio',
            'id_servicio'
        );
    }
}