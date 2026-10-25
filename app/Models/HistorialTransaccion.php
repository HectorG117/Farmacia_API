<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialTransaccion extends Model
{
    protected $table = 'historial_transacciones';

    protected $primaryKey = 'id_transaccion';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_carrito',
        'total',
        'estado',
        'fecha',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function carrito(): BelongsTo
    {
        return $this->belongsTo(
            Carrito::class,
            'id_carrito',
            'id_carrito'
        );
    }
}