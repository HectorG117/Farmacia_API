@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-8">
            <a href="{{ route('productos.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver al catálogo</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $producto->nombre }}</h1>
            <p class="mt-2 text-slate-500">Detalle del producto (solo lectura).</p>
        </div>

        @if (session('exito'))
            <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-medium text-teal-800">{{ session('exito') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
        @endif

        <section class="admin-panel grid gap-6 rounded-xl p-6 md:grid-cols-2 lg:p-8">
            <div class="flex items-center justify-center rounded-lg bg-slate-50 p-6">
                @if ($producto->imagen)
                    <img src="{{ asset($producto->imagen) }}" alt="{{ $producto->nombre }}" class="max-h-64 max-w-full object-contain">
                @else
                    <span class="text-sm text-slate-400">Sin imagen</span>
                @endif
            </div>
            <dl class="divide-y divide-slate-200 text-sm">
                <div class="flex justify-between py-3"><dt class="text-slate-500">ID</dt><dd class="font-semibold text-slate-800">#{{ $producto->id }}</dd></div>
                <div class="flex justify-between py-3"><dt class="text-slate-500">Categoría</dt><dd><span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700">{{ $producto->categoria }}</span></dd></div>
                <div class="flex justify-between py-3"><dt class="text-slate-500">Precio</dt><dd class="font-semibold text-teal-700">${{ number_format($producto->precio, 2) }}</dd></div>
                <div class="flex justify-between py-3"><dt class="text-slate-500">Stock</dt><dd class="font-semibold text-slate-800">{{ $producto->stock }} unidades</dd></div>
                <div class="flex justify-between py-3"><dt class="text-slate-500">Registrado el</dt><dd class="text-slate-800">{{ $producto->created_at?->format('d/m/Y H:i') ?? 'N/D' }}</dd></div>
                <div class="flex justify-between py-3"><dt class="text-slate-500">Última actualización</dt><dd class="text-slate-800">{{ $producto->updated_at?->format('d/m/Y H:i') ?? 'N/D' }}</dd></div>
            </dl>
        </section>

        @auth('admin')
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('productos.edit', $producto) }}" class="rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">Editar</a>
                <form action="{{ route('productos.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Deseas mover este producto a la papelera? Podrás restaurarlo después.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
                </form>
            </div>
        @endauth
    </div>
@endsection
