<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso administrativo | Farmacia Vital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell flex min-h-screen items-center justify-center p-5">
    <main class="admin-panel w-full max-w-md rounded-xl p-6 sm:p-8">
        <a href="{{ route('productos.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver al catálogo</a>
        <p class="mt-8 text-xs font-bold uppercase tracking-[.18em] text-orange-600">Farmacia Vital</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Acceso de administrador</h1>
        <p class="mt-2 text-sm text-slate-500">Inicia sesión con tu nombre de usuario y contraseña.</p>

        @session('status')
            <div role="status" class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ $value }}
            </div>
        @endsession
        @session('error')
            <div role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $value }}
            </div>
        @endsession
        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login.store') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-700">Nombre de usuario</span>
                <input class="admin-field" type="text" name="nombre_usuario" value="{{ old('nombre_usuario') }}" autocomplete="username" required autofocus>
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-700">Contraseña</span>
                <input class="admin-field" type="password" name="password" autocomplete="current-password" required>
            </label>
            <button type="submit" class="w-full rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">
                Iniciar sesión
            </button>
        </form>
        <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">
            ¿Eres doctor?
            <a href="{{ route('doctor.login') }}" class="font-semibold text-teal-700 hover:text-teal-900">Accede al portal clínico</a>
        </p>
    </main>
</body>
</html>
