@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-slate-200">
    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Vincular cuenta social</h1>
            <p class="text-xs text-slate-500">Asocia una cuenta de un proveedor externo a un usuario.</p>
        </div>
        <a href="{{ route('admin.sesiones-sociales.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
            ← Volver al listado
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-md text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.sesiones-sociales.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label for="id_usuario" class="block text-sm font-medium text-slate-700 mb-1">Usuario</label>
            <select name="id_usuario" id="id_usuario" required class="w-full rounded-md border border-slate-300 p-2 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                <option value="">-- Seleccionar usuario --</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id_usuario }}" @selected(old('id_usuario') == $usuario->id_usuario)>
                        {{ $usuario->nombre }} {{ $usuario->apellido }} ({{ $usuario->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="proveedor" class="block text-sm font-medium text-slate-700 mb-1">Proveedor</label>
            <select name="proveedor" id="proveedor" required class="w-full rounded-md border border-slate-300 p-2 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                <option value="">-- Seleccionar proveedor --</option>
                @foreach (['Google', 'Facebook', 'Apple'] as $proveedor)
                    <option value="{{ $proveedor }}" @selected(old('proveedor') === $proveedor)>{{ $proveedor }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="provider_id" class="block text-sm font-medium text-slate-700 mb-1">Identificador del proveedor</label>
            <input type="text" name="provider_id" id="provider_id" value="{{ old('provider_id') }}" maxlength="200" required class="w-full rounded-md border border-slate-300 p-2 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.sesiones-sociales.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-md hover:bg-slate-200">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-md hover:bg-teal-700">
                Vincular cuenta
            </button>
        </div>
    </form>
</div>
@endsection
