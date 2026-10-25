<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Muestra la lista de logs registrados.
     */
    public function index()
    {
        $logs = Log::all();
        return view('admin.logs.index', compact('logs'));
    }

    /**
     * Muestra el formulario para registrar un log manualmente.
     */
    public function create()
    {
        return view('admin.logs.create');
    }

    /**
     * Almacena un nuevo registro de log.
     */
    public function store(Request $request)
    {
        $request->validate([
            'accion' => 'required|string|max:255',
            'modulo' => 'required|string|max:100',
        ]);

        Log::create([
            'id_usuario' => $request->id_usuario ?? null,
            'accion'     => $request->accion,
            'modulo'     => $request->modulo,
            'ip'         => $request->ip(),
            'fecha'      => now(),
        ]);

        return redirect()->route('admin.logs.index')->with('success', 'Log registrado exitosamente.');
    }

    /**
     * Elimina el registro de log especificado.
     */
    public function destroy(string $id)
    {
        $log = Log::findOrFail($id);
        $log->delete();

        return redirect()->route('admin.logs.index')->with('success', 'Log eliminado exitosamente.');
    }
}