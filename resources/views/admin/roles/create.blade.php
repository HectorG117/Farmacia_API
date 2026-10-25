@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Crear Nuevo Rol</h1>
            <a href="{{ route('admin.roles.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver</a>
        </div>

        <section class="admin-panel rounded-xl p-6">
            <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nombre del Rol</label>
                    <input type="text" name="nombre" required placeholder="Ej. Administrador, Médico, Farmacéutico" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Descripción breve del rol" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none"></textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-teal-700 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">Guardar Rol</button>
            </form>
        </section>
    </div>
@endsection