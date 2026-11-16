<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'producto',
        'monto',
        'detalles',
        'estado',
    ];

    /**
     * Relación inversa con el usuario propietario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}