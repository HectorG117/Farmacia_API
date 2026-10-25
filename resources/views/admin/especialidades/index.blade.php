@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-7xl">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-teal-100 p-4 text-sm font-semibold text-teal-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Gestión de Especialidades</h1>
                <p class="mt-2 max-w-2xl text-slate-500">Administra las especialidades médicas del sistema.</p>
            </div>
            <a href="{{ route('admin.especialidades.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Nueva Especialidad</a>
        </div>

        <section class="admin-panel rounded-xl p-6">
            <div class="overflow-x-auto">
                <table class="admin-table w-full min-w-[540px] text-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600">
                        @forelse ($especialidades as $especialidad)
                            <tr>
                                <td class="font-semibold text-slate-800">{{ $especialidad->id_especialidad ?? $especialidad->id }}</td>
                                <td class="font-medium text-slate-900">{{ $especialidad->nombre }}</td>
                                <td>{{ $especialidad->descripcion ?? 'Sin descripción' }}</td>
                                <td>
                                    <form action="{{ route('admin.especialidades.destroy', $especialidad->id_especialidad ?? $especialidad->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta especialidad?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-slate-500">No hay especialidades registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection