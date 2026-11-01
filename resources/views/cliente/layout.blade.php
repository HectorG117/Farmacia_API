<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi portal') | Farmacia Vital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell min-h-screen">
    @php
        $menu = [
            ['route' => 'cliente.dashboard', 'match' => 'cliente.dashboard', 'label' => 'Inicio'],
            ['route' => 'cliente.citas.index', 'match' => 'cliente.citas.*', 'label' => 'Mis citas'],
            ['route' => 'cliente.expedientes.index', 'match' => 'cliente.expedientes.*', 'label' => 'Mi expediente'],
            ['route' => 'cliente.recetas.index', 'match' => 'cliente.recetas.*', 'label' => 'Mis recetas'],
            ['route' => 'productos.index', 'match' => 'productos.*', 'label' => 'Catálogo'],
        ];
    @endphp
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4">
            <div>
                <a href="{{ route('cliente.dashboard') }}" class="text-lg font-bold tracking-wide text-slate-900">FARMACIA <span class="text-orange-500">VITAL</span></a>
                <p class="mt-1 text-xs text-slate-500">Portal del paciente · tu información de salud en un solo lugar</p>
            </div>
            <nav class="order-3 flex w-full gap-1 overflow-x-auto text-sm font-semibold md:order-none md:w-auto">
                @foreach ($menu as $item)
                    <a href="{{ route($item['route']) }}" class="shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs($item['match']) ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:text-teal-700' }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-4">
                <span class="hidden text-sm font-semibold text-slate-700 sm:block">{{ auth('cliente')->user()->nombre }} {{ auth('cliente')->user()->apellido }}</span>
                <form action="{{ route('cliente.logout') }}" method="POST">
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
