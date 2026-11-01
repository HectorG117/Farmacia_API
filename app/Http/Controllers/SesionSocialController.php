<?php

namespace App\Http\Controllers;

use App\Models\SesionSocial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Sesiones sociales vinculadas a usuarios (responde JSON para la API).
 */
class SesionSocialController extends Controller
{
    // GET /api/sesiones-sociales
    public function index(): JsonResponse
    {
        return response()->json(['data' => SesionSocial::with('usuario')->get()]);
    }

    // GET /api/sesiones-sociales/{id}
    public function show(int $id): JsonResponse
    {
        $sesion = SesionSocial::with('usuario')->find($id);

        return $sesion ? response()->json(['data' => $sesion]) : $this->noEncontrada();
    }

    // POST /api/sesiones-sociales
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_usuario'  => 'required|integer|exists:usuarios,id_usuario',
            'proveedor'   => 'required|string|max:50',
            'provider_id' => 'required|string|max:200',
        ]);

        $sesion = SesionSocial::create([...$datos, 'fecha_vinculacion' => now()]);

        return response()->json([
            'mensaje' => 'Sesión social vinculada.',
            'data'    => $sesion->load('usuario'),
        ], 201);
    }

    // DELETE /api/sesiones-sociales/{id}
    public function destroy(int $id): JsonResponse
    {
        $sesion = SesionSocial::find($id);

        if (!$sesion) {
            return $this->noEncontrada();
        }

        $sesion->delete();

        return response()->json(['mensaje' => 'Sesión social eliminada.']);
    }

    private function noEncontrada(): JsonResponse
    {
        return response()->json(['mensaje' => 'La sesión social solicitada no existe.'], 404);
    }
}
