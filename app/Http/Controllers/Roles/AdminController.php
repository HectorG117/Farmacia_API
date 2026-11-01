<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Rol ADMIN (API, responde JSON).
 *
 * POST /api/admin/login     -> token Bearer con habilidad "admin"
 * POST /api/admin/logout    -> revoca el token actual
 * GET  /api/admin/perfil    -> datos del administrador autenticado
 * GET  /api/admin/dashboard -> resumen del panel (estadísticas, próximas citas, etc.)
 *
 * El CRUD de médicos, citas, expedientes y recetas sigue en sus controladores
 * (MedicoController, CitaController, ...) y se protege con los Gates por nivel.
 */
class AdminController extends Controller
{
    private const STOCK_BAJO = 10;

    public function login(Request $request): JsonResponse
    {
        $credenciales = $request->validate([
            'nombre_usuario' => ['required', 'string', 'max:150'],
            'password'       => ['required', 'string'],
            'dispositivo'    => ['nullable', 'string', 'max:100'], // nombre del token (ej. "Postman")
        ]);

        $administrador = Administrador::where('nombre_usuario', $credenciales['nombre_usuario'])->first();

        if (!$administrador || !Hash::check($credenciales['password'], $administrador->password)) {
            return response()->json([
                'mensaje' => 'El nombre de usuario o la contraseña son incorrectos.',
            ], 401);
        }

        if (!$administrador->activo) {
            return response()->json([
                'mensaje' => 'La cuenta de administrador está deshabilitada.',
            ], 403);
        }

        $token = $administrador->createToken($credenciales['dispositivo'] ?? 'api', ['admin'])->plainTextToken;

        return response()->json([
            'mensaje'       => 'Inicio de sesión correcto.',
            'rol'           => 'admin',
            'token'         => $token,
            'tipo_token'    => 'Bearer',
            'administrador' => $administrador,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'La sesión se cerró correctamente.']);
    }

    public function perfil(Request $request): JsonResponse
    {
        return response()->json(['rol' => 'admin', 'data' => $request->user()]);
    }

    // Mismos datos que el panel web (AdminDashboardController), en JSON
    public function dashboard(): JsonResponse
    {
        $estadisticas = [
            'pacientes_activos' => Usuario::where('activo', true)->whereDoesntHave('medico')->count(),
            'medicos_activos'   => Medico::activos()->count(),
            'citas_hoy'         => Cita::whereDate('fecha', today())->where('estado', '!=', 'Cancelada')->count(),
            'recetas_vigentes'  => Receta::where('estado', 'Vigente')->count(),
        ];

        $proximasCitas = Cita::with(['paciente', 'medico.usuario'])
            ->whereIn('estado', ['Pendiente', 'Confirmada'])
            ->whereDate('fecha', '>=', today())
            ->orderBy('fecha')
            ->orderBy('hora')
            ->limit(6)
            ->get();

        $ultimasRecetas = Receta::with(['paciente', 'medico.usuario', 'detalles.producto'])
            ->latest('fecha_emision')
            ->limit(5)
            ->get();

        $stockBajo = Producto::where('stock', '<', self::STOCK_BAJO)
            ->orderBy('stock')
            ->limit(6)
            ->get();

        return response()->json([
            'data' => [
                'estadisticas'    => $estadisticas,
                'proximas_citas'  => $proximasCitas,
                'ultimas_recetas' => $ultimasRecetas,
                'stock_bajo'      => $stockBajo,
                'limite_stock'    => self::STOCK_BAJO,
            ],
        ]);
    }
}
