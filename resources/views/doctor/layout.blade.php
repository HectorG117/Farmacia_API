<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal médico') | Farmacia Vital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell min-h-screen">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4">
            <div>
                <a href="{{ route('doctor.dashboard') }}" class="text-lg font-bold tracking-wide text-slate-900">FARMACIA <span class="text-teal-700">VITAL</span></a>
                <p class="mt-1 text-xs text-slate-500">Portal clínico · acceso limitado a tus pacientes</p>
            </div>
            <nav class="flex items-center gap-1 text-sm font-semibold">
                <a href="{{ route('doctor.dashboard') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('doctor.dashboard', 'doctor.expedientes.*') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:text-teal-700' }}">Mis citas</a>
                <a href="{{ route('doctor.recetas.index') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('doctor.recetas.*') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:text-teal-700' }}">Mis recetas</a>
            </nav>
            <div class="flex items-center gap-4">
                <span class="hidden text-sm font-semibold text-slate-700 sm:block">{{ auth('doctor')->user()->nombre }} {{ auth('doctor')->user()->apellido }}</span>
                <form action="{{ route('doctor.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:border-red-300 hover:text-red-700">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-7xl p-5 lg:p-10">
        @session('status')
            <div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ $value }}</div>
        @endsession
        @session('error')
            <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $value }}</div>
        @endsession
        @yield('content')
    </main>
</body>
</html>
