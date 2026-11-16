<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Consulta la información del perfil del cliente autenticado.
     * Endpoint: GET /api/v1/client/perfil
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ], 200);
    }

    /**
     * Actualiza la información del perfil del propio cliente.
     * Endpoint: PUT/PATCH /api/v1/client/perfil
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'data'    => new UserResource($user),
        ], 200);
    }
}