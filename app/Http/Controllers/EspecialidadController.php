<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    public function index()
    {
        $especialidades = Especialidad::all();
        return view('admin.especialidades.index', compact('especialidades'));
    }

    public function create()
    {
        return view('admin.especialidades.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
        ]);

        Especialidad::create($request->only(['nombre', 'descripcion']));

        return redirect()->route('admin.especialidades.index')->with('success', 'Especialidad creada exitosamente.');
    }

    public function destroy(string $id)
    {
        $especialidad = Especialidad::findOrFail($id);
        $especialidad->delete();

        return redirect()->route('admin.especialidades.index')->with('success', 'Especialidad eliminada exitosamente.');
    }
}