<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Medico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Especialidades médicas (responde JSON para la API).
 */
class EspecialidadController extends Controller
{
    // GET /api/especialidades
    public function index(): JsonResponse
    {
        return response()->json(['data' => Especialidad::all()]);
    }

    // GET /api/especialidades/{id}
    public function show(int $id): JsonResponse
    {
        $especialidad = Especialidad::find($id);

        return $especialidad
            ? response()->json(['data' => $especialidad])
            : $this->noEncontrada();
    }

    // POST /api/especialidades
    public function store(Request $request): JsonResponse
    {
        $datos = $this->validar($request);

        $especialidad = Especialidad::create($datos);

        return response()->json([
            'mensaje' => 'Especialidad creada exitosamente.',
            'data'    => $especialidad,
        ], 201);
    }

    // PUT /api/especialidades/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $especialidad = Especialidad::find($id);

        if (!$especialidad) {
            return $this->noEncontrada();
        }

        $especialidad->update($this->validar($request));

        return response()->json([
            'mensaje' => 'Especialidad actualizada exitosamente.',
            'data'    => $especialidad,
        ]);
    }

    // DELETE /api/especialidades/{id}
    public function destroy(int $id): JsonResponse
    {
        $especialidad = Especialidad::find($id);

        if (!$especialidad) {
            return $this->noEncontrada();
        }

        // La llave foránea de "medicos" impide borrar una especialidad en uso
        if (Medico::where('especialidad_id', $especialidad->id_especialidad)->exists()) {
            return response()->json([
                'mensaje' => "No se puede eliminar \"{$especialidad->nombre}\" porque hay médicos con esa especialidad.",
            ], 409);
        }

        $especialidad->delete();

        return response()->json(['mensaje' => 'Especialidad eliminada exitosamente.']);
    }

    // ------------------------------------------------------------------

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre'      => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:500',
        ]);
    }

    private function noEncontrada(): JsonResponse
    {
        return response()->json(['mensaje' => 'La especialidad solicitada no existe.'], 404);
    }
}
