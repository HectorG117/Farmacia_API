<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Médicos (responde JSON para la API).
 */
class MedicoController extends Controller
{
    // GET /api/medicos?buscar=&especialidad_id=&estado=activos|inactivos
    public function index(Request $request): JsonResponse
    {
        $buscar = trim((string) $request->buscar);

        $medicos = Medico::with(['usuario', 'especialidad'])
            ->withCount([
                'citas as citas_proximas_count' => fn ($q) => $q->activas()
                    ->where('estado', '!=', 'Completada')
                    ->whereDate('fecha', '>=', today()),
                'recetas',
            ])
            ->when($request->filled('especialidad_id'), fn ($q) => $q->where('especialidad_id', $request->especialidad_id))
            ->when($request->estado === 'activos', fn ($q) => $q->activos())
            ->when($request->estado === 'inactivos', fn ($q) => $q->whereHas('usuario', fn ($u) => $u->where('activo', false)))
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('cedula', 'like', "%{$buscar}%")
                        ->orWhere('consultorio', 'like', "%{$buscar}%")
                        ->orWhereHas('usuario', fn ($u) => $u
                            ->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('apellido', 'like', "%{$buscar}%")
                            ->orWhere('email', 'like', "%{$buscar}%"));
                });
            })
            ->get()
            ->sortBy(fn ($m) => $m->usuario->nombre ?? '')
            ->values();

        return response()->json(['data' => $medicos]);
    }

    // POST /api/medicos
    // tipo_usuario = "nuevo" (crea la cuenta) o "existente" (vincula id_usuario)
    public function store(Request $request): JsonResponse
    {
        $usuarioNuevo = $request->input('tipo_usuario', 'nuevo') === 'nuevo';

        $reglas = $this->reglasProfesionales() + ['tipo_usuario' => 'required|in:nuevo,existente'];

        $reglas += $usuarioNuevo
            ? $this->reglasUsuario() + ['password' => 'required|string|min:8|confirmed']
            : ['id_usuario' => [
                'required', 'integer',
                Rule::exists('usuarios', 'id_usuario'),
                Rule::unique('medicos', 'id_usuario'),
            ]];

        $datos = $request->validate($reglas, $this->mensajes(), $this->atributos());

        $medico = DB::transaction(function () use ($datos, $usuarioNuevo) {
            if ($usuarioNuevo) {
                $usuario = Usuario::create([
                    'nombre'   => $datos['nombre'],
                    'apellido' => $datos['apellido'],
                    'email'    => $datos['email'],
                    'telefono' => $datos['telefono'] ?? null,
                    'password' => Hash::make($datos['password']),
                    'activo'   => (bool) ($datos['activo'] ?? true),
                ]);
            } else {
                $usuario = Usuario::find($datos['id_usuario']);
            }

            $this->asignarRolMedico($usuario);

            return Medico::create([
                'id_usuario'      => $usuario->id_usuario,
                'cedula'          => $datos['cedula'],
                'especialidad_id' => $datos['especialidad_id'],
                'consultorio'     => $datos['consultorio'],
            ]);
        });

        return response()->json([
            'mensaje' => '¡Médico registrado correctamente!',
            'data'    => $medico->load(['usuario', 'especialidad']),
        ], 201);
    }

    // GET /api/medicos/{medico}  (perfil con estadísticas, próximas citas y últimas recetas)
    public function show(int $medico): JsonResponse
    {
        $registro = Medico::with(['usuario', 'especialidad'])->find($medico);

        if (!$registro) {
            return $this->noEncontrado();
        }

        $conteoCitas = $registro->citas()
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $estadisticas = [
            'citas_totales' => $conteoCitas->sum(),
            'pendientes'    => ($conteoCitas['Pendiente'] ?? 0) + ($conteoCitas['Confirmada'] ?? 0),
            'completadas'   => $conteoCitas['Completada'] ?? 0,
            'recetas'       => $registro->recetas()->count(),
        ];

        $proximasCitas = $registro->citas()
            ->with('paciente')
            ->whereIn('estado', ['Pendiente', 'Confirmada'])
            ->whereDate('fecha', '>=', today())
            ->orderBy('fecha')
            ->orderBy('hora')
            ->limit(5)
            ->get();

        $ultimasRecetas = $registro->recetas()
            ->with('paciente')
            ->withCount('detalles')
            ->latest('fecha_emision')
            ->limit(5)
            ->get();

        return response()->json([
            'data'            => $registro,
            'estadisticas'    => $estadisticas,
            'proximas_citas'  => $proximasCitas,
            'ultimas_recetas' => $ultimasRecetas,
        ]);
    }

    // PUT/PATCH /api/medicos/{medico}  (datos profesionales + cuenta de usuario)
    public function update(Request $request, int $medico): JsonResponse
    {
        $registro = Medico::with('usuario')->find($medico);

        if (!$registro) {
            return $this->noEncontrado();
        }

        $reglas = $this->reglasProfesionales($registro)
            + $this->reglasUsuario($registro->id_usuario)
            + ['password' => 'nullable|string|min:8|confirmed'];

        $datos = $request->validate($reglas, $this->mensajes(), $this->atributos());

        DB::transaction(function () use ($registro, $datos, $request) {
            $datosUsuario = [
                'nombre'   => $datos['nombre'],
                'apellido' => $datos['apellido'],
                'email'    => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
                // En la API, si no se envía "activo" se conserva el valor actual
                'activo'   => $request->has('activo') ? $request->boolean('activo') : $registro->usuario->activo,
            ];

            if (!empty($datos['password'])) {
                $datosUsuario['password'] = Hash::make($datos['password']);
            }

            $registro->usuario->update($datosUsuario);

            $registro->update([
                'cedula'          => $datos['cedula'],
                'especialidad_id' => $datos['especialidad_id'],
                'consultorio'     => $datos['consultorio'],
            ]);
        });

        return response()->json([
            'mensaje' => '¡Datos del médico actualizados correctamente!',
            'data'    => $registro->fresh(['usuario', 'especialidad']),
        ]);
    }

    // DELETE /api/medicos/{medico}  (la cuenta de usuario se conserva)
    public function destroy(int $medico): JsonResponse
    {
        $registro = Medico::with('usuario')->find($medico);

        if (!$registro) {
            return $this->noEncontrado();
        }

        if ($registro->tieneHistorial()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar a un médico que ya tiene citas o recetas registradas. En su lugar, márcalo como inactivo.',
            ], 409);
        }

        $nombre = $registro->nombre_completo;

        DB::transaction(function () use ($registro) {
            if ($rol = $this->rolMedico()) {
                $registro->usuario?->roles()->detach($rol->id_rol);
            }
            $registro->delete();
        });

        return response()->json([
            'mensaje' => "Se eliminó el perfil de médico de {$nombre}. Su cuenta de usuario se conserva.",
        ]);
    }

    // ------------------------------------------------------------------

    private function noEncontrado(): JsonResponse
    {
        return response()->json(['mensaje' => 'El médico solicitado no existe.'], 404);
    }

    private function rolMedico(): ?Rol
    {
        return Rol::whereIn('nombre', ['Medico', 'Médico', 'medico', 'médico'])->first();
    }

    private function asignarRolMedico(Usuario $usuario): void
    {
        if ($rol = $this->rolMedico()) {
            $usuario->roles()->syncWithoutDetaching([$rol->id_rol]);
        }
    }

    private function reglasProfesionales(?Medico $medico = null): array
    {
        return [
            'cedula'          => [
                'required', 'string', 'min:5', 'max:30', 'regex:/^[A-Za-z0-9\-]+$/',
                Rule::unique('medicos', 'cedula')->ignore($medico?->id_medico, 'id_medico'),
            ],
            'especialidad_id' => 'required|integer|exists:especialidades,id_especialidad',
            'consultorio'     => 'required|string|max:100',
        ];
    }

    private function reglasUsuario(?int $idUsuario = null): array
    {
        return [
            'nombre'   => 'required|string|min:2|max:100',
            'apellido' => 'required|string|min:2|max:100',
            'email'    => [
                'required', 'email', 'max:150',
                Rule::unique('usuarios', 'email')->ignore($idUsuario, 'id_usuario'),
            ],
            'telefono' => 'nullable|string|max:20|regex:/^[0-9\s\+\-()]+$/',
            'activo'   => 'nullable|boolean',
        ];
    }

    private function mensajes(): array
    {
        return [
            'cedula.unique'      => 'Ya existe un médico registrado con esa cédula profesional.',
            'cedula.regex'       => 'La cédula solo puede contener letras, números y guiones.',
            'email.unique'       => 'Ese correo ya pertenece a otro usuario.',
            'id_usuario.unique'  => 'Ese usuario ya tiene un perfil de médico.',
            'telefono.regex'     => 'El teléfono solo puede contener números, espacios y los signos + - ( ).',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    private function atributos(): array
    {
        return [
            'cedula'          => 'cédula profesional',
            'especialidad_id' => 'especialidad',
            'id_usuario'      => 'usuario',
            'email'           => 'correo electrónico',
            'telefono'        => 'teléfono',
            'password'        => 'contraseña',
        ];
    }
}
