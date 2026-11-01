<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Bitácora (logs) del sistema (responde JSON para la API). Solo administradores nivel 3.
 */
class LogController extends Controller
{
    // GET /api/logs
    public function index(): JsonResponse
    {
        return response()->json(['data' => Log::latest('fecha')->get()]);
    }

    // GET /api/logs/{id}
    public function show(int $id): JsonResponse
    {
        $log = Log::find($id);

        return $log ? response()->json(['data' => $log]) : $this->noEncontrado();
    }

    // POST /api/logs  -> la IP y la fecha se toman de la petición
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario', // la columna no acepta NULL
            'accion'     => 'required|string|max:255',
            'modulo'     => 'required|string|max:100',
        ]);

        $log = Log::create([
            'id_usuario' => $datos['id_usuario'],
            'accion'     => $datos['accion'],
            'modulo'     => $datos['modulo'],
            'ip'         => $request->ip(),
            'fecha'      => now(),
        ]);

        return response()->json([
            'mensaje' => 'Log registrado exitosamente.',
            'data'    => $log,
        ], 201);
    }

    // DELETE /api/logs/{id}
    public function destroy(int $id): JsonResponse
    {
        $log = Log::find($id);

        if (!$log) {
            return $this->noEncontrado();
        }

        $log->delete();

        return response()->json(['mensaje' => 'Log eliminado exitosamente.']);
    }

    private function noEncontrado(): JsonResponse
    {
        return response()->json(['mensaje' => 'El log solicitado no existe.'], 404);
    }
}
