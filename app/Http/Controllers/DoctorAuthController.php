<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.doctor-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('doctor')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'activo' => true,
        ])) {
            $doctor = Auth::guard('doctor')->user();

            if (! Medico::where('id_usuario', $doctor->id_usuario)->exists()) {
                Auth::guard('doctor')->logout();

                return back()->withInput($request->only('email'))
                    ->with('error', 'Esta cuenta no tiene un perfil médico habilitado.');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('doctor.dashboard'))
                ->with('status', 'Inicio de sesión correcto.');
        }

        $user = Usuario::where('email', $credentials['email'])->first();
        $message = $user && ! $user->activo
            ? 'La cuenta está deshabilitada.'
            : 'El correo o la contraseña son incorrectos.';

        return back()
            ->withInput($request->only('email'))
            ->with('error', $message);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('doctor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('doctor.login')
            ->with('status', 'La sesión se cerró correctamente.');
    }
}
