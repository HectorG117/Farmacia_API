<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Productos — API pública v1 (actividad 4.2). Recurso con imagen.
 *
 *  GET    /api/v1/productos               Consultar todos (?buscar=&categoria=)
 *  GET    /api/v1/productos/{id}          Consultar uno (incluye imagen_url)
 *  POST   /api/v1/productos               Registrar (JSON, o multipart/form-data con "imagen")
 *  PUT    /api/v1/productos/{id}          Reemplazar todos los datos
 *  PATCH  /api/v1/productos/{id}          Actualizar solo los campos enviados
 *  DELETE /api/v1/productos/{id}          Eliminar (borrado lógico / papelera)
 *  POST   /api/v1/productos/{id}/imagen   Asociar o sustituir la imagen (multipart/form-data)
 *  DELETE /api/v1/productos/{id}/imagen   Retirar la imagen asociada
 *
 * Imágenes: se guardan en el disco "public" (storage/app/public/productos) y se
 * consultan por la URL pública /storage/productos/... (php artisan storage:link).
 *   - "imagen"     -> ruta guardada en la base de datos   (productos/producto_9f1c....jpg)
 *   - "imagen_url" -> URL pública para mostrarla           (http://localhost/storage/productos/...)
 */
class ProductoController extends ApiController
{
    private const DISCO = 'public';
    private const CARPETA = 'productos';
    private const CAMPOS = ['nombre', 'precio', 'stock', 'categoria'];

    // Reglas de la imagen: formato permitido, tamaño máximo (KB) y dimensiones
    private const REGLA_IMAGEN = 'image|mimes:jpeg,jpg,png,webp|max:2048|dimensions:max_width=4000,max_height=4000';

    private const ATRIBUTOS = [
        'nombre'    => 'nombre',
        'precio'    => 'precio',
        'stock'     => 'stock',
        'categoria' => 'categoría',
        'imagen'    => 'imagen',
        'buscar'    => 'buscar',
    ];

    // GET /api/v1/productos
    public function index(Request $request): JsonResponse
    {
        $validator = $this->validador($request->query(), [
            'buscar'    => 'nullable|string|max:100',
            'categoria' => 'nullable|string|max:50',
        ], self::ATRIBUTOS);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $productos = Producto::query()
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%' . $request->query('buscar') . '%'))
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->query('categoria')))
            ->latest()
            ->get()
            ->map(fn (Producto $p) => $this->presentar($p));

        return $this->exito('Productos consultados correctamente', $productos);
    }

    // GET /api/v1/productos/{id}
    public function show(string $id): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $producto = Producto::find($id);

        if (!$producto) {
            return $this->noEncontrado('El producto solicitado no existe');
        }

        return $this->exito('Producto consultado correctamente', $this->presentar($producto));
    }

    // POST /api/v1/productos
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validador($request->all(), $this->reglas('completa'), self::ATRIBUTOS);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $datos = $validator->validated();

        // Primero se guarda el archivo; si el registro falla, se borra para no dejar basura
        $ruta = $request->hasFile('imagen') ? $this->guardarImagen($request->file('imagen')) : null;

        try {
            $producto = Producto::create([
                'nombre'    => $datos['nombre'],
                'precio'    => $datos['precio'],
                'stock'     => $datos['stock'],
                'categoria' => $datos['categoria'],
                'imagen'    => $ruta,
            ]);
        } catch (Throwable $e) {
            $this->eliminarArchivo($ruta);
            throw $e;
        }

        return $this->exito('Producto registrado correctamente', $this->presentar($producto->fresh()), 201);
    }

    // PUT /api/v1/productos/{id}  -> se deben enviar TODOS los campos
    public function update(Request $request, string $id): JsonResponse
    {
        return $this->actualizar($request, $id, 'completa');
    }

    // PATCH /api/v1/productos/{id}  -> los campos que no se envían conservan su valor
    public function patch(Request $request, string $id): JsonResponse
    {
        return $this->actualizar($request, $id, 'parcial');
    }

    // DELETE /api/v1/productos/{id}
    public function destroy(string $id): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $producto = Producto::find($id);

        if (!$producto) {
            return $this->noEncontrado('El producto que intentas eliminar no existe');
        }

        // Borrado lógico (SoftDeletes): la imagen se conserva por si el producto se restaura
        $producto->delete();

        return $this->exito('Producto eliminado correctamente', ['id' => $producto->id]);
    }

    // POST /api/v1/productos/{id}/imagen  (multipart/form-data, campo "imagen")
    public function subirImagen(Request $request, string $id): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $producto = Producto::find($id);

        if (!$producto) {
            return $this->noEncontrado('El producto al que intentas asignar la imagen no existe');
        }

        if (!str_contains(strtolower((string) $request->header('Content-Type')), 'multipart/form-data')) {
            return $this->error(
                'El tipo de contenido enviado no es admitido',
                415,
                ['content_type' => ['La imagen debe enviarse como multipart/form-data en el campo "imagen".']]
            );
        }

        $validator = $this->validador($request->all(), [
            'imagen' => 'required|' . self::REGLA_IMAGEN,
        ], self::ATRIBUTOS);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $this->reemplazarImagen($producto, $request->file('imagen'));

        return $this->exito('Imagen del producto actualizada correctamente', $this->presentar($producto->fresh()));
    }

    // DELETE /api/v1/productos/{id}/imagen
    public function eliminarImagen(string $id): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $producto = Producto::find($id);

        if (!$producto) {
            return $this->noEncontrado('El producto solicitado no existe');
        }

        if (!$producto->imagen) {
            return $this->noEncontrado('El producto no tiene una imagen asociada');
        }

        $anterior = $producto->imagen;
        $producto->update(['imagen' => null]);
        $this->eliminarSiNoSeUsa($anterior, $producto->id);

        return $this->exito('Imagen del producto eliminada correctamente', $this->presentar($producto->fresh()));
    }

    // ------------------------------------------------------------------

    private function actualizar(Request $request, string $id, string $modo): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $producto = Producto::find($id);

        if (!$producto) {
            return $this->noEncontrado('El producto que intentas actualizar no existe');
        }

        if ($modo === 'parcial' && !$request->hasAny([...self::CAMPOS, 'imagen']) && !$request->hasFile('imagen')) {
            return $this->sinCamposParaActualizar([...self::CAMPOS, 'imagen']);
        }

        $validator = $this->validador($request->all(), $this->reglas($modo), self::ATRIBUTOS);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $datos = collect($validator->validated())->only(self::CAMPOS)->all();

        if (!empty($datos)) {
            $producto->update($datos);
        }

        // La imagen solo se puede mandar aquí usando multipart (POST + _method=PUT/PATCH)
        if ($request->hasFile('imagen')) {
            $this->reemplazarImagen($producto, $request->file('imagen'));
        }

        return $this->exito('Producto actualizado correctamente', $this->presentar($producto->fresh()));
    }

    /**
     * completa -> POST y PUT: todos los campos son obligatorios.
     * parcial  -> PATCH: solo se validan los campos que vienen en la solicitud.
     */
    private function reglas(string $modo): array
    {
        $obligatorio = $modo === 'parcial' ? 'sometimes|required' : 'required';

        return [
            'nombre'    => "{$obligatorio}|string|min:3|max:100",
            'precio'    => "{$obligatorio}|numeric|decimal:0,2|min:0.01|max:999999.99",
            'stock'     => "{$obligatorio}|integer|min:0|max:1000000",
            'categoria' => "{$obligatorio}|string|min:3|max:50",
            'imagen'    => 'nullable|' . self::REGLA_IMAGEN,
        ];
    }

    private function presentar(Producto $producto): array
    {
        return [
            'id'         => $producto->id,
            'nombre'     => $producto->nombre,
            'precio'     => (float) $producto->precio,
            'stock'      => (int) $producto->stock,
            'categoria'  => $producto->categoria,
            'imagen'     => $producto->imagen,                    // ruta guardada en la BD
            'imagen_url' => $this->urlImagen($producto->imagen),  // URL pública
            'created_at' => $producto->created_at?->toIso8601String(),
            'updated_at' => $producto->updated_at?->toIso8601String(),
        ];
    }

    // ---------------------------- Imágenes ----------------------------

    // Nombre seguro: no se usa el nombre original del cliente, solo un UUID + extensión real del archivo
    private function guardarImagen(UploadedFile $archivo): string
    {
        $extension = strtolower($archivo->extension() ?: 'jpg');
        $nombre = 'producto_' . Str::uuid() . '.' . $extension;

        $ruta = Storage::disk(self::DISCO)->putFileAs(self::CARPETA, $archivo, $nombre);

        if (!$ruta) {
            throw new RuntimeException('No se pudo guardar la imagen del producto en el disco.');
        }

        return $ruta;
    }

    private function reemplazarImagen(Producto $producto, UploadedFile $archivo): void
    {
        $anterior = $producto->imagen;
        $nueva = $this->guardarImagen($archivo);

        try {
            $producto->update(['imagen' => $nueva]);
        } catch (Throwable $e) {
            $this->eliminarArchivo($nueva);
            throw $e;
        }

        $this->eliminarSiNoSeUsa($anterior, $producto->id);
    }

    // Solo se borra el archivo anterior si ningún otro producto (incluidos los de la papelera) lo usa
    private function eliminarSiNoSeUsa(?string $ruta, int $idProducto): void
    {
        if (!$ruta) {
            return;
        }

        $enUso = Producto::withTrashed()
            ->where('imagen', $ruta)
            ->where('id', '!=', $idProducto)
            ->exists();

        if (!$enUso) {
            $this->eliminarArchivo($ruta);
        }
    }

    private function eliminarArchivo(?string $ruta): void
    {
        if (!$ruta) {
            return;
        }

        $disco = Storage::disk(self::DISCO);

        if ($disco->exists($ruta)) {
            $disco->delete($ruta);
            return;
        }

        // Imágenes subidas con la versión anterior del proyecto (public/uploads/productos/...).
        // Las de public/pictures son del sitio y nunca se borran.
        if (str_starts_with($ruta, 'uploads/') && !str_contains($ruta, '..') && is_file(public_path($ruta))) {
            @unlink(public_path($ruta));
        }
    }

    private function urlImagen(?string $ruta): ?string
    {
        if (!$ruta) {
            return null;
        }

        if (Str::startsWith($ruta, ['http://', 'https://'])) {
            return $ruta;
        }

        if (Storage::disk(self::DISCO)->exists($ruta)) {
            return Storage::disk(self::DISCO)->url($ruta);
        }

        // Rutas antiguas guardadas dentro de /public (uploads/productos/..., pictures/...)
        return asset($ruta);
    }
}
