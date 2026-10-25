@extends('layouts.admin')

@section('content')
    @php
        $cita = $expediente->cita;
        $medico = $cita?->medico;
        $paciente = $cita?->paciente;
    @endphp

    <div class="mx-auto max-w-5xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('admin.expedientes.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a expedientes</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Expediente #{{ str_pad($expediente->id_expediente, 4, '0', STR_PAD_LEFT) }}</h1>
                <p class="mt-2 text-slate-500">
                    Atendido el {{ $expediente->fecha?->format('d/m/Y \a \l\a\s H:i') }}
                    @if ($cita) · <a href="{{ route('admin.citas.show', $cita->id_cita) }}" class="font-semibold text-teal-700 hover:text-teal-900">Cita #{{ str_pad($cita->id_cita, 4, '0', STR_PAD_LEFT) }}</a>@endif
                </p>
            </div>
            <div class="flex gap-3">
                @if ($medico && $paciente)
                    @can('crear-editar-registros')
                    <a href="{{ route('admin.recetas-medicas.create', ['medico_id' => $medico->id_medico, 'paciente_id' => $paciente->id_usuario, 'diagnostico' => \Illuminate\Support\Str::limit($expediente->diagnostico, 250, '')]) }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">℞ Crear receta</a>
                    @endcan
                @endif
                <button type="button" onclick="window.print()" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">🖨 Imprimir</button>
            </div>
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
                    <div><dt class="inline text-slate-500">Cédula profesional:</dt> <dd class="inline">{{ $medico?->cedula ?? 'N/D' }}</dd></div>
                </dl>
            </section>
        </div>

        <section class="admin-panel mb-6 divide-y divide-slate-200 rounded-xl">
            @if ($cita?->motivo)
                <div class="p-6">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Motivo de consulta</p>
                    <p class="mt-2 whitespace-pre-line text-slate-800">{{ $cita->motivo }}</p>
                </div>
            @endif
            <div class="p-6">
                <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Diagnóstico</p>
                <p class="mt-2 whitespace-pre-line text-slate-800">{{ $expediente->diagnostico }}</p>
            </div>
            <div class="p-6">
                <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Tratamiento</p>
                <p class="mt-2 whitespace-pre-line text-slate-800">{{ $expediente->tratamiento }}</p>
            </div>
            <div class="p-6">
                <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Observaciones</p>
                <p class="mt-2 whitespace-pre-line text-slate-800">{{ $expediente->observaciones ?: 'Sin observaciones.' }}</p>
            </div>
        </section>

        <div class="flex flex-wrap gap-3">
            @can('crear-editar-registros')
            <a href="{{ route('admin.expedientes.edit', $expediente->id_expediente) }}" class="rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">Editar</a>
            @endcan
            @can('eliminar-registros')
            <form action="{{ route('admin.expedientes.destroy', $expediente->id_expediente) }}" method="POST" onsubmit="return confirm('¿Eliminar este expediente? La cita volverá a quedar como Confirmada.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
            </form>
            @endcan
        </div>
    </div>
@endsection
