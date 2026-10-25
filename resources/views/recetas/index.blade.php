@extends('layouts.admin')

@section('content')
    @php
        $colorEstado = [
            'Vigente'   => 'bg-teal-50 text-teal-700',
            'Surtida'   => 'bg-blue-50 text-blue-700',
            'Cancelada' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Resumen</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Recetas médicas</h1>
                <p class="mt-2 text-slate-500">Recetas emitidas por los médicos, con sus productos e instrucciones de uso.</p>
            </div>
            @can('crear-editar-registros')
                <a href="{{ route('admin.recetas-medicas.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Nueva receta</a>
            @endcan
        </div>


        <section class="admin-panel overflow-hidden rounded-xl">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <span class="text-sm text-slate-500">{{ $recetas->count() }} {{ $recetas->count() === 1 ? 'receta registrada' : 'recetas registradas' }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table w-full min-w-[820px] text-sm">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Productos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600">
                        @forelse ($recetas as $receta)
                            <tr class="transition hover:bg-slate-50">
                                <td class="font-semibold text-slate-800">#{{ str_pad($receta->id_receta, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $receta->fecha_emision?->format('d/m/Y') }}</td>
                                <td>{{ $receta->paciente?->nombre_completo ?? 'N/D' }}</td>
                                <td>{{ $receta->medico?->usuario?->nombre_completo ?? 'N/D' }}</td>
                                <td>{{ $receta->detalles_count }}</td>
                                <td><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $colorEstado[$receta->estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $receta->estado }}</span></td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.recetas-medicas.show', $receta->id_receta) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Ver</a>
                                        @can('crear-editar-registros')
                                            <a href="{{ route('admin.recetas-medicas.edit', $receta->id_receta) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-orange-400 hover:text-orange-700">Editar</a>
                                        @endcan
                                        @can('eliminar-registros')
                                        <form action="{{ route('admin.recetas-medicas.destroy', $receta->id_receta) }}" method="POST" onsubmit="return confirm('¿Eliminar esta receta? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-500">
                                    No hay recetas registradas aún.
                                    @can('crear-editar-registros')
                                        <a href="{{ route('admin.recetas-medicas.create') }}" class="ml-1 font-semibold text-teal-700 hover:text-teal-900">Crear la primera</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
