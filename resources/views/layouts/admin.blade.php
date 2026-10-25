@php
    $navigation = [
        ['slug' => 'usuarios', 'label' => 'Usuarios', 'icon' => '◉', 'gate' => 'ver-registros'],
        ['slug' => 'roles', 'label' => 'Roles', 'icon' => '◇', 'gate' => 'eliminar-registros'], // Exclusivo Nivel 3
        ['slug' => 'usuario-roles', 'label' => 'Roles de usuario', 'icon' => '⬡', 'gate' => 'eliminar-registros'], // Exclusivo Nivel 3
        ['slug' => 'especialidades', 'label' => 'Especialidades', 'icon' => '✦', 'gate' => 'ver-registros'],
        ['slug' => 'sesiones-sociales', 'label' => 'Sesiones sociales', 'icon' => '↗', 'gate' => 'ver-registros'],
        ['slug' => 'medicos', 'label' => 'Medicos', 'icon' => '＋', 'gate' => 'ver-registros'],
        ['slug' => 'citas', 'label' => 'Citas', 'icon' => '◷', 'gate' => 'ver-registros'],
        ['slug' => 'expedientes', 'label' => 'Expedientes', 'icon' => '▤', 'gate' => 'ver-registros'],
        ['slug' => 'recetas-medicas', 'label' => 'Recetas medicas', 'icon' => '℞', 'gate' => 'ver-registros'],
        ['slug' => 'servicios-medicos', 'label' => 'Servicios medicos', 'icon' => '✚', 'gate' => 'ver-registros'],
        ['slug' => 'carritos', 'label' => 'Carritos', 'icon' => '▱', 'gate' => 'ver-registros'],
        ['slug' => 'carrito-detalles', 'label' => 'Detalles de carrito', 'icon' => '▤', 'gate' => 'ver-registros'],
        ['slug' => 'historial-transacciones', 'label' => 'Transacciones', 'icon' => '↔', 'gate' => 'ver-registros'],
        ['slug' => 'users', 'label' => 'Cuentas de acceso', 'icon' => '◎', 'gate' => 'eliminar-registros'], // Exclusivo Nivel 3
        ['slug' => 'logs', 'label' => 'Logs del sistema', 'icon' => '≡', 'gate' => 'eliminar-registros'], // Exclusivo Nivel 3
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel administrativo' }} | Farmacia Vital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <aside class="admin-sidebar w-full shrink-0 text-white lg:min-h-screen lg:w-72">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold tracking-wide">FARMACIA <span class="text-orange-300">VITAL</span></a>
                @auth('admin')
                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] uppercase tracking-widest text-cyan-100">
                        Nivel {{ auth('admin')->user()->nivel ?? 1 }}
                    </span>
                @else
                    <span class="rounded-full bg-white/10 px-2 py-1 text-[10px] uppercase tracking-widest text-cyan-100">Admin</span>
                @endauth
            </div>
            <nav class="flex gap-1 overflow-x-auto p-4 lg:block lg:space-y-1 lg:overflow-visible">
                <a href="{{ route('productos.index') }}" class="admin-nav-link flex shrink-0 items-center gap-3 rounded-lg px-3 py-2.5 text-sm {{ request()->routeIs('productos.index', 'productos.show') ? 'is-active' : '' }}">
                    <span class="w-5 text-center text-cyan-200">▣</span> Productos
                </a>
                @auth('admin')
                    <a href="{{ route('admin.dashboard') }}" class="admin-nav-link flex shrink-0 items-center gap-3 rounded-lg px-3 py-2.5 text-sm {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                        <span class="w-5 text-center">⌂</span> Resumen
                    </a>
                    @foreach ($navigation as $item)
                        @can($item['gate'])
                            <a href="{{ route('admin.' . $item['slug'] . '.index') }}" class="admin-nav-link flex shrink-0 items-center gap-3 rounded-lg px-3 py-2.5 text-sm {{ request()->routeIs('admin.' . $item['slug'] . '.*') ? 'is-active' : '' }}">
                                <span class="w-5 text-center text-cyan-200">{{ $item['icon'] }}</span> {{ $item['label'] }}
                            </a>
                        @endcan
                    @endforeach
                @endauth
                @guest('admin')
                    <a href="{{ route('login') }}" class="admin-nav-link flex shrink-0 items-center gap-3 rounded-lg px-3 py-2.5 text-sm">
                        <span class="w-5 text-center">◎</span> Acceso de administrador
                    </a>
                @endguest
            </nav>
        </aside>
        <main class="min-w-0 flex-1">
            <header class="flex items-center justify-between border-b border-slate-200 bg-white/80 px-5 py-4 backdrop-blur lg:px-10">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-teal-700">Centro de operaciones</p>
                    <p class="mt-1 text-sm text-slate-500">Gestión visual del sistema</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" class="hidden rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 sm:block">⌕ Buscar</button>
                    @auth('admin')
                        <span class="hidden text-sm font-semibold text-slate-700 sm:block">
                            {{ auth('admin')->user()->nombre_completo }}
                            <span class="ml-1 rounded bg-slate-100 px-2 py-0.5 text-xs font-normal text-slate-500">Nivel {{ auth('admin')->user()->nivel }}</span>
                        </span>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:border-red-300 hover:text-red-700">Cerrar sesión</button>
                        </form>
                    @endauth
                    @guest('admin')
                        <a href="{{ route('login') }}" class="rounded-lg bg-teal-700 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-800">Iniciar sesión</a>
                    @endguest
                </div>
            </header>
            <div class="p-5 lg:p-10">
                @session('status')
                    <div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ $value }}
                    </div>
                @endsession
                @session('error')
                    <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ $value }}
                    </div>
                @endsession
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>