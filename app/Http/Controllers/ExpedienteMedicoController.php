<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\ExpedienteMedico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Expedientes médicos (responde JSON para la API).
 */
class ExpedienteMedicoController extends Controller
{
    // GET /api/expedientes?buscar=
    public function index(Request $request): JsonResponse
    {
        $buscar = trim((string) $request->buscar);

        $expedientes = ExpedienteMedico::with(['cita.paciente', 'cita.medico.usuario'])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('diagnostico', 'like', "%{$buscar}%")
                        ->orWhereHas('cita.paciente', fn ($p) => $p
                            ->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('apellido', 'like', "%{$buscar}%"));
                });
            })
            ->latest('fecha')
            ->get();

        return response()->json(['data' => $expedientes]);
    }

    // POST /api/expedientes  (la cita queda como "Completada")
    public function store(Request $request): JsonResponse
    {
        $datos = $this->validar($request);

        $cita = Cita::find($datos['id_cita']);

        if ($cita->estado === 'Cancelada') {
            throw ValidationException::withMessages([
                'id_cita' => 'No se puede registrar un expediente para una cita cancelada.',
            ]);
        }

        $expediente = DB::transaction(function () use ($datos, $cita) {
            $expediente = ExpedienteMedico::create($datos);
            $cita->update(['estado' => 'Completada']);

            return $expediente;
        });

        return response()->json([
            'mensaje' => '¡Expediente médico registrado correctamente! La cita se marcó como completada.',
            'data'    => $expediente->load(['cita.paciente', 'cita.medico.usuario']),
        ], 201);
    }

    // GET /api/expedientes/{expediente}
    public function show(int $expediente): JsonResponse
    {
        $registro = ExpedienteMedico::with(['cita.paciente', 'cita.medico.usuario', 'cita.medico.especialidad'])
            ->find($expediente);

        if (!$registro) {
            return $this->noEncontrado();
        }

        return response()->json(['data' => $registro]);
    }

    // PUT/PATCH /api/expedientes/{expediente}  (la cita no se cambia; solo el contenido clínico)
    public function update(Request $request, int $expediente): JsonResponse
    {
        $registro = ExpedienteMedico::find($expediente);

        if (!$registro) {
            return $this->noEncontrado();
        }

        $datos = $this->validar($request, $registro);
        unset($datos['id_cita']);

        $registro->update($datos);

        return response()->json([
            'mensaje' => '¡Expediente actualizado correctamente!',
            'data'    => $registro->fresh(['cita.paciente', 'cita.medico.usuario']),
        ]);
    }

    // DELETE /api/expedientes/{expediente}  (la cita vuelve a "Confirmada")
    public function destroy(int $expediente): JsonResponse
    {
        $registro = ExpedienteMedico::with('cita')->find($expediente);

        if (!$registro) {
            return $this->noEncontrado();
        }

        DB::transaction(function () use ($registro) {
            $registro->cita?->update(['estado' => 'Confirmada']);
            $registro->delete();
        });

        return response()->json([
            'mensaje' => "El expediente #{$expediente} fue eliminado y su cita volvió a estado \"Confirmada\".",
        ]);
    }

    // ------------------------------------------------------------------

    private function noEncontrado(): JsonResponse
    {
        return response()->json(['mensaje' => 'El expediente solicitado no existe.'], 404);
    }

    private function validar(Request $request, ?ExpedienteMedico $expediente = null): array
    {
        return $request->validate([
            'id_cita'       => $expediente
                ? 'nullable'
                : ['required', 'integer', 'exists:citas,id_cita', Rule::unique('expediente_medico', 'id_cita')],
            'fecha'         => 'required|date|before_or_equal:now',
            'diagnostico'   => 'required|string|min:3|max:2000',
            'tratamiento'   => 'required|string|min:3|max:2000',
            'observaciones' => 'nullable|string|max:2000',
        ], [
            'id_cita.unique'        => 'Esa cita ya tiene un expediente registrado.',
            'fecha.before_or_equal' => 'La fecha del expediente no puede ser futura.',
        ], [
            'id_cita' => 'cita',
        ]);
    }
}
