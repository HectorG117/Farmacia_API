<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';
    protected $primaryKey = 'id_especialidad';

    // Desactiva la gestión automática de created_at y updated_at
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
}