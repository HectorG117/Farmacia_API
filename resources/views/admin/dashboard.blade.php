@extends('layouts.admin')

@section('content')
    @php
        $colorEstado = [
            'Pendiente'  => 'bg-orange-50 text-orange-700',
            'Confirmada' => 'bg-blue-50 text-blue-700',
            'Vigente'    => 'bg-teal-50 text-teal-700',
            'Surtida'    => 'bg-blue-50 text-blue-700',
            'Cancelada'  => 'bg-red-50 text-red-700',
        ];
        $puntos = ['teal' => 'bg-teal-400', 'orange' => 'bg-orange-400', 'blue' => 'bg-blue-400', 'violet' => 'bg-violet-400'];
    @endphp

    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="mb-2 text-sm font-semibold text-teal-700">{{ now()->format('d/m/Y') }}</p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Resumen operativo</h1>
                <p class="mt-2 max-w-2xl text-slate-500">Una vista general de la actividad de Farmacia Vital.</p>
            </div>
            @can('crear-editar-registros')
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.citas.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Agendar cita</a>
                    <a href="{{ route('admin.recetas-medicas.create') }}" class="inline-flex items-center justify-center rounded-lg border border-teal-600 px-4 py-2.5 text-sm font-semibold text-teal-700 transition hover:bg-teal-50">+ Nueva receta</a>
                </div>
            @endcan
        </div>

        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($estadisticas as $stat)
                <a href="{{ route($stat['route'], $stat['params'] ?? []) }}" class="admin-panel block rounded-xl p-5 transition hover:border-teal-300">
                    <div class="mb-5 flex items-center justify-between">
                        <span class="text-sm text-slate-500">{{ $stat['label'] }}</span>
                        <span class="h-2.5 w-2.5 rounded-full {{ $puntos[$stat['tone']] ?? 'bg-slate-400' }}"></span>
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($stat['value']) }}</p>
                </a>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
            {{-- Próximas citas --}}
            <section class="admin-panel overflow-hidden rounded-xl">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Próximas citas</h2>
                        <p class="mt-1 text-sm text-slate-500">Pendientes y confirmadas a partir de hoy</p>
                    </div>
                    <a href="{{ route('admin.citas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Ver todas</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="admin-table w-full min-w-[540px] text-sm">
                        <thead><tr><th>Fecha</th><th>Paciente</th><th>Médico</th><th>Estado</th></tr></thead>
                        <tbody class="text-slate-600">
                            @forelse ($proximasCitas as $cita)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="font-semibold text-slate-800"><a href="{{ route('admin.citas.show', $cita->id_cita) }}" class="hover:text-teal-700">{{ $cita->fecha?->format('d/m/Y') }} · {{ $cita->hora_corta }}</a></td>
                                    <td>{{ $cita->paciente?->nombre_completo ?? 'N/D' }}</td>
                                    <td>{{ $cita->medico?->nombre_completo ?? 'N/D' }}</td>
                                    <td><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $colorEstado[$cita->estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $cita->estado }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-slate-500">No hay citas próximas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Stock bajo --}}
            <section class="admin-panel overflow-hidden rounded-xl">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Stock bajo</h2>
                        <p class="mt-1 text-sm text-slate-500">Productos con menos de {{ $limiteStock }} unidades</p>
                    </div>
                    <a href="{{ route('productos.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Catálogo</a>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse ($stockBajo as $producto)
                        <a href="{{ route('productos.show', $producto) }}" class="flex items-center justify-between px-5 py-3 text-sm transition hover:bg-slate-50">
                            <span class="font-semibold text-slate-800">{{ $producto->nombre }}</span>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ (int) $producto->stock === 0 ? 'bg-red-50 text-red-700' : 'bg-orange-50 text-orange-700' }}">{{ $producto->stock }} u.</span>
                        </a>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Todo el inventario está en buen nivel.</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Últimas recetas --}}
        <section class="admin-panel mt-6 overflow-hidden rounded-xl">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Últimas recetas</h2>
                    <p class="mt-1 text-sm text-slate-500">Incluye lo que cada paciente eligió comprar</p>
                </div>
                <a href="{{ route('admin.recetas-medicas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Ver todas</a>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table w-full min-w-[640px] text-sm">
                    <thead><tr><th>Folio</th><th>Fecha</th><th>Paciente</th><th>Médico</th><th>Compra elegida</th><th>Estado</th></tr></thead>
                    <tbody class="text-slate-600">
                        @forelse ($ultimasRecetas as $receta)
                            <tr class="transition hover:bg-slate-50">
                                <td class="font-semibold text-slate-800"><a href="{{ route('admin.recetas-medicas.show', $receta->id_receta) }}" class="hover:text-teal-700">#{{ str_pad($receta->id_receta, 4, '0', STR_PAD_LEFT) }}</a></td>
                                <td>{{ $receta->fecha_emision?->format('d/m/Y') }}</td>
                                <td>{{ $receta->paciente?->nombre_completo ?? 'N/D' }}</td>
                                <td>{{ $receta->medico?->nombre_completo ?? 'N/D' }}</td>
                                <td>{{ $receta->productos_seleccionados }} prod. · ${{ number_format($receta->total_compra, 2) }}</td>
                                <td><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $colorEstado[$receta->estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $receta->estado }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-slate-500">Aún no hay recetas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
