<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Concerns\GestionaRecetas;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\ExpedienteMedico;
use App\Models\Medico;
use App\Models\Receta;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Rol MÉDICO (API, responde JSON). Misma lógica que el portal médico web
 * (DoctorPortalController y DoctorRecetaController):
 *  - Solo ve sus citas, los expedientes de esas citas y las recetas que él emitió.
 *  - Cambia el estado de sus citas y registra/edita el expediente de cada cita.
 *  - Emite y edita recetas solo a pacientes que tienen citas con él.
 *  - No elimina nada: una receta se anula cambiando su estado a "Cancelada".
 */
class MedicoController extends Controller
{
    use GestionaRecetas;

    // ------------------------------------------------------------------
    // Autenticación
    // ------------------------------------------------------------------

    // POST /api/medico/login
    public function login(Request $request): JsonResponse
    {
        $credenciales = $request->validate([
            'email'       => ['required', 'email', 'max:150'],
            'password'    => ['required', 'string'],
            'dispositivo' => ['nullable', 'string', 'max:100'],
        ]);

        $usuario = Usuario::where('email', $credenciales['email'])->first();

        if (!$usuario || !Hash::check($credenciales['password'], $usuario->password)) {
            return response()->json(['mensaje' => 'El correo o la contraseña son incorrectos.'], 401);
        }

        if (!$usuario->activo) {
            return response()->json(['mensaje' => 'La cuenta está deshabilitada.'], 403);
        }

        $medico = Medico::with('especialidad')->where('id_usuario', $usuario->id_usuario)->first();

        if (!$medico) {
            return response()->json(['mensaje' => 'Esta cuenta no tiene un perfil médico habilitado.'], 403);
        }

        $token = $usuario->createToken($credenciales['dispositivo'] ?? 'api', ['medico'])->plainTextToken;

        return response()->json([
            'mensaje'    => 'Inicio de sesión correcto.',
            'rol'        => 'medico',
            'token'      => $token,
            'tipo_token' => 'Bearer',
            'usuario'    => $usuario,
            'medico'     => $medico,
        ]);
    }

    // POST /api/medico/logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'La sesión se cerró correctamente.']);
    }

    // GET /api/medico/perfil
    public function perfil(): JsonResponse
    {
        return response()->json([
            'rol'  => 'medico',
            'data' => $this->medicoActual()->load(['usuario', 'especialidad']),
        ]);
    }

    // ------------------------------------------------------------------
    // Citas
    // ------------------------------------------------------------------

    // GET /api/medico/citas?estado=&fecha=
    public function citas(Request $request): JsonResponse
    {
        $citas = Cita::with(['paciente', 'expediente'])
            ->where('medico_id', $this->medicoActual()->id_medico)
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('fecha'), fn ($q) => $q->whereDate('fecha', $request->fecha))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        return response()->json(['data' => $citas]);
    }

    // GET /api/medico/citas/{id}
    public function cita(int $id): JsonResponse
    {
        $cita = $this->citaDelMedico($id);

        return $cita ? response()->json(['data' => $cita]) : $this->citaNoEncontrada();
    }

    // PUT /api/medico/citas/{id}   { "estado": "Confirmada" }
    public function actualizarCita(Request $request, int $id): JsonResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(Cita::ESTADOS)],
        ]);

        $cita = $this->citaDelMedico($id);

        if (!$cita) {
            return $this->citaNoEncontrada();
        }

        // Misma regla que el panel administrativo: una cita con expediente ya fue completada
        if ($cita->expediente && $datos['estado'] !== 'Completada') {
            throw ValidationException::withMessages([
                'estado' => 'Esta cita ya tiene expediente médico, su estado debe ser "Completada".',
            ]);
        }

        $cita->update($datos);

        return response()->json([
            'mensaje' => 'Estado de la cita actualizado.',
            'data'    => $cita->fresh(['paciente', 'expediente']),
        ]);
    }

    // ------------------------------------------------------------------
    // Expedientes (uno por cita)
    // ------------------------------------------------------------------

    // GET /api/medico/citas/{id}/expediente
    public function expediente(int $id): JsonResponse
    {
        $cita = $this->citaDelMedico($id);

        if (!$cita) {
            return $this->citaNoEncontrada();
        }

        if (!$cita->expediente) {
            return response()->json(['mensaje' => 'Esta cita todavía no tiene expediente médico.'], 404);
        }

        return response()->json([
            'data' => $cita->expediente,
            'cita' => $cita->withoutRelations()->load('paciente'),
        ]);
    }

    // PUT /api/medico/citas/{id}/expediente  -> crea o actualiza el expediente
    public function guardarExpediente(Request $request, int $id): JsonResponse
    {
        $datos = $request->validate([
            'diagnostico'   => ['required', 'string', 'min:3', 'max:2000'],
            'tratamiento'   => ['required', 'string', 'min:3', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $cita = $this->citaDelMedico($id);

        if (!$cita) {
            return $this->citaNoEncontrada();
        }

        if (!$cita->expediente && $cita->estado === 'Cancelada') {
            return response()->json([
                'mensaje' => 'No se puede registrar un expediente para una cita cancelada.',
            ], 409);
        }

        $nuevo = !$cita->expediente;

        DB::transaction(function () use ($cita, $datos) {
            if ($cita->expediente) {
                // Al editar se conserva la fecha original de atención
                $cita->expediente->update($datos);
            } else {
                ExpedienteMedico::create([...$datos, 'id_cita' => $cita->id_cita, 'fecha' => now()]);
            }

            // Igual que en el panel administrativo: con expediente, la cita queda completada
            $cita->update(['estado' => 'Completada']);
        });

        return response()->json([
            'mensaje' => 'Expediente médico guardado. La cita se marcó como completada.',
            'data'    => $cita->fresh(['paciente', 'expediente']),
        ], $nuevo ? 201 : 200);
    }

    // ------------------------------------------------------------------
    // Pacientes y recetas
    // ------------------------------------------------------------------

    // GET /api/medico/pacientes  -> pacientes que tienen al menos una cita con el médico
    public function pacientes(): JsonResponse
    {
        return response()->json(['data' => $this->pacientesDelMedico($this->medicoActual())]);
    }

    // GET /api/medico/recetas?estado=
    public function recetas(Request $request): JsonResponse
    {
        $recetas = Receta::with('paciente')
            ->withCount('detalles')
            ->where('medico_id', $this->medicoActual()->id_medico)
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->latest('fecha_emision')
            ->get();

        return response()->json(['data' => $recetas]);
    }

    // GET /api/medico/recetas/{id}
    public function receta(int $id): JsonResponse
    {
        $receta = $this->recetaPropia($id);

        return $receta
            ? response()->json(['data' => $receta->append('total_estimado')])
            : $this->recetaNoEncontrada();
    }

    // POST /api/medico/recetas  (el médico se toma del token, no se envía medico_id)
    public function crearReceta(Request $request): JsonResponse
    {
        $medico = $this->medicoActual();
        $datos = $this->validarReceta($request, soloProductosActivos: true, conMedico: false);

        $this->verificarPaciente($medico, (int) $datos['paciente_id']);

        $datos['medico_id'] = $medico->id_medico;

        $receta = DB::transaction(function () use ($datos) {
            $receta = Receta::create($this->datosEncabezado($datos));
            $this->guardarDetalles($receta, $datos);

            return $receta;
        });

        return response()->json([
            'mensaje' => 'Receta emitida correctamente.',
            'data'    => $this->recetaPropia($receta->id_receta)->append('total_estimado'),
        ], 201);
    }

    // PUT /api/medico/recetas/{id}  (para anularla se envía "estado": "Cancelada")
    public function actualizarReceta(Request $request, int $id): JsonResponse
    {
        $receta = $this->recetaPropia($id);

        if (!$receta) {
            return $this->recetaNoEncontrada();
        }

        $medico = $this->medicoActual();
        $datos = $this->validarReceta($request, soloProductosActivos: false, conMedico: false);

        $this->verificarPaciente($medico, (int) $datos['paciente_id'], $receta->paciente_id);

        $datos['medico_id'] = $medico->id_medico;

        DB::transaction(function () use ($receta, $datos) {
            $receta->update($this->datosEncabezado($datos));
            $this->guardarDetalles($receta, $datos);
        });

        return response()->json([
            'mensaje' => 'Receta actualizada correctamente.',
            'data'    => $this->recetaPropia($receta->id_receta)->append('total_estimado'),
        ]);
    }

    // ------------------------------------------------------------------
    // Auxiliares
    // ------------------------------------------------------------------

    // Perfil médico del usuario dueño del token
    private function medicoActual(): Medico
    {
        $medico = Medico::where('id_usuario', request()->user()->id_usuario)->first();

        abort_if(!$medico, 403, 'Esta cuenta ya no tiene un perfil médico habilitado.');

        return $medico;
    }

    // Cita que pertenece al médico (si no es suya, null)
    private function citaDelMedico(int $id): ?Cita
    {
        return Cita::with(['paciente', 'expediente'])
            ->where('medico_id', $this->medicoActual()->id_medico)
            ->find($id);
    }

    // Receta emitida por el médico (si no es suya, null)
    private function recetaPropia(int $id): ?Receta
    {
        return Receta::with(['medico.usuario', 'medico.especialidad', 'paciente', 'detalles.producto'])
            ->where('medico_id', $this->medicoActual()->id_medico)
            ->find($id);
    }

    // Pacientes que han tenido al menos una cita con el médico (+ el de la receta al editar)
    private function pacientesDelMedico(Medico $medico, ?int $incluirId = null)
    {
        return Usuario::where(function ($q) use ($medico, $incluirId) {
            $q->whereHas('citas', fn ($c) => $c->where('medico_id', $medico->id_medico));
            if ($incluirId) {
                $q->orWhere('id_usuario', $incluirId);
            }
        })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();
    }

    private function verificarPaciente(Medico $medico, int $pacienteId, ?int $incluirId = null): void
    {
        if (!$this->pacientesDelMedico($medico, $incluirId)->contains('id_usuario', $pacienteId)) {
            throw ValidationException::withMessages([
                'paciente_id' => 'Solo puedes emitir recetas a pacientes que tengan citas contigo.',
            ]);
        }
    }

    private function citaNoEncontrada(): JsonResponse
    {
        return response()->json(['mensaje' => 'La cita solicitada no existe o no es tuya.'], 404);
    }

    private function recetaNoEncontrada(): JsonResponse
    {
        return response()->json(['mensaje' => 'La receta solicitada no existe o no la emitiste tú.'], 404);
    }
}
