<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Acceso de pacientes (clientes): inicio de sesión, registro y cierre de sesión.
 * Usa la tabla "usuarios"; las cuentas con perfil médico entran por el portal médico.
 */
class ClienteAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.cliente-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('cliente')->attempt([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
            'activo'   => true,
        ])) {
            $cliente = Auth::guard('cliente')->user();

            if (Medico::where('id_usuario', $cliente->id_usuario)->exists()) {
                Auth::guard('cliente')->logout();

                return back()->withInput($request->only('email'))
                    ->with('error', 'Esta cuenta pertenece a un médico. Ingresa desde el portal médico.');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('cliente.dashboard'))
                ->with('status', 'Bienvenido(a), ' . $cliente->nombre . '.');
        }

        $usuario = Usuario::where('email', $credentials['email'])->first();
        $message = $usuario && !$usuario->activo && Hash::check($credentials['password'], $usuario->password)
            ? 'La cuenta está deshabilitada. Comunícate con la farmacia.'
            : 'El correo o la contraseña son incorrectos.';

        return back()->withInput($request->only('email'))->with('error', $message);
    }

    public function registroForm(): View
    {
        return view('auth.cliente-registro');
    }

    public function registro(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre'   => ['required', 'string', 'min:2', 'max:100'],
            'apellido' => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\s\+\-()]+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique'       => 'Ya existe una cuenta con ese correo. Inicia sesión.',
            'telefono.regex'     => 'El teléfono solo puede contener números, espacios y los signos + - ( ).',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ], [
            'email'    => 'correo electrónico',
            'telefono' => 'teléfono',
            'password' => 'contraseña',
        ]);

        $usuario = Usuario::create([
            'nombre'         => $datos['nombre'],
            'apellido'       => $datos['apellido'],
            'email'          => $datos['email'],
            'telefono'       => $datos['telefono'] ?? null,
            'password'       => Hash::make($datos['password']),
            'activo'         => true,
            'fecha_registro' => now(),
        ]);

        Auth::guard('cliente')->login($usuario);
        $request->session()->regenerate();

        return redirect()->route('cliente.dashboard')
            ->with('status', '¡Tu cuenta fue creada! Aquí verás tus citas, expedientes y recetas.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('cliente')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cliente.login')
            ->with('status', 'La sesión se cerró correctamente.');
    }
}
