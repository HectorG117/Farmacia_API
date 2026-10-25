<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Citas médicas (responde JSON para la API).
 */
class CitaController extends Controller
{
    // GET /api/citas?estado=&fecha=&medico_id=&paciente_id=&sin_expediente=1
    public function index(Request $request): JsonResponse
    {
        $citas = Cita::with(['paciente', 'medico.usuario', 'expediente'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('fecha'), fn ($q) => $q->whereDate('fecha', $request->fecha))
            ->when($request->filled('medico_id'), fn ($q) => $q->where('medico_id', $request->medico_id))
            ->when($request->filled('paciente_id'), fn ($q) => $q->where('paciente_id', $request->paciente_id))
            // Citas activas que todavía pueden recibir expediente
            ->when($request->boolean('sin_expediente'), fn ($q) => $q->activas()->whereDoesntHave('expediente'))
            ->orderByDesc('fecha')
            ->orderBy('hora')
            ->get();

        return response()->json(['data' => $citas]);
    }

    // POST /api/citas
    public function store(Request $request): JsonResponse
    {
        $datos = $this->validar($request);

        $cita = Cita::create($datos);

        return response()->json([
            'mensaje' => '¡Cita agendada correctamente!',
            'data'    => $cita->load(['paciente', 'medico.usuario']),
        ], 201);
    }

    // GET /api/citas/{cita}
    public function show(int $cita): JsonResponse
    {
        $registro = Cita::with(['paciente', 'medico.usuario', 'medico.especialidad', 'expediente'])->find($cita);

        if (!$registro) {
            return $this->noEncontrada();
        }

        return response()->json(['data' => $registro]);
    }

    // PUT/PATCH /api/citas/{cita}
    public function update(Request $request, int $cita): JsonResponse
    {
        $registro = Cita::with('expediente')->find($cita);

        if (!$registro) {
            return $this->noEncontrada();
        }

        $datos = $this->validar($request, $registro);

        // Una cita con expediente ya fue completada: no se puede cancelar ni regresar a pendiente
        if ($registro->expediente && $datos['estado'] !== 'Completada') {
            throw ValidationException::withMessages([
                'estado' => 'Esta cita ya tiene expediente médico, su estado debe ser "Completada".',
            ]);
        }

        $registro->update($datos);

        return response()->json([
            'mensaje' => '¡Cita actualizada correctamente!',
            'data'    => $registro->fresh(['paciente', 'medico.usuario', 'expediente']),
        ]);
    }

    // DELETE /api/citas/{cita}
    public function destroy(int $cita): JsonResponse
    {
        $registro = Cita::with('expediente')->find($cita);

        if (!$registro) {
            return $this->noEncontrada();
        }

        if ($registro->expediente) {
            return response()->json([
                'mensaje' => 'No se puede eliminar una cita que ya tiene expediente médico. Elimina primero el expediente.',
            ], 409);
        }

        $registro->delete();

        return response()->json(['mensaje' => "La cita #{$cita} fue eliminada."]);
    }

    // ------------------------------------------------------------------

    private function noEncontrada(): JsonResponse
    {
        return response()->json(['mensaje' => 'La cita solicitada no existe.'], 404);
    }

    private function validar(Request $request, ?Cita $cita = null): array
    {
        $datos = $request->validate([
            'paciente_id' => 'required|integer|exists:usuarios,id_usuario',
            'medico_id'   => 'required|integer|exists:medicos,id_medico',
            'fecha'       => $cita ? 'required|date' : 'required|date|after_or_equal:today',
            'hora'        => 'required|date_format:H:i',
            'estado'      => ['required', Rule::in(Cita::ESTADOS)],
            'motivo'      => 'nullable|string|max:1000',
        ], [
            'fecha.after_or_equal' => 'No se pueden agendar citas en fechas pasadas.',
        ], [
            'paciente_id' => 'paciente',
            'medico_id'   => 'médico',
            'motivo'      => 'motivo de consulta',
        ]);

        // El médico no puede tener dos citas activas a la misma fecha y hora
        if ($datos['estado'] !== 'Cancelada') {
            $ocupado = Cita::activas()
                ->where('medico_id', $datos['medico_id'])
                ->whereDate('fecha', $datos['fecha'])
                ->where('hora', 'like', $datos['hora'] . '%')
                ->when($cita, fn ($q) => $q->where('id_cita', '!=', $cita->id_cita))
                ->exists();

            if ($ocupado) {
                throw ValidationException::withMessages([
                    'hora' => 'El médico ya tiene una cita agendada en esa fecha y hora. Elige otro horario.',
                ]);
            }
        }

        return $datos;
    }
}
