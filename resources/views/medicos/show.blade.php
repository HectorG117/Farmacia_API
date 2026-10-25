@extends('layouts.admin')

@section('content')
    @php
        $u = $medico->usuario;
        $iniciales = mb_strtoupper(mb_substr($u->nombre ?? 'M', 0, 1) . mb_substr($u->apellido ?? '', 0, 1));
        $colorEstado = [
            'Pendiente'  => 'bg-orange-50 text-orange-700',
            'Confirmada' => 'bg-blue-50 text-blue-700',
        ];
    @endphp

    <div class="mx-auto max-w-6xl">
        <div class="mb-8">
            <a href="{{ route('admin.medicos.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a médicos</a>
        </div>


        {{-- Encabezado del perfil --}}
        <section class="admin-panel mb-6 flex flex-col gap-6 rounded-xl p-6 md:flex-row md:items-center lg:p-8">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-teal-50 text-2xl font-bold text-teal-700">{{ $iniciales }}</div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ $medico->nombre_completo }}</h1>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $medico->activo ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-500' }}">{{ $medico->activo ? 'Activo' : 'Inactivo' }}</span>
                </div>
                <p class="mt-1 font-semibold text-orange-600">{{ $medico->especialidad->nombre ?? 'Sin especialidad' }}</p>
                <dl class="mt-3 grid gap-x-6 gap-y-1 text-sm text-slate-600 sm:grid-cols-2">
                    <div><dt class="inline text-slate-500">Cédula profesional:</dt> <dd class="inline font-semibold text-slate-800">{{ $medico->cedula }}</dd></div>
                    <div><dt class="inline text-slate-500">Consultorio:</dt> <dd class="inline">{{ $medico->consultorio }}</dd></div>
                    <div><dt class="inline text-slate-500">Correo:</dt> <dd class="inline">{{ $u->email ?? 'N/D' }}</dd></div>
                    <div><dt class="inline text-slate-500">Teléfono:</dt> <dd class="inline">{{ $u->telefono ?? 'N/D' }}</dd></div>
                </dl>
            </div>
        </section>

        {{-- Estadísticas --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($estadisticas as $etiqueta => $valor)
                <section class="admin-panel rounded-xl p-5">
                    <span class="text-sm text-slate-500">{{ $etiqueta }}</span>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $valor }}</p>
                </section>
            @endforeach
        </div>

        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            {{-- Próximas citas --}}
            <section class="admin-panel overflow-hidden rounded-xl">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <h2 class="text-lg font-bold text-slate-900">Próximas citas</h2>
                    <a href="{{ route('admin.citas.index', ['medico_id' => $medico->id_medico]) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Ver todas</a>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse ($proximasCitas as $cita)
                        <a href="{{ route('admin.citas.show', $cita->id_cita) }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm transition hover:bg-slate-50">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $cita->paciente?->nombre_completo ?? 'N/D' }}</p>
                                <p class="text-slate-500">{{ $cita->fecha?->format('d/m/Y') }} · {{ $cita->hora_corta }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $colorEstado[$cita->estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $cita->estado }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Sin citas próximas.</p>
                    @endforelse
                </div>
            </section>

            {{-- Últimas recetas --}}
            <section class="admin-panel overflow-hidden rounded-xl">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <h2 class="text-lg font-bold text-slate-900">Últimas recetas emitidas</h2>
                    @can('crear-editar-registros')
                    <a href="{{ route('admin.recetas-medicas.create', ['medico_id' => $medico->id_medico]) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">+ Nueva receta</a>
                    @endcan
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse ($ultimasRecetas as $receta)
                        <a href="{{ route('admin.recetas-medicas.show', $receta->id_receta) }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm transition hover:bg-slate-50">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $receta->paciente?->nombre_completo ?? 'N/D' }}</p>
                                <p class="text-slate-500">#{{ str_pad($receta->id_receta, 4, '0', STR_PAD_LEFT) }} · {{ $receta->fecha_emision?->format('d/m/Y') }} · {{ $receta->detalles_count }} {{ $receta->detalles_count === 1 ? 'producto' : 'productos' }}</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-500">{{ $receta->estado }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Aún no ha emitido recetas.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="flex flex-wrap gap-3">
            @can('crear-editar-registros')
            <a href="{{ route('admin.medicos.edit', $medico->id_medico) }}" class="rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">Editar</a>
            @endcan
            @can('eliminar-registros')
            <form action="{{ route('admin.medicos.destroy', $medico->id_medico) }}" method="POST" onsubmit="return confirm('¿Eliminar el perfil de médico? Su cuenta de usuario se conservará.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar perfil</button>
            </form>
            @endcan
        </div>
    </div>
@endsection
