<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Autenticación de administradores para la API (tokens de Sanctum).
 *
 * POST /api/login   -> devuelve un token Bearer
 * POST /api/logout  -> revoca el token actual
 * GET  /api/me      -> datos del administrador autenticado
 */
class AuthController extends Controller
{
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

        $token = $administrador->createToken($credenciales['dispositivo'] ?? 'api')->plainTextToken;

        return response()->json([
            'mensaje'       => 'Inicio de sesión correcto.',
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

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }
}
