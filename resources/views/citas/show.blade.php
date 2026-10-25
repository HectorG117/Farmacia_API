@extends('layouts.admin')

@section('content')
    @php
        $medico = $cita->medico;
        $paciente = $cita->paciente;
        $colorEstado = [
            'Pendiente'  => 'bg-orange-50 text-orange-700',
            'Confirmada' => 'bg-blue-50 text-blue-700',
            'Completada'   => 'bg-teal-50 text-teal-700',
            'Cancelada'  => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="mx-auto max-w-5xl">
        <div class="mb-8">
            <a href="{{ route('admin.citas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a citas</a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Cita #{{ str_pad($cita->id_cita, 4, '0', STR_PAD_LEFT) }}</h1>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $colorEstado[$cita->estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $cita->estado }}</span>
            </div>
            <p class="mt-2 text-slate-500">{{ $cita->fecha?->format('d/m/Y') }} a las {{ $cita->hora_corta }} hrs</p>
        </div>


        <div class="mb-6 grid gap-6 md:grid-cols-2">
            <section class="admin-panel rounded-xl p-6">
                <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Paciente</p>
                <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $paciente?->nombre_completo ?? 'N/D' }}</h2>
                <dl class="mt-3 space-y-1 text-sm text-slate-600">
                    <div><dt class="inline text-slate-500">Correo:</dt> <dd class="inline">{{ $paciente?->email ?? 'N/D' }}</dd></div>
                    <div><dt class="inline text-slate-500">Teléfono:</dt> <dd class="inline">{{ $paciente?->telefono ?? 'N/D' }}</dd></div>
                </dl>
            </section>

            <section class="admin-panel rounded-xl p-6">
                <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Médico</p>
                <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $medico?->usuario?->nombre_completo ?? 'N/D' }}</h2>
                <dl class="mt-3 space-y-1 text-sm text-slate-600">
                    <div><dt class="inline text-slate-500">Especialidad:</dt> <dd class="inline">{{ $medico?->especialidad?->nombre ?? 'N/D' }}</dd></div>
                    <div><dt class="inline text-slate-500">Consultorio:</dt> <dd class="inline">{{ $medico?->consultorio ?? 'N/D' }}</dd></div>
                </dl>
            </section>
        </div>

        <section class="admin-panel mb-6 rounded-xl p-6">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Motivo de consulta</p>
            <p class="mt-2 whitespace-pre-line text-slate-800">{{ $cita->motivo ?: 'Sin motivo registrado.' }}</p>
        </section>

        {{-- Expediente médico de la cita --}}
        <section class="admin-panel mb-6 rounded-xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Expediente médico</p>
                    @if ($cita->expediente)
                        <p class="mt-2 text-slate-800"><span class="font-semibold">Diagnóstico:</span> {{ \Illuminate\Support\Str::limit($cita->expediente->diagnostico, 140) }}</p>
                    @elseif ($cita->estado === 'Cancelada')
                        <p class="mt-2 text-sm text-slate-500">La cita fue cancelada; no lleva expediente.</p>
                    @else
                        <p class="mt-2 text-sm text-slate-500">Aún no se ha registrado el resultado clínico de esta cita.</p>
                    @endif
                </div>
                @if ($cita->expediente)
                    <a href="{{ route('admin.expedientes.show', $cita->expediente->id_expediente) }}" class="rounded-lg border border-teal-600 px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-50">Ver expediente</a>
                @elseif ($cita->puedeRegistrarExpediente())
                    @can('crear-editar-registros')
                    <a href="{{ route('admin.expedientes.create', ['cita' => $cita->id_cita]) }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">+ Registrar expediente</a>
                    @endcan
                @endif
            </div>
        </section>

        <div class="flex flex-wrap gap-3">
            @can('crear-editar-registros')
            <a href="{{ route('admin.citas.edit', $cita->id_cita) }}" class="rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">Editar</a>
            @endcan
            @unless ($cita->expediente)
                @can('eliminar-registros')
                <form action="{{ route('admin.citas.destroy', $cita->id_cita) }}" method="POST" onsubmit="return confirm('¿Eliminar esta cita? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
                </form>
                @endcan
            @endunless
        </div>
    </div>
@endsection
