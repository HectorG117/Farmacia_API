@extends('doctor.layout')

@section('title', 'Mis recetas')

@section('content')
    @php
        $colorEstado = [
            'Vigente'   => 'bg-teal-50 text-teal-700',
            'Surtida'   => 'bg-blue-50 text-blue-700',
            'Cancelada' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.18em] text-teal-700">Prescripción</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Mis recetas</h1>
            <p class="mt-2 text-slate-500">Recetas que has emitido a tus pacientes. Para anular una, edítala y márcala como "Cancelada".</p>
        </div>
        <a href="{{ route('doctor.recetas.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">+ Nueva receta</a>
    </div>

    <section class="admin-panel overflow-hidden rounded-xl">
        <div class="overflow-x-auto">
            <table class="admin-table w-full min-w-[720px] text-sm">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Paciente</th>
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
                            <td>{{ $receta->detalles_count }}</td>
                            <td><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $colorEstado[$receta->estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $receta->estado }}</span></td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="{{ route('doctor.recetas.show', $receta->id_receta) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Ver</a>
                                    <a href="{{ route('doctor.recetas.edit', $receta->id_receta) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-orange-400 hover:text-orange-700">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">Aún no has emitido recetas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
