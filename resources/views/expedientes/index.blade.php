@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Resumen</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Expedientes médicos</h1>
                <p class="mt-2 text-slate-500">Resultado clínico registrado en cada cita atendida.</p>
            </div>
            @can('crear-editar-registros')
            <a href="{{ route('admin.expedientes.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Nuevo expediente</a>
            @endcan
        </div>


        <section class="admin-panel overflow-hidden rounded-xl">
            <form method="GET" action="{{ route('admin.expedientes.index') }}" class="flex flex-col gap-3 border-b border-slate-200 p-5 md:flex-row md:items-center">
                <div class="relative w-full md:max-w-sm">
                    <span class="absolute left-3 top-2.5 text-slate-400">⌕</span>
                    <input class="admin-field pl-9" type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar por paciente o diagnóstico..." aria-label="Buscar expediente">
                </div>
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">Buscar</button>
                @if ($buscar !== '')
                    <a href="{{ route('admin.expedientes.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-600">Limpiar</a>
                @endif
                <span class="text-sm text-slate-500 md:ml-auto">{{ $expedientes->count() }} {{ $expedientes->count() === 1 ? 'expediente' : 'expedientes' }}</span>
            </form>

            <div class="overflow-x-auto">
                <table class="admin-table w-full min-w-[860px] text-sm">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Cita</th>
                            <th>Diagnóstico</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600">
                        @forelse ($expedientes as $expediente)
                            @php $cita = $expediente->cita; @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="font-semibold text-slate-800">#{{ str_pad($expediente->id_expediente, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $expediente->fecha?->format('d/m/Y H:i') }}</td>
                                <td>{{ $cita?->paciente?->nombre_completo ?? 'N/D' }}</td>
                                <td>{{ $cita?->medico?->usuario?->nombre_completo ?? 'N/D' }}</td>
                                <td>
                                    @if ($cita)
                                        <a href="{{ route('admin.citas.show', $cita->id_cita) }}" class="font-semibold text-teal-700 hover:text-teal-900">#{{ str_pad($cita->id_cita, 4, '0', STR_PAD_LEFT) }}</a>
                                    @endif
                                </td>
                                <td class="max-w-xs">{{ \Illuminate\Support\Str::limit($expediente->diagnostico, 70) }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.expedientes.show', $expediente->id_expediente) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Ver</a>
                                        @can('crear-editar-registros')
                                        <a href="{{ route('admin.expedientes.edit', $expediente->id_expediente) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-orange-400 hover:text-orange-700">Editar</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-500">
                                    {{ $buscar !== '' ? 'No hay expedientes que coincidan con la búsqueda.' : 'No hay expedientes registrados aún.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
