@extends('layouts.admin')

@section('content')
    @php
        $hayFiltros = $buscar !== '' || request()->filled('especialidad_id') || request()->filled('estado');
    @endphp

    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Resumen</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Médicos</h1>
                <p class="mt-2 text-slate-500">Gestiona a los profesionales, sus especialidades y consultorios.</p>
            </div>
            @can('crear-editar-registros')
            <a href="{{ route('admin.medicos.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Registrar médico</a>
            @endcan
        </div>


        {{-- Filtros --}}
        <form method="GET" action="{{ route('admin.medicos.index') }}" class="admin-panel mb-6 grid gap-3 rounded-xl p-5 md:grid-cols-[2fr_1.2fr_1fr_auto] md:items-end">
            <label>
                <span class="mb-1 block text-xs font-semibold text-slate-600">Buscar</span>
                <input class="admin-field" type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre, correo, cédula o consultorio...">
            </label>
            <label>
                <span class="mb-1 block text-xs font-semibold text-slate-600">Especialidad</span>
                <select class="admin-field" name="especialidad_id">
                    <option value="">Todas</option>
                    @foreach ($especialidades as $especialidad)
                        <option value="{{ $especialidad->id_especialidad }}" @selected((string) request('especialidad_id') === (string) $especialidad->id_especialidad)>{{ $especialidad->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="mb-1 block text-xs font-semibold text-slate-600">Estado</span>
                <select class="admin-field" name="estado">
                    <option value="">Todos</option>
                    <option value="activos" @selected(request('estado') === 'activos')>Activos</option>
                    <option value="inactivos" @selected(request('estado') === 'inactivos')>Inactivos</option>
                </select>
            </label>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">Filtrar</button>
                @if ($hayFiltros)
                    <a href="{{ route('admin.medicos.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($medicos as $medico)
                @php
                    $u = $medico->usuario;
                    $iniciales = mb_strtoupper(mb_substr($u->nombre ?? 'M', 0, 1) . mb_substr($u->apellido ?? '', 0, 1));
                @endphp
                <section class="admin-panel flex flex-col rounded-xl p-5 {{ $medico->activo ? '' : 'opacity-70' }}">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-teal-50 text-base font-bold text-teal-700">{{ $iniciales }}</div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="truncate text-base font-bold text-slate-900">{{ $medico->nombre_completo }}</h3>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $medico->activo ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-500' }}">{{ $medico->activo ? 'Activo' : 'Inactivo' }}</span>
                            </div>
                            <p class="text-sm font-semibold text-orange-600">{{ $medico->especialidad->nombre ?? 'Sin especialidad' }}</p>
                        </div>
                    </div>

                    <dl class="mt-4 space-y-1 text-sm text-slate-600">
                        <div class="flex justify-between"><dt class="text-slate-500">Cédula</dt><dd class="font-semibold text-slate-800">{{ $medico->cedula }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Consultorio</dt><dd>{{ $medico->consultorio }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Correo</dt><dd class="truncate pl-3">{{ $u->email ?? 'N/D' }}</dd></div>
                    </dl>

                    <div class="mt-4 grid grid-cols-2 gap-2 text-center">
                        <div class="rounded-md bg-slate-50 px-2 py-2"><span class="block text-lg font-bold text-slate-900">{{ $medico->citas_proximas_count }}</span><span class="text-xs text-slate-500">Citas próximas</span></div>
                        <div class="rounded-md bg-slate-50 px-2 py-2"><span class="block text-lg font-bold text-slate-900">{{ $medico->recetas_count }}</span><span class="text-xs text-slate-500">Recetas</span></div>
                    </div>

                    <div class="mt-4 flex gap-2 border-t border-slate-200 pt-4">
                        <a href="{{ route('admin.medicos.show', $medico->id_medico) }}" class="flex-1 rounded-md border border-slate-200 px-2.5 py-1.5 text-center text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Ver perfil</a>
                        @can('crear-editar-registros')
                        <a href="{{ route('admin.medicos.edit', $medico->id_medico) }}" class="flex-1 rounded-md border border-slate-200 px-2.5 py-1.5 text-center text-xs font-semibold text-slate-600 hover:border-orange-400 hover:text-orange-700">Editar</a>
                        @endcan
                    </div>
                </section>
            @empty
                <div class="admin-panel col-span-full rounded-xl p-10 text-center">
                    <p class="text-slate-500">{{ $hayFiltros ? 'No hay médicos que coincidan con los filtros.' : 'No hay médicos registrados aún.' }}</p>
                    @unless ($hayFiltros)
                        @can('crear-editar-registros')
                        <a href="{{ route('admin.medicos.create') }}" class="mt-4 inline-block rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Registrar el primero</a>
                        @endcan
                    @endunless
                </div>
            @endforelse
        </div>
    </div>
@endsection
