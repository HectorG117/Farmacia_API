<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medico extends Model
{
    protected $table = 'medicos';

    protected $primaryKey = 'id_medico';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'cedula',
        'especialidad_id',
        'consultorio',
    ];

    // Cuenta de usuario del médico (nombre, correo, teléfono, estado)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(
            Especialidad::class,
            'especialidad_id',
            'id_especialidad'
        );
    }

    // Citas que atiende el médico
    public function citas(): HasMany
    {
        return $this->hasMany(
            Cita::class,
            'medico_id',
            'id_medico'
        );
    }

    // Recetas médicas que ha emitido este médico
    public function recetas(): HasMany
    {
        return $this->hasMany(
            Receta::class,
            'medico_id',
            'id_medico'
        );
    }

    // Médicos cuya cuenta de usuario está activa
    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereHas('usuario', fn ($q) => $q->where('activo', true));
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->usuario?->nombre_completo ?? "Médico #{$this->id_medico}";
    }

    public function getActivoAttribute(): bool
    {
        return (bool) $this->usuario?->activo;
    }

    // Un médico con citas o recetas no se puede borrar (las llaves foráneas lo impiden)
    public function tieneHistorial(): bool
    {
        return $this->citas()->exists() || $this->recetas()->exists();
    }
}
