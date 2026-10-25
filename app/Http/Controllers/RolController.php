<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    /**
     * Muestra la lista de roles.
     */
    public function index()
    {
        $roles = Rol::all();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Muestra el formulario para crear un nuevo rol.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Almacena un rol recién creado en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
        ]);

        Rol::create($request->only(['nombre', 'descripcion']));

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Muestra un rol específico.
     */
    public function show(string $id)
    {
        $rol = Rol::findOrFail($id);
        return view('admin.roles.show', compact('rol'));
    }

    /**
     * Muestra el formulario para editar un rol.
     */
    public function edit(string $id)
    {
        $rol = Rol::findOrFail($id);
        return view('admin.roles.edit', compact('rol'));
    }

    /**
     * Actualiza el rol especificado.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $rol = Rol::findOrFail($id);
        $rol->update($request->only(['nombre', 'descripcion']));

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Elimina el rol especificado.
     */
    public function destroy(string $id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado exitosamente.');
    }
}