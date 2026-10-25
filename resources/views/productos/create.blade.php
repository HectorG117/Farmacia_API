@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-8">
            <a href="{{ route('productos.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver al catálogo</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Nuevo producto</h1>
            <p class="mt-2 text-slate-500">Completa la información para registrarlo en el catálogo.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Por favor corrige los siguientes errores:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="admin-panel rounded-xl p-6 lg:p-8">
            <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-5 md:grid-cols-2">
                @csrf

                <label>
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Nombre del producto</span>
                    <input class="admin-field" type="text" name="nombre" value="{{ old('nombre') }}" required minlength="3" maxlength="100">
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Precio ($)</span>
                    <input class="admin-field" type="number" step="0.01" name="precio" value="{{ old('precio') }}" required min="0.01">
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Stock</span>
                    <input class="admin-field" type="number" name="stock" value="{{ old('stock') }}" required min="0">
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Categoría</span>
                    <select class="admin-field" name="categoria" required>
                        <option value="">-- Selecciona una opción --</option>
                        @foreach (['Analgesico' => 'Analgésico', 'Antibiotico' => 'Antibiótico', 'Vitamina' => 'Vitamina'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('categoria') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="md:col-span-2">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Imagen (JPG, PNG, WEBP - máx. 2MB)</span>
                    <input class="admin-field" type="file" name="imagen" accept="image/jpeg,image/png,image/webp" required>
                </label>

                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-6 md:col-span-2">
                    <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">Guardar producto</button>
                    <a href="{{ route('productos.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
                </div>
            </form>
        </section>
    </div>
@endsection
