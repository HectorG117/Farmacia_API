<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | Farmacia Vital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell flex min-h-screen items-center justify-center p-5">
    <main class="admin-panel w-full max-w-lg rounded-xl p-6 sm:p-8">
        <a href="{{ route('cliente.login') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Ya tengo cuenta</a>
        <p class="mt-8 text-xs font-bold uppercase tracking-[.18em] text-orange-600">Farmacia Vital</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Crea tu cuenta de paciente</h1>
        <p class="mt-2 text-sm text-slate-500">Con tu cuenta podrás ver tus citas, tu expediente y elegir los productos de tus recetas.</p>

        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('cliente.registro.store') }}" method="POST" class="mt-6 grid gap-5 sm:grid-cols-2">
            @csrf
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-700">Nombre(s)</span>
                <input class="admin-field" type="text" name="nombre" value="{{ old('nombre') }}" maxlength="100" autocomplete="given-name" required autofocus>
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-700">Apellidos</span>
                <input class="admin-field" type="text" name="apellido" value="{{ old('apellido') }}" maxlength="100" autocomplete="family-name" required>
            </label>
            <label class="block sm:col-span-2">
                <span class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</span>
                <input class="admin-field" type="email" name="email" value="{{ old('email') }}" maxlength="150" autocomplete="email" required>
            </label>
            <label class="block sm:col-span-2">
                <span class="mb-2 block text-sm font-semibold text-slate-700">Teléfono (opcional)</span>
                <input class="admin-field" type="tel" name="telefono" value="{{ old('telefono') }}" maxlength="20" autocomplete="tel">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-700">Contraseña</span>
                <input class="admin-field" type="password" name="password" minlength="8" autocomplete="new-password" required>
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contraseña</span>
                <input class="admin-field" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
            </label>
            <button type="submit" class="w-full rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800 sm:col-span-2">Crear cuenta</button>
        </form>
    </main>
</body>
</html>
