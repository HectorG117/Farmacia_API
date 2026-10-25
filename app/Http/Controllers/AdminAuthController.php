<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.admin-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nombre_usuario' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('admin')->attempt([
            'nombre_usuario' => $credentials['nombre_usuario'],
            'password' => $credentials['password'],
            'activo' => true,
        ])) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))
                ->with('status', 'Inicio de sesión correcto.');
        }

        $administrador = Administrador::where('nombre_usuario', $credentials['nombre_usuario'])->first();
        $message = $administrador
            && Hash::check($credentials['password'], $administrador->password)
            && ! $administrador->activo
                ? 'La cuenta de administrador está deshabilitada.'
                : 'El nombre de usuario o la contraseña son incorrectos.';

        return back()
            ->withInput($request->only('nombre_usuario'))
            ->with('error', $message);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'La sesión se cerró correctamente.');
    }
}
