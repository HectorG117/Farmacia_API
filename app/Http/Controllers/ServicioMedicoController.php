<?php

namespace App\Http\Controllers;

use App\Models\ServicioMedico;
use Illuminate\Http\Request;

class ServicioMedicoController extends Controller
{
    public function index()
    {
        $servicios = ServicioMedico::all();
        return view('admin.servicios-medicos.index', compact('servicios'));
    }

    public function create()
    {
        return view('admin.servicios-medicos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'costo'       => 'required|numeric|min:0',
        ]);

        ServicioMedico::create($request->only(['nombre', 'descripcion', 'costo']));

        return redirect()->route('admin.servicios-medicos.index')->with('success', 'Servicio médico creado exitosamente.');
    }

    public function destroy(string $id)
    {
        $servicio = ServicioMedico::findOrFail($id);
        $servicio->delete();

        return redirect()->route('admin.servicios-medicos.index')->with('success', 'Servicio médico eliminado exitosamente.');
    }
}