<?php

namespace App\Http\Controllers;

use App\Models\SesionSocial;
use App\Models\Usuario;
use Illuminate\Http\Request;

class SesionSocialController extends Controller
{
    public function index()
    {
        $sesiones = SesionSocial::with('usuario')->get();
        return view('admin.sesiones_sociales.index', compact('sesiones'));
    }

    public function create()
    {
        $usuarios = Usuario::all();
        return view('admin.sesiones_sociales.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'proveedor' => 'required|string|max:50',
            'provider_id' => 'required|string|max:200',
        ]);

        SesionSocial::create([
            'id_usuario' => $request->id_usuario,
            'proveedor' => $request->proveedor,
            'provider_id' => $request->provider_id,
            'fecha_vinculacion' => now(),
        ]);

        return redirect()->route('admin.sesiones-sociales.index')->with('success', 'Sesión social vinculada.');
    }

    public function destroy($id)
    {
        $sesion = SesionSocial::findOrFail($id);
        $sesion->delete();

        return redirect()->route('admin.sesiones-sociales.index')->with('success', 'Sesión social eliminada.');
    }
}
