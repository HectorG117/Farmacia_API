<?php

namespace App\Http\Controllers;

use App\Models\ServicioMedico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'costo'       => 'required|numeric|min:0',
        ]);

        ServicioMedico::create($request->only(['nombre', 'descripcion', 'costo']));

        return redirect()->route('admin.servicios-medicos.index')->with('success', 'Servicio médico creado exitosamente.');
    }

    public function edit(string $id)
    {
        $servicio = ServicioMedico::findOrFail($id);
        return view('admin.servicios-medicos.edit', compact('servicio'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'costo'       => 'required|numeric|min:0',
        ]);

        $servicio = ServicioMedico::findOrFail($id);
        $servicio->update($request->only(['nombre', 'descripcion', 'costo']));

        return redirect()->route('admin.servicios-medicos.index')->with('success', 'Servicio médico actualizado exitosamente.');
    }

    public function destroy(string $id)
    {
        $servicio = ServicioMedico::findOrFail($id);

        // La llave foránea de "carrito_detalle" impide borrar un servicio que está en carritos
        if (DB::table('carrito_detalle')->where('id_servicio', $servicio->id_servicio)->exists()) {
            return redirect()->route('admin.servicios-medicos.index')
                ->with('error', "No se puede eliminar \"{$servicio->nombre}\" porque está en carritos de compra.");
        }

        $servicio->delete();

        return redirect()->route('admin.servicios-medicos.index')->with('success', 'Servicio médico eliminado exitosamente.');
    }
}