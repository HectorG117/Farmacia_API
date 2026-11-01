<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Productos (responde JSON para la API).
 *
 * Públicas:  GET /api/productos, GET /api/productos/{id}
 * Admin:     POST, PUT, DELETE (papelera), restaurar y borrado definitivo.
 */
class ProductoController extends Controller
{
    /**
     * Tablas que podrían referenciar un producto mediante llave foránea.
     * Antes de un borrado físico se revisa cada una; si el producto está
     * en uso en alguna, se cancela la eliminación y se informa el motivo.
     *
     * Agrega aquí cualquier tabla futura que dependa de "productos"
     * (por ejemplo el detalle de una venta o de un carrito de compras):
     *   'nombre_tabla' => ['columna' => 'columna_fk', 'etiqueta' => 'texto para el usuario'],
     */
    private const RELACIONES_PRODUCTO = [
        'receta_detalle' => ['columna' => 'producto_id', 'etiqueta' => 'recetas médicas'],
        // 'carrito_detalle' => ['columna' => 'id_producto', 'etiqueta' => 'carritos de compra'],
    ];

    // Carpeta, dentro de /public, donde se guardan las imágenes de los productos.
    // OJO: no debe llamarse igual que ninguna ruta de la app (por ejemplo "productos"),
    // porque Apache sirve primero cualquier carpeta real que exista dentro de /public
    // y nunca llega a pasarle esa petición a Laravel.
    private const CARPETA_IMAGENES = 'uploads/productos';

    // GET /api/productos  -> productos activos
    public function index(): JsonResponse
    {
        return response()->json(['data' => Producto::latest()->get()]);
    }

    // GET /api/productos/papelera  -> productos eliminados mediante borrado lógico
    public function papelera(): JsonResponse
    {
        return response()->json(['data' => Producto::onlyTrashed()->latest('deleted_at')->get()]);
    }

    // GET /api/productos/{id}
    public function show(int $id): JsonResponse
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return $this->noEncontrado('El producto solicitado no existe o fue eliminado.');
        }

        return response()->json(['data' => $producto]);
    }

    // POST /api/productos  (multipart/form-data por la imagen)
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'    => 'required|string|min:3|max:100',
            'precio'    => 'required|numeric|min:0.01',
            'stock'     => 'required|integer|min:0',
            'categoria' => 'required|string',
            'imagen'    => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $producto = Producto::create([
            'nombre'    => $request->nombre,
            'precio'    => $request->precio,
            'stock'     => $request->stock,
            'categoria' => $request->categoria,
        ]);

        $this->saveImage($request, $producto);

        return response()->json([
            'mensaje' => '¡Producto registrado correctamente!',
            'data'    => $producto->fresh(),
        ], 201);
    }

    // PUT /api/productos/{id}  (para enviar una imagen nueva: POST con el campo _method=PUT)
    public function update(Request $request, int $id): JsonResponse
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return $this->noEncontrado('El producto que intentas actualizar no existe.');
        }

        $request->validate([
            'nombre'    => 'sometimes|required|string|min:3|max:100',
            'precio'    => 'sometimes|required|numeric|min:0.01',
            'stock'     => 'sometimes|required|integer|min:0',
            'categoria' => 'sometimes|required|string',
            'imagen'    => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $producto->update($request->only('nombre', 'precio', 'stock', 'categoria'));

        if ($request->hasFile('imagen')) {
            $this->deleteImageFile($producto->imagen);
            $this->saveImage($request, $producto);
        }

        return response()->json([
            'mensaje' => '¡Producto actualizado correctamente!',
            'data'    => $producto->fresh(),
        ]);
    }

    // DELETE /api/productos/{id}  -> borrado LÓGICO (solo marca "deleted_at")
    public function destroy(int $id): JsonResponse
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return $this->noEncontrado('El producto que intentas eliminar no existe.');
        }

        $producto->delete();

        return response()->json([
            'mensaje' => "El producto \"{$producto->nombre}\" se movió a la papelera. Puedes restaurarlo desde ahí.",
        ]);
    }

    // PUT /api/productos/{id}/restaurar
    public function restore(int $id): JsonResponse
    {
        $producto = Producto::onlyTrashed()->find($id);

        if (!$producto) {
            return $this->noEncontrado('El producto no existe o ya se encuentra activo.');
        }

        $producto->restore();

        return response()->json([
            'mensaje' => "El producto \"{$producto->nombre}\" fue restaurado y ya aparece en el catálogo.",
            'data'    => $producto,
        ]);
    }

    // DELETE /api/productos/{id}/definitivo  -> borrado FÍSICO (solo productos en la papelera)
    public function forceDestroy(int $id): JsonResponse
    {
        $producto = Producto::onlyTrashed()->find($id);

        if (!$producto) {
            return $this->noEncontrado('Solo se pueden eliminar de forma definitiva productos que ya estén en la papelera.');
        }

        $dependencias = $this->buscarDependenciasActivas($producto);

        if (!empty($dependencias)) {
            return response()->json([
                'mensaje' => "No se puede eliminar definitivamente \"{$producto->nombre}\" porque todavía está relacionado con: " . implode(', ', $dependencias) . '.',
            ], 409);
        }

        $this->deleteImageFile($producto->imagen);

        $nombre = $producto->nombre;
        $producto->forceDelete();

        return response()->json([
            'mensaje' => "El producto \"{$nombre}\" y su imagen fueron eliminados de forma definitiva.",
        ]);
    }

    // ------------------------------------------------------------------

    private function noEncontrado(string $mensaje): JsonResponse
    {
        return response()->json(['mensaje' => $mensaje], 404);
    }

    // Revisa, tabla por tabla, si el producto sigue siendo usado en alguna relación
    private function buscarDependenciasActivas(Producto $producto): array
    {
        $encontradas = [];

        foreach (self::RELACIONES_PRODUCTO as $tabla => $info) {
            if (!Schema::hasTable($tabla) || !Schema::hasColumn($tabla, $info['columna'])) {
                continue;
            }

            $enUso = DB::table($tabla)->where($info['columna'], $producto->id)->exists();

            if ($enUso) {
                $encontradas[] = $info['etiqueta'];
            }
        }

        return $encontradas;
    }

    /**
     * Guarda la imagen subida directamente en public/uploads/productos, para que
     * quede dentro de los archivos del proyecto y sea accesible sin configuración
     * adicional (sin depender del enlace simbólico storage:link).
     */
    private function saveImage(Request $request, Producto $producto): void
    {
        if (!$request->hasFile('imagen')) {
            return;
        }

        $extension = $request->file('imagen')->extension();
        $filename = "Producto_{$producto->id}_1.{$extension}";

        // Mueve el archivo subido a /public/uploads/productos/{filename}
        $request->file('imagen')->move(public_path(self::CARPETA_IMAGENES), $filename);

        $producto->update(['imagen' => self::CARPETA_IMAGENES . "/{$filename}"]);
    }

    // Elimina físicamente el archivo de imagen (si existe) de public/uploads/productos
    private function deleteImageFile(?string $rutaRelativa): void
    {
        if (!$rutaRelativa) {
            return;
        }

        $rutaCompleta = public_path($rutaRelativa);

        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
    }
}
