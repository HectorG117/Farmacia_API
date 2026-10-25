<?php

namespace App\Http\Controllers;

use App\Models\HistorialTransaccion;
use App\Models\Usuario;
use App\Models\Carrito;
use Illuminate\Http\Request;

class HistorialTransaccionController extends Controller
{
    public function index()
    {
        $transacciones = HistorialTransaccion::with(['usuario', 'carrito'])->get();
        return view('admin.historial_transacciones.index', compact('transacciones'));
    }

    public function create()
    {
        $usuarios = Usuario::all();
        $carritos = Carrito::all();
        return view('admin.historial_transacciones.create', compact('usuarios', 'carritos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'id_carrito' => 'required|exists:carrito,id_carrito',
            'total' => 'required|numeric|min:0',
            'estado' => 'required|string|max:30',
        ]);

        HistorialTransaccion::create([
            'id_usuario' => $request->id_usuario,
            'id_carrito' => $request->id_carrito,
            'total' => $request->total,
            'estado' => $request->estado,
            'fecha' => now(),
        ]);

        return redirect()->route('admin.historial-transacciones.index')->with('success', 'Transacción registrada.');
    }

    public function destroy($id)
    {
        $transaccion = HistorialTransaccion::findOrFail($id);
        $transaccion->delete();

        return redirect()->route('admin.historial-transacciones.index')->with('success', 'Transacción eliminada.');
    }
}
