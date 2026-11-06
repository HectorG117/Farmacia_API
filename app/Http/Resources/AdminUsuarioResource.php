<?php

namespace App\Http\Resources;

use App\Models\Administrador;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Administrador) {
            return [
                'id' => $this->id_administrador,
                'tipo' => 'administrador',
                'nombre' => $this->nombre_completo,
                'nombre_usuario' => $this->nombre_usuario,
                'nivel' => $this->nivel,
                'activo' => $this->activo,
            ];
        }

        /** @var Usuario $usuario */
        $usuario = $this->resource;

        return [
            'id' => $usuario->id_usuario,
            'tipo' => 'usuario',
            'nombre' => $usuario->nombre_completo,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono,
            'roles' => $this->whenLoaded('roles', fn () => $usuario->roles->pluck('nombre')->values()),
            'activo' => $usuario->activo,
            'fecha_registro' => $usuario->fecha_registro?->toIso8601String(),
        ];
    }
}
