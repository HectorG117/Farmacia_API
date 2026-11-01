@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Editar Especialidad</h1>
            <a href="{{ route('admin.especialidades.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver</a>
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <section class="admin-panel rounded-xl p-6">
            <form action="{{ route('admin.especialidades.update', $especialidad->id_especialidad) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nombre de la Especialidad</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $especialidad->nombre) }}" required placeholder="Ej. Cardiología, Pediatría" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Descripción breve" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">{{ old('descripcion', $especialidad->descripcion) }}</textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-teal-700 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">Guardar cambios</button>
            </form>
        </section>
    </div>
@endsection