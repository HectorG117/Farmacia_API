<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receta extends Model
{
    protected $table = 'recetas';

    protected $primaryKey = 'id_receta';

    public $timestamps = false;

    public const ESTADOS = ['Vigente', 'Surtida', 'Cancelada'];

    protected $fillable = [
        'medico_id',
        'paciente_id',
        'fecha_emision',
        'diagnostico',
        'indicaciones_generales',
        'estado',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
    ];

    // Médico que realizó la receta
    public function medico(): BelongsTo
    {
        return $this->belongsTo(
            Medico::class,
            'medico_id',
            'id_medico'
        );
    }

    // Paciente al que se le emitió la receta
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'paciente_id',
            'id_usuario'
        );
    }

    // Productos recetados con sus instrucciones de uso
    public function detalles(): HasMany
    {
        return $this->hasMany(
            RecetaDetalle::class,
            'id_receta',
            'id_receta'
        );
    }

    // Solo las recetas vigentes pueden recibir cambios del paciente
    public function esEditablePorPaciente(): bool
    {
        return $this->estado === 'Vigente';
    }

    // Total de los productos que el paciente eligió comprar
    public function getTotalCompraAttribute(): float
    {
        return (float) $this->detalles->sum(fn (RecetaDetalle $detalle) => $detalle->subtotal_compra);
    }

    public function getProductosSeleccionadosAttribute(): int
    {
        return $this->detalles->where('comprar', true)->count();
    }

    // Costo estimado de surtir toda la receta
    public function getTotalEstimadoAttribute(): float
    {
        return (float) $this->detalles->sum(
            fn (RecetaDetalle $detalle) => $detalle->cantidad * ($detalle->producto->precio ?? 0)
        );
    }
}
