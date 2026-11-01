@extends('cliente.layout')

@section('title', 'Expediente')

@section('content')
    @php
        $cita = $expediente->cita;
        $medico = $cita?->medico;
    @endphp

    <div class="mx-auto max-w-4xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('cliente.expedientes.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Mi expediente</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Consulta del {{ $expediente->fecha?->format('d/m/Y') }}</h1>
                <p class="mt-2 text-slate-500">
                    {{ $medico?->nombre_completo ?? 'Médico' }} · {{ $medico?->especialidad?->nombre ?? '' }}
                    @if ($cita) · <a href="{{ route('cliente.citas.show', $cita->id_cita) }}" class="font-semibold text-teal-700 hover:text-teal-900">Ver cita</a>@endif
                </p>
            </div>
            <button type="button" onclick="window.print()" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">🖨 Imprimir</button>
        </div>

        <section class="admin-panel divide-y divide-slate-200 rounded-xl">
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
    </div>
@endsection
