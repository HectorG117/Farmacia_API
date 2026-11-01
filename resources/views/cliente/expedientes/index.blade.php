@extends('cliente.layout')

@section('title', 'Mi expediente')

@section('content')
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-teal-700">Historial clínico</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Mi expediente médico</h1>
        <p class="mt-2 text-slate-500">Lo que tus médicos registraron en cada consulta: diagnóstico, tratamiento y observaciones.</p>
    </div>

    <div class="space-y-4">
        @forelse ($expedientes as $expediente)
            @php $medico = $expediente->cita?->medico; @endphp
            <a href="{{ route('cliente.expedientes.show', $expediente->id_expediente) }}" class="admin-panel block rounded-xl p-5 transition hover:border-teal-300">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-orange-600">{{ $expediente->fecha?->format('d/m/Y') }}</p>
                        <h2 class="mt-1 font-bold text-slate-900">{{ \Illuminate\Support\Str::limit($expediente->diagnostico, 120) }}</h2>
                    </div>
                    <span class="text-sm text-slate-500">{{ $medico?->nombre_completo ?? 'Médico' }} · {{ $medico?->especialidad?->nombre }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-600"><span class="font-semibold">Tratamiento:</span> {{ \Illuminate\Support\Str::limit($expediente->tratamiento, 160) }}</p>
            </a>
        @empty
            <div class="admin-panel rounded-xl p-10 text-center text-slate-500">Aún no hay registros en tu expediente. Aparecerán cuando tu médico registre el resultado de una consulta.</div>
        @endforelse
    </div>
@endsection
