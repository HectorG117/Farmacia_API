@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Catálogo</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Productos</h1>
                <p class="mt-2 text-slate-500">Administra el inventario de productos disponibles en la farmacia.</p>
            </div>
            @auth('admin')
                <div class="flex gap-3">
                    <a href="{{ route('productos.papelera') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-orange-400 hover:text-orange-700">🗑 Papelera</a>
                    <a href="{{ route('productos.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Nuevo producto</a>
                </div>
            @else
                <a href="{{ route('doctor.login') }}" class="inline-flex items-center justify-center rounded-lg border border-teal-700 px-4 py-2.5 text-sm font-semibold text-teal-700 transition hover:bg-teal-50">Acceso para doctores</a>
            @endauth
        </div>

        @if (session('exito'))
            <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-medium text-teal-800">
                {{ session('exito') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($productos as $producto)
                <section class="admin-panel flex flex-col overflow-hidden rounded-xl">
                    <div class="flex h-40 items-center justify-center bg-slate-50">
                        @if ($producto->imagen)
                            <img src="{{ asset($producto->imagen) }}" alt="{{ $producto->nombre }}" class="max-h-36 max-w-full object-contain">
                        @else
                            <span class="text-sm text-slate-400">Sin imagen</span>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col gap-2 p-5">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-base font-bold text-slate-900">{{ $producto->nombre }}</h3>
                            <span class="shrink-0 rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700">{{ $producto->categoria }}</span>
                        </div>
                        <p class="text-xl font-bold text-teal-700">${{ number_format($producto->precio, 2) }}</p>
                        <p class="text-sm text-slate-500">Stock: {{ $producto->stock }} unidades</p>
                        <div class="mt-3 flex gap-2 border-t border-slate-200 pt-3">
                            <a href="{{ route('productos.show', $producto) }}" class="flex-1 rounded-md border border-slate-200 px-2.5 py-1.5 text-center text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Ver</a>
                            @auth('admin')
                                <a href="{{ route('productos.edit', $producto) }}" class="flex-1 rounded-md border border-slate-200 px-2.5 py-1.5 text-center text-xs font-semibold text-slate-600 hover:border-orange-400 hover:text-orange-700">Editar</a>
                            @endauth
                        </div>
                        @auth('admin')
                            <form action="{{ route('productos.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Deseas mover este producto a la papelera? Podrás restaurarlo después.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-md border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
                            </form>
                        @endauth
                    </div>
                </section>
            @empty
                <div class="col-span-full admin-panel rounded-xl p-10 text-center">
                    <p class="text-slate-500">No hay productos registrados aún.</p>
                    @auth('admin')
                        <a href="{{ route('productos.create') }}" class="mt-4 inline-block rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Crear el primer producto</a>
                    @endauth
                </div>
            @endforelse
        </div>
    </div>
@endsection
