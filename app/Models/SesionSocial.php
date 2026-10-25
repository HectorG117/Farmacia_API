<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesionSocial extends Model
{
    protected $table = 'sesiones_sociales';

    protected $primaryKey = 'id_sesion';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'proveedor',
        'provider_id',
        'fecha_vinculacion',
    ];

    protected $casts = [
        'fecha_vinculacion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }
}