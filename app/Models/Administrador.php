<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Administrador extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // HasApiTokens: tokens de la API (Sanctum)

    protected $table = 'administradores';

    protected $primaryKey = 'id_administrador';

    protected $fillable = [
        'nombre_completo',
        'nombre_usuario',
        'password',
        'activo',
        'nivel', // <-- Agregado para la Actividad 2
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'password' => 'hashed',
        'nivel' => 'integer', // <-- Opcional, para castear el valor a entero
    ];
}