<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpedienteMedico extends Model
{
    protected $table = 'expediente_medico';

    protected $primaryKey = 'id_expediente';

    public $timestamps = false;

    protected $fillable = [
        'id_cita',
        'diagnostico',
        'tratamiento',
        'observaciones',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Cita de la que proviene el expediente (de ahí se obtienen paciente y médico)
    public function cita(): BelongsTo
    {
        return $this->belongsTo(
            Cita::class,
            'id_cita',
            'id_cita'
        );
    }
}
