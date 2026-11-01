<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Medico;
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
            'nombre'      => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:500',
        ]);

        Especialidad::create($request->only(['nombre', 'descripcion']));

        return redirect()->route('admin.especialidades.index')->with('success', 'Especialidad creada exitosamente.');
    }

    public function edit(string $id)
    {
        $especialidad = Especialidad::findOrFail($id);
        return view('admin.especialidades.edit', compact('especialidad'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre'      => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $especialidad = Especialidad::findOrFail($id);
        $especialidad->update($request->only(['nombre', 'descripcion']));

        return redirect()->route('admin.especialidades.index')->with('success', 'Especialidad actualizada exitosamente.');
    }

    public function destroy(string $id)
    {
        $especialidad = Especialidad::findOrFail($id);

        // La llave foránea de "medicos" impide borrar una especialidad en uso
        if (Medico::where('especialidad_id', $especialidad->id_especialidad)->exists()) {
            return redirect()->route('admin.especialidades.index')
                ->with('error', "No se puede eliminar \"{$especialidad->nombre}\" porque hay médicos con esa especialidad.");
        }

        $especialidad->delete();

        return redirect()->route('admin.especialidades.index')->with('success', 'Especialidad eliminada exitosamente.');
    }
}