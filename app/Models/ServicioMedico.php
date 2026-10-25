<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioMedico extends Model
{
    use HasFactory;

    protected $table = 'servicios_medicos';
    protected $primaryKey = 'id_servicio';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'costo',
    ];
}