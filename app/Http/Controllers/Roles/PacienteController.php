<?php

namespace App\Http\Controllers\Roles;

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
 * Rol PACIENTE / CLIENTE (API, responde JSON). Misma lógica que el portal del
 * paciente web (ClienteAuthController y ClientePortalController):
 *  - Solo ve su información: sus citas, sus expedientes médicos y sus recetas.
 *  - En sus recetas vigentes elige qué productos comprar y en qué cantidad.
 *  - No puede eliminar recetas ni cambiar lo que indicó el médico.
 */
class PacienteController extends Controller
{
    // ------------------------------------------------------------------
    // Autenticación
    // ------------------------------------------------------------------

    // POST /api/paciente/registro
    public function registro(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre'      => ['required', 'string', 'min:2', 'max:100'],
            'apellido'    => ['required', 'string', 'min:2', 'max:100'],
            'email'       => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')],
            'telefono'    => ['nullable', 'string', 'max:20', 'regex:/^[0-9\s\+\-()]+$/'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'dispositivo' => ['nullable', 'string', 'max:100'],
        ], [
            'email.unique'       => 'Ya existe una cuenta con ese correo. Inicia sesión.',
            'telefono.regex'     => 'El teléfono solo puede contener números, espacios y los signos + - ( ).',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ], [
            'email'    => 'correo electrónico',
            'telefono' => 'teléfono',
            'password' => 'contraseña',
        ]);

        $usuario = Usuario::create([
            'nombre'         => $datos['nombre'],
            'apellido'       => $datos['apellido'],
            'email'          => $datos['email'],
            'telefono'       => $datos['telefono'] ?? null,
            'password'       => Hash::make($datos['password']),
            'activo'         => true,
            'fecha_registro' => now(),
        ]);

        $token = $usuario->createToken($datos['dispositivo'] ?? 'api', ['paciente'])->plainTextToken;

