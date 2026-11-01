@extends('cliente.layout')

@section('title', 'Cita')

@section('content')
    @php $medico = $cita->medico; @endphp

    <div class="mx-auto max-w-4xl">
        <div class="mb-8">
            <a href="{{ route('cliente.citas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Mis citas</a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Cita del {{ $cita->fecha?->format('d/m/Y') }}</h1>
                @include('cliente._estado', ['estado' => $cita->estado])
            </div>
            <p class="mt-2 text-slate-500">A las {{ $cita->hora_corta }} hrs</p>
        </div>

        <section class="admin-panel mb-6 rounded-xl p-6">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Médico</p>
            <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $medico?->nombre_completo ?? 'N/D' }}</h2>
            <dl class="mt-3 grid gap-1 text-sm text-slate-600 sm:grid-cols-2">
                <div><dt class="inline text-slate-500">Especialidad:</dt> <dd class="inline">{{ $medico?->especialidad?->nombre ?? 'N/D' }}</dd></div>
                <div><dt class="inline text-slate-500">Consultorio:</dt> <dd class="inline">{{ $medico?->consultorio ?? 'N/D' }}</dd></div>
            </dl>
        </section>

        <section class="admin-panel mb-6 rounded-xl p-6">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Motivo de consulta</p>
            <p class="mt-2 whitespace-pre-line text-slate-800">{{ $cita->motivo ?: 'Sin motivo registrado.' }}</p>
        </section>

        <section class="admin-panel rounded-xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Resultado de la consulta</p>
                    @if ($cita->expediente)
                        <p class="mt-2 text-slate-800"><span class="font-semibold">Diagnóstico:</span> {{ \Illuminate\Support\Str::limit($cita->expediente->diagnostico, 160) }}</p>
                    @elseif ($cita->estado === 'Cancelada')
                        <p class="mt-2 text-sm text-slate-500">La cita fue cancelada.</p>
                    @else
                        <p class="mt-2 text-sm text-slate-500">Tu médico aún no registra el resultado de esta cita.</p>
                    @endif
                </div>
                @if ($cita->expediente)
                    <a href="{{ route('cliente.expedientes.show', $cita->expediente->id_expediente) }}" class="rounded-lg border border-teal-600 px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-50">Ver expediente</a>
                @endif
            </div>
        </section>
    </div>
@endsection
