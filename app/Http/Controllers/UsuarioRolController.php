<?php

namespace App\Http\Controllers;

use App\Models\UsuarioRol;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;

class UsuarioRolController extends Controller
{
    public function index()
    {
        $usuarioRoles = UsuarioRol::with(['usuario', 'rol'])->get();
        return view('admin.usuario_roles.index', compact('usuarioRoles'));
    }

    public function create()
    {
        $usuarios = Usuario::all();
        $roles = Rol::all();
        return view('admin.usuario_roles.create', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'id_rol' => 'required|exists:roles,id_rol',
        ]);

        UsuarioRol::firstOrCreate([
            'id_usuario' => $request->id_usuario,
            'id_rol' => $request->id_rol,
        ]);

        return redirect()->route('admin.usuario-roles.index')->with('success', 'Rol asignado correctamente.');
    }

    public function destroy($id_usuario, $id_rol)
    {
        UsuarioRol::where('id_usuario', $id_usuario)
            ->where('id_rol', $id_rol)
            ->delete();

        return redirect()->route('admin.usuario-roles.index')->with('success', 'Asignación eliminada.');
    }
}