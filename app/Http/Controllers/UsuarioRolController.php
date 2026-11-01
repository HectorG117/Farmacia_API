<?php

namespace App\Http\Controllers;

use App\Models\UsuarioRol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Asignación de roles a usuarios (responde JSON para la API).
 */
class UsuarioRolController extends Controller
{
    // GET /api/usuario-roles
    public function index(): JsonResponse
    {
        return response()->json(['data' => UsuarioRol::with(['usuario', 'rol'])->get()]);
    }

    // POST /api/usuario-roles  -> 201 si se asignó, 200 si el usuario ya tenía ese rol
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario',
            'id_rol'     => 'required|integer|exists:roles,id_rol',
        ]);

        $asignacion = UsuarioRol::firstOrCreate($datos);

        return response()->json([
            'mensaje' => $asignacion->wasRecentlyCreated
                ? 'Rol asignado correctamente.'
                : 'El usuario ya tenía asignado ese rol.',
            'data'    => UsuarioRol::with(['usuario', 'rol'])->where($datos)->first(),
        ], $asignacion->wasRecentlyCreated ? 201 : 200);
    }

    // DELETE /api/usuario-roles/{id_usuario}/{id_rol}
    public function destroy(int $id_usuario, int $id_rol): JsonResponse
    {
        $eliminadas = UsuarioRol::where('id_usuario', $id_usuario)
            ->where('id_rol', $id_rol)
            ->delete();

        if (!$eliminadas) {
            return response()->json(['mensaje' => 'Ese usuario no tiene asignado ese rol.'], 404);
        }

        return response()->json(['mensaje' => 'Asignación eliminada.']);
    }
}
