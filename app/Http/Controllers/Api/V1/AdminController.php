<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminResourceRequest;
use App\Http\Requests\AdminUserStatusRequest;
use App\Http\Resources\AdminProductoResource;
use App\Http\Resources\AdminUsuarioResource;
use App\Models\Administrador;
use App\Models\Producto;
use App\Models\Usuario;
use App\Support\RespuestaApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AdminController
{
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $admin = Administrador::where('nombre_usuario', $credentials['nombre_usuario'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return RespuestaApi::error('El nombre de usuario o la contraseña son incorrectos.', 401);
        }

        if (! $admin->activo) {
            return RespuestaApi::error('La cuenta de administrador está deshabilitada.', 403);
        }

        $token = $admin->createToken($credentials['dispositivo'] ?? 'admin-token', ['admin'])->plainTextToken;

        return RespuestaApi::exito('Inicio de sesión correcto.', [
            'rol' => 'admin',
            'token' => $token,
            'tipo_token' => 'Bearer',
            'administrador' => [
                'id' => $admin->id_administrador,
                'nombre' => $admin->nombre_completo,
                'nombre_usuario' => $admin->nombre_usuario,
                'nivel' => $admin->nivel,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return RespuestaApi::exito('La sesión se cerró correctamente.');
    }

    public function indexResources(): AnonymousResourceCollection
    {
        // El backoffice necesita consultar también los productos enviados a la papelera.
        $products = Producto::withTrashed()->latest()->paginate(15);

        return AdminProductoResource::collection($products)
            ->additional([
                'success' => true,
                'message' => 'Recursos consultados correctamente.',
                'errors' => null,
            ]);
    }

    public function showResource(int $id): AdminProductoResource
    {
        $product = Producto::withTrashed()->findOrFail($id);

        return (new AdminProductoResource($product))
            ->additional([
                'success' => true,
                'message' => 'Recurso consultado correctamente.',
                'errors' => null,
            ]);
    }

    public function storeResource(AdminResourceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $imagePath = isset($data['imagen']) ? $this->storeImage($data['imagen']) : null;

        try {
            $product = Producto::create([
                'nombre' => $data['nombre'],
                'precio' => $data['precio'],
                'stock' => $data['stock'],
                'categoria' => $data['categoria'],
                'imagen' => $imagePath,
            ]);
        } catch (Throwable $exception) {
            $this->deleteStoredImage($imagePath);
            throw $exception;
        }

        return (new AdminProductoResource($product->fresh()))
            ->additional([
                'success' => true,
                'message' => 'Recurso creado correctamente.',
                'errors' => null,
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function updateResource(AdminResourceRequest $request, int $id): JsonResponse
    {
        $product = Producto::withTrashed()->findOrFail($id);
        $data = $request->validated();
        $newImage = isset($data['imagen']) ? $this->storeImage($data['imagen']) : null;
        $oldImage = $product->imagen;

        try {
            $product->update([
                'nombre' => $data['nombre'] ?? $product->nombre,
                'precio' => $data['precio'] ?? $product->precio,
                'stock' => $data['stock'] ?? $product->stock,
                'categoria' => $data['categoria'] ?? $product->categoria,
                'imagen' => $newImage ?? $product->imagen,
            ]);
        } catch (Throwable $exception) {
            $this->deleteStoredImage($newImage);
            throw $exception;
        }

        if ($newImage && $oldImage && $oldImage !== $newImage) {
            $this->deleteImageWhenUnused($oldImage, $product->id);
        }

        return (new AdminProductoResource($product->fresh()))
            ->additional([
                'success' => true,
                'message' => 'Recurso actualizado correctamente.',
                'errors' => null,
            ])
            ->response();
    }

    public function destroyResource(int $id): JsonResponse
    {
        $product = Producto::findOrFail($id);
        $product->delete();

        return RespuestaApi::exito('Recurso eliminado correctamente.', ['id' => $product->id]);
    }

    public function indexUsers(): JsonResponse
    {
        $users = Usuario::with('roles')->orderBy('id_usuario')->get();
        $admins = Administrador::orderBy('id_administrador')->get();

        return RespuestaApi::exito('Usuarios consultados correctamente.', [
            'usuarios' => AdminUsuarioResource::collection($users)->resolve(),
            'administradores' => AdminUsuarioResource::collection($admins)->resolve(),
        ]);
    }

    public function updateUserStatus(AdminUserStatusRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $active = (bool) $data['activo'];

        if ($data['tipo'] === 'usuario') {
            $user = Usuario::findOrFail($id);
            $user->update(['activo' => $active]);

            return RespuestaApi::exito('Estado del usuario actualizado correctamente.', [
                'id' => $user->id_usuario,
                'tipo' => 'usuario',
                'activo' => $user->activo,
            ]);
        }

        $admin = Administrador::findOrFail($id);
        if (! $active && $admin->id_administrador === $request->user()->id_administrador && $admin->activo) {
            return RespuestaApi::error('No puedes desactivar tu propia cuenta administrativa.', 409);
        }

        if (! $active && $admin->activo && $admin->nivel === 3) {
            $activeOwners = Administrador::where('activo', true)->where('nivel', 3)->count();
            if ($activeOwners <= 1) {
                return RespuestaApi::error('No se puede desactivar al último administrador de nivel 3 activo.', 409);
            }
        }

        $admin->update(['activo' => $active]);

        return RespuestaApi::exito('Estado del administrador actualizado correctamente.', [
            'id' => $admin->id_administrador,
            'tipo' => 'administrador',
            'activo' => $admin->activo,
        ]);
    }

    private function storeImage(UploadedFile $image): string
    {
        $name = 'producto_'.Str::uuid().'.'.strtolower($image->extension() ?: 'jpg');
        $path = Storage::disk('public')->putFileAs('productos', $image, $name);

        if (! $path) {
            throw new RuntimeException('No se pudo guardar la imagen del producto.');
        }

        return $path;
    }

    private function deleteImageWhenUnused(string $path, int $productId): void
    {
        $inUse = Producto::withTrashed()
            ->where('imagen', $path)
            ->where('id', '!=', $productId)
            ->exists();

        if (! $inUse) {
            $this->deleteStoredImage($path);
        }
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'productos/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
