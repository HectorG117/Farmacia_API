<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    // Mostrar listado de productos activos
    public function index()
    {
        $productos = Producto::latest()->get();

        return view('productos.index', compact('productos'));
    }

    // Mostrar el listado de productos eliminados mediante borrado lógico
    public function papelera()
    {
        $productos = Producto::onlyTrashed()->latest('deleted_at')->get();

        return view('productos.papelera', compact('productos'));
    }

    // Mostrar el formulario de creación
    public function create()
    {
        return view('productos.create');
    }

    // Mostrar toda la información de un único producto, sin permitir modificarla
    public function show(int $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return redirect()->route('productos.index')
                ->with('error', 'El producto solicitado no existe o fue eliminado.');
        }

        return view('productos.show', compact('producto'));
    }

    // Mostrar el formulario de edición con los datos existentes
    public function edit(int $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return redirect()->route('productos.index')
                ->with('error', 'El producto solicitado no existe.');
        }

        return view('productos.edit', compact('producto'));
    }

    // Guardar el nuevo producto con validaciones e imagen
    public function store(Request $request)
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

        return redirect()->route('productos.index')
            ->with('exito', '¡Producto registrado correctamente!');
    }

    // Actualizar los datos del producto y reemplazar su imagen si se proporciona
    public function update(Request $request, int $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return redirect()->route('productos.index')
                ->with('error', 'El producto que intentas actualizar no existe.');
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

        return redirect()->route('productos.index')
            ->with('exito', '¡Producto actualizado correctamente!');
    }

    // Borrado LÓGICO: conserva el registro en la base de datos y solo marca "deleted_at"
    public function destroy(int $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return redirect()->route('productos.index')
                ->with('error', 'El producto que intentas eliminar no existe.');
        }

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('exito', "El producto \"{$producto->nombre}\" se movió a la papelera. Puedes restaurarlo desde ahí.");
    }

    // Restaurar un producto eliminado lógicamente para que vuelva a estar activo
    public function restore(int $id)
    {
        $producto = Producto::onlyTrashed()->find($id);

        if (!$producto) {
            return redirect()->route('productos.papelera')
                ->with('error', 'El producto no existe o ya se encuentra activo.');
        }

        $producto->restore();

        return redirect()->route('productos.papelera')
            ->with('exito', "El producto \"{$producto->nombre}\" fue restaurado y ya aparece en el catálogo.");
    }

    // Borrado FÍSICO: elimina el registro de forma definitiva (solo disponible para productos ya eliminados lógicamente)
    public function forceDestroy(int $id)
    {
        $producto = Producto::onlyTrashed()->find($id);

        if (!$producto) {
            return redirect()->route('productos.papelera')
                ->with('error', 'Solo se pueden eliminar de forma definitiva productos que ya estén en la papelera.');
        }

        $dependencias = $this->buscarDependenciasActivas($producto);

        if (!empty($dependencias)) {
            return redirect()->route('productos.papelera')
                ->with('advertencia', "No se puede eliminar definitivamente \"{$producto->nombre}\" porque todavía está relacionado con: " . implode(', ', $dependencias) . '.');
        }

        $this->deleteImageFile($producto->imagen);

        $nombre = $producto->nombre;
        $producto->forceDelete();

        return redirect()->route('productos.papelera')
            ->with('exito', "El producto \"{$nombre}\" y su imagen fueron eliminados de forma definitiva.");
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