        return response()->json([
            'mensaje'    => '¡Tu cuenta fue creada!',
            'rol'        => 'paciente',
            'token'      => $token,
            'tipo_token' => 'Bearer',
            'usuario'    => $usuario,
        ], 201);
    }

    // POST /api/paciente/login
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
            return response()->json(['mensaje' => 'La cuenta está deshabilitada. Comunícate con la farmacia.'], 403);
        }

        if (Medico::where('id_usuario', $usuario->id_usuario)->exists()) {
            return response()->json([
                'mensaje' => 'Esta cuenta pertenece a un médico. Inicia sesión en /api/medico/login.',
            ], 403);
        }

        $token = $usuario->createToken($credenciales['dispositivo'] ?? 'api', ['paciente'])->plainTextToken;

        return response()->json([
            'mensaje'    => 'Bienvenido(a), ' . $usuario->nombre . '.',
            'rol'        => 'paciente',
            'token'      => $token,
            'tipo_token' => 'Bearer',
            'usuario'    => $usuario,
        ]);
    }

    // POST /api/paciente/logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'La sesión se cerró correctamente.']);
    }

    // GET /api/paciente/perfil
    public function perfil(Request $request): JsonResponse
    {
        return response()->json(['rol' => 'paciente', 'data' => $request->user()]);
    }

    // GET /api/paciente/resumen  -> lo mismo que el inicio del portal web
    public function resumen(Request $request): JsonResponse
    {
        $id = $request->user()->id_usuario;

        $proximasCitas = Cita::with(['medico.usuario', 'medico.especialidad'])
            ->where('paciente_id', $id)
            ->whereIn('estado', ['Pendiente', 'Confirmada'])
            ->whereDate('fecha', '>=', today())
            ->orderBy('fecha')
            ->orderBy('hora')
            ->limit(5)
            ->get();

        $recetasVigentes = Receta::with(['medico.usuario', 'detalles.producto'])
            ->where('paciente_id', $id)
            ->where('estado', 'Vigente')
            ->latest('fecha_emision')
            ->get();

        $ultimosExpedientes = ExpedienteMedico::with('cita.medico.usuario')
            ->whereHas('cita', fn ($q) => $q->where('paciente_id', $id))
            ->latest('fecha')
            ->limit(3)
            ->get();

        return response()->json([
            'data' => [
                'totales' => [
                    'citas'       => Cita::where('paciente_id', $id)->count(),
                    'expedientes' => ExpedienteMedico::whereHas('cita', fn ($q) => $q->where('paciente_id', $id))->count(),
                    'recetas'     => Receta::where('paciente_id', $id)->count(),
                ],
                'proximas_citas'      => $proximasCitas,
                'recetas_vigentes'    => $recetasVigentes,
                'ultimos_expedientes' => $ultimosExpedientes,
            ],
        ]);
    }

    // ------------------------------------------------------------------
    // Citas
    // ------------------------------------------------------------------

    // GET /api/paciente/citas
    public function citas(Request $request): JsonResponse
    {
        $citas = Cita::with(['medico.usuario', 'medico.especialidad', 'expediente'])
            ->where('paciente_id', $request->user()->id_usuario)
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        return response()->json(['data' => $citas]);
    }

    // GET /api/paciente/citas/{id}
    public function cita(Request $request, int $id): JsonResponse
    {
        $cita = Cita::with(['medico.usuario', 'medico.especialidad', 'expediente'])
            ->where('paciente_id', $request->user()->id_usuario)
            ->find($id);

        return $cita
            ? response()->json(['data' => $cita])
            : response()->json(['mensaje' => 'La cita solicitada no existe o no es tuya.'], 404);
    }

    // ------------------------------------------------------------------
    // Expedientes médicos
    // ------------------------------------------------------------------

    // GET /api/paciente/expedientes
    public function expedientes(Request $request): JsonResponse
    {
        $expedientes = ExpedienteMedico::with(['cita.medico.usuario', 'cita.medico.especialidad'])
            ->whereHas('cita', fn ($q) => $q->where('paciente_id', $request->user()->id_usuario))
            ->latest('fecha')
            ->get();

        return response()->json(['data' => $expedientes]);
    }

    // GET /api/paciente/expedientes/{id}
    public function expediente(Request $request, int $id): JsonResponse
    {
        $expediente = ExpedienteMedico::with(['cita.medico.usuario', 'cita.medico.especialidad'])
            ->whereHas('cita', fn ($q) => $q->where('paciente_id', $request->user()->id_usuario))
            ->find($id);

        return $expediente
            ? response()->json(['data' => $expediente])
            : response()->json(['mensaje' => 'El expediente solicitado no existe o no es tuyo.'], 404);
    }

    // ------------------------------------------------------------------
    // Recetas
    // ------------------------------------------------------------------

    // GET /api/paciente/recetas
    public function recetas(Request $request): JsonResponse
    {
        $recetas = Receta::with(['medico.usuario', 'detalles.producto'])
            ->where('paciente_id', $request->user()->id_usuario)
            ->latest('fecha_emision')
            ->get()
            ->each->append(['total_compra', 'productos_seleccionados']);

        return response()->json(['data' => $recetas]);
    }

    // GET /api/paciente/recetas/{id}
    public function receta(Request $request, int $id): JsonResponse
    {
        $receta = $this->recetaPropia($request, $id);

        return $receta
            ? response()->json(['data' => $this->conTotales($receta)])
            : $this->recetaNoEncontrada();
    }

    /**
     * PUT /api/paciente/recetas/{id}/compra
     * Solo se guardan "comprar" y "cantidad_compra"; lo indicado por el médico no cambia.
     * Cuerpo: { "detalles": { "<id_detalle>": { "comprar": true, "cantidad_compra": 2 }, ... } }
     * Los productos que no se envíen quedan como "no comprar".
     */
    public function actualizarCompra(Request $request, int $id): JsonResponse
    {
        $receta = $this->recetaPropia($request, $id);

        if (!$receta) {
            return $this->recetaNoEncontrada();
        }

        if (!$receta->esEditablePorPaciente()) {
            return response()->json([
                'mensaje' => "Esta receta está \"{$receta->estado}\" y ya no se puede modificar.",
            ], 409);
        }

        $entrada = $request->input('detalles', []);
        $entrada = is_array($entrada) ? $entrada : [];
        $errores = [];
        $cambios = [];

        foreach ($receta->detalles as $detalle) {
            $fila = $entrada[$detalle->id_detalle] ?? [];
            $comprar = filter_var($fila['comprar'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $nombre = $detalle->producto->nombre ?? 'Producto';

            if (!$comprar) {
                $cambios[$detalle->id_detalle] = ['comprar' => false, 'cantidad_compra' => 0];
                continue;
            }

            $maximo = $detalle->maximo_compra;
            $cantidad = filter_var($fila['cantidad_compra'] ?? null, FILTER_VALIDATE_INT);

            if ($maximo < 1) {
                $errores["detalles.{$detalle->id_detalle}.comprar"] = "\"{$nombre}\" no está disponible en este momento.";
            } elseif ($cantidad === false || $cantidad < 1) {
                $errores["detalles.{$detalle->id_detalle}.cantidad_compra"] = "Indica una cantidad válida para \"{$nombre}\".";
            } elseif ($cantidad > $maximo) {
                $errores["detalles.{$detalle->id_detalle}.cantidad_compra"] = $maximo < $detalle->cantidad
                    ? "Solo hay {$maximo} unidad(es) de \"{$nombre}\" en existencia."
                    : "No puedes comprar más de lo recetado de \"{$nombre}\" ({$maximo}).";
            } else {
                $cambios[$detalle->id_detalle] = ['comprar' => true, 'cantidad_compra' => $cantidad];
            }
        }

        if ($errores) {
            throw ValidationException::withMessages($errores);
        }

        DB::transaction(function () use ($receta, $cambios) {
            foreach ($receta->detalles as $detalle) {
                $detalle->update($cambios[$detalle->id_detalle]);
            }
        });

        $receta->load('detalles.producto');

        return response()->json([
            'mensaje' => $receta->productos_seleccionados > 0
                ? 'Selección guardada. Total a comprar: $' . number_format($receta->total_compra, 2)
                : 'Selección guardada. No elegiste productos para comprar.',
            'data'    => $this->conTotales($receta),
        ]);
    }

    // ------------------------------------------------------------------
    // Auxiliares
    // ------------------------------------------------------------------

    private function recetaPropia(Request $request, int $id): ?Receta
    {
        return Receta::with(['medico.usuario', 'medico.especialidad', 'paciente', 'detalles.producto'])
            ->where('paciente_id', $request->user()->id_usuario)
            ->find($id);
    }

    // Agrega a cada producto el máximo que puede comprar y su subtotal, y a la receta sus totales
    private function conTotales(Receta $receta): Receta
    {
        $receta->detalles->each->append(['maximo_compra', 'subtotal_compra']);

        return $receta->append(['total_estimado', 'total_compra', 'productos_seleccionados']);
    }

    private function recetaNoEncontrada(): JsonResponse
    {
        return response()->json(['mensaje' => 'La receta solicitada no existe o no es tuya.'], 404);
    }
}
