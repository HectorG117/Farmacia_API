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
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Servicios Médicos</h1>
                <p class="mt-2 max-w-2xl text-slate-500">Administra el catálogo de servicios médicos ofertados.</p>
            </div>
            <a href="{{ route('admin.servicios-medicos.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Nuevo Servicio</a>
        </div>

        <section class="admin-panel rounded-xl p-6">
            <div class="overflow-x-auto">
                <table class="admin-table w-full min-w-[540px] text-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Costo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600">
                        @forelse ($servicios as $servicio)
                            <tr>
                                <td class="font-semibold text-slate-800">{{ $servicio->id_servicio ?? $servicio->id }}</td>
                                <td class="font-medium text-slate-900">{{ $servicio->nombre }}</td>
                                <td>{{ $servicio->descripcion ?? 'Sin descripción' }}</td>
                                <td class="font-semibold text-teal-800">${{ number_format($servicio->costo, 2) }}</td>
                                <td>
                                    <form action="{{ route('admin.servicios-medicos.destroy', $servicio->id_servicio ?? $servicio->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este servicio?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-slate-500">No hay servicios médicos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection