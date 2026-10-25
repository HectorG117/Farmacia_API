<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'precio',
        'stock',
        'categoria',
        'imagen',
    ];

    // Renglones de recetas médicas en los que aparece este producto
    public function recetaDetalles(): HasMany
    {
        return $this->hasMany(
            RecetaDetalle::class,
            'producto_id',
            'id'
        );
    }
}
