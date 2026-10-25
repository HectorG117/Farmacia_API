<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cita extends Model
{
    protected $table = 'citas';

    protected $primaryKey = 'id_cita';

    public $timestamps = false;

    public const ESTADOS = ['Pendiente', 'Confirmada', 'Completada', 'Cancelada'];

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'fecha',
        'hora',
        'estado',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Paciente que solicita la cita
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'paciente_id',
            'id_usuario'
        );
    }

    // Médico que atiende la cita
    public function medico(): BelongsTo
    {
        return $this->belongsTo(
            Medico::class,
            'medico_id',
            'id_medico'
        );
    }

    // Expediente generado al atender la cita (uno por cita)
    public function expediente(): HasOne
    {
        return $this->hasOne(
            ExpedienteMedico::class,
            'id_cita',
            'id_cita'
        );
    }

    // Citas que todavía ocupan un horario en la agenda del médico
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', '!=', 'Cancelada');
    }

    // Hora en formato HH:MM (la columna TIME regresa HH:MM:SS)
    public function getHoraCortaAttribute(): string
    {
        return substr((string) $this->hora, 0, 5);
    }

    // Texto corto para mostrar la cita en listas y selects
    public function getEtiquetaAttribute(): string
    {
        $folio = str_pad($this->id_cita, 4, '0', STR_PAD_LEFT);
        $fecha = $this->fecha?->format('d/m/Y');

        return "#{$folio} · {$fecha} {$this->hora_corta} · " . ($this->paciente?->nombre_completo ?? 'Paciente');
    }

    // Una cita cancelada o que ya tiene expediente no puede recibir otro
    public function puedeRegistrarExpediente(): bool
    {
        return $this->estado !== 'Cancelada' && !$this->expediente;
    }
}
