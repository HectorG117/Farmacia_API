@extends('layouts.admin')

@section('content')
    @php
        $colorEstado = [
            'Pendiente'  => 'bg-orange-50 text-orange-700',
            'Confirmada' => 'bg-blue-50 text-blue-700',
            'Completada'   => 'bg-teal-50 text-teal-700',
            'Cancelada'  => 'bg-red-50 text-red-700',
        ];
        $hayFiltros = request()->hasAny(['estado', 'fecha', 'medico_id']) && collect(request()->only(['estado', 'fecha', 'medico_id']))->filter()->isNotEmpty();
    @endphp

    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Resumen</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Citas</h1>
                <p class="mt-2 text-slate-500">Consulta y programa las citas médicas de los pacientes.</p>
            </div>
            @can('crear-editar-registros')
            <a href="{{ route('admin.citas.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Agendar cita</a>
            @endcan
        </div>


        <section class="admin-panel overflow-hidden rounded-xl">
            {{-- Filtros --}}
            <form method="GET" action="{{ route('admin.citas.index') }}" class="grid gap-3 border-b border-slate-200 p-5 md:grid-cols-[1fr_1fr_1.4fr_auto] md:items-end">
                <label>
                    <span class="mb-1 block text-xs font-semibold text-slate-600">Estado</span>
                    <select class="admin-field" name="estado">
                        <option value="">Todos</option>
                        @foreach (\App\Models\Cita::ESTADOS as $estado)
                            <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="mb-1 block text-xs font-semibold text-slate-600">Fecha</span>
                    <input class="admin-field" type="date" name="fecha" value="{{ request('fecha') }}">
                </label>
                <label>
                    <span class="mb-1 block text-xs font-semibold text-slate-600">Médico</span>
                    <select class="admin-field" name="medico_id">
                        <option value="">Todos</option>
                        @foreach ($medicos as $medico)
                            <option value="{{ $medico->id_medico }}" @selected((string) request('medico_id') === (string) $medico->id_medico)>{{ $medico->usuario?->nombre_completo ?? 'Médico #' . $medico->id_medico }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">Filtrar</button>
                    @if ($hayFiltros)
                        <a href="{{ route('admin.citas.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="admin-table w-full min-w-[860px] text-sm">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha y hora</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Estado</th>
                            <th>Expediente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600">
                        @forelse ($citas as $cita)
                            <tr class="transition hover:bg-slate-50">
                                <td class="font-semibold text-slate-800">#{{ str_pad($cita->id_cita, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $cita->fecha?->format('d/m/Y') }} · {{ $cita->hora_corta }}</td>
                                <td>{{ $cita->paciente?->nombre_completo ?? 'N/D' }}</td>
                                <td>{{ $cita->medico?->usuario?->nombre_completo ?? 'N/D' }}</td>
                                <td><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $colorEstado[$cita->estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $cita->estado }}</span></td>
                                <td>
                                    @if ($cita->expediente)
                                        <a href="{{ route('admin.expedientes.show', $cita->expediente->id_expediente) }}" class="text-xs font-semibold text-teal-700 hover:text-teal-900">Ver expediente</a>
                                    @elseif ($cita->estado !== 'Cancelada')
                                        @can('crear-editar-registros')
                                        <a href="{{ route('admin.expedientes.create', ['cita' => $cita->id_cita]) }}" class="text-xs font-semibold text-orange-600 hover:text-orange-800">+ Registrar</a>
                                        @endcan
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.citas.show', $cita->id_cita) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Ver</a>
                                        @can('crear-editar-registros')
                                        <a href="{{ route('admin.citas.edit', $cita->id_cita) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-orange-400 hover:text-orange-700">Editar</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-500">
                                    {{ $hayFiltros ? 'No hay citas que coincidan con los filtros.' : 'No hay citas registradas aún.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4 text-sm text-slate-500">{{ $citas->count() }} {{ $citas->count() === 1 ? 'cita' : 'citas' }}</div>
        </section>
    </div>
@endsection
