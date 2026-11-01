<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Roles de usuario (responde JSON para la API). Solo administradores nivel 3.
 */
class RolController extends Controller
{
    // GET /api/roles
    public function index(): JsonResponse
    {
        return response()->json(['data' => Rol::all()]);
    }

    // GET /api/roles/{id}
    public function show(int $id): JsonResponse
    {
        $rol = Rol::find($id);

        return $rol ? response()->json(['data' => $rol]) : $this->noEncontrado();
    }

    // POST /api/roles
    public function store(Request $request): JsonResponse
    {
        $rol = Rol::create($this->validar($request));

        return response()->json([
            'mensaje' => 'Rol creado exitosamente.',
            'data'    => $rol,
        ], 201);
    }

    // PUT /api/roles/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $rol = Rol::find($id);

        if (!$rol) {
            return $this->noEncontrado();
        }

        $rol->update($this->validar($request));

        return response()->json([
            'mensaje' => 'Rol actualizado exitosamente.',
            'data'    => $rol,
        ]);
    }

    // DELETE /api/roles/{id}  (también quita el rol a los usuarios que lo tenían)
    public function destroy(int $id): JsonResponse
    {
        $rol = Rol::find($id);

        if (!$rol) {
            return $this->noEncontrado();
        }

        $rol->usuarios()->detach();
        $rol->delete();

        return response()->json(['mensaje' => 'Rol eliminado exitosamente.']);
    }

    // ------------------------------------------------------------------

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre'      => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);
    }

    private function noEncontrado(): JsonResponse
    {
        return response()->json(['mensaje' => 'El rol solicitado no existe.'], 404);
    }
}
