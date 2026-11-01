@extends('cliente.layout')

@section('title', 'Mis recetas')

@section('content')
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-teal-700">Prescripciones</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Mis recetas</h1>
        <p class="mt-2 text-slate-500">En las recetas vigentes puedes elegir qué productos comprar y en qué cantidad.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        @forelse ($recetas as $receta)
            <section class="admin-panel flex flex-col rounded-xl p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-slate-900">Receta #{{ str_pad($receta->id_receta, 4, '0', STR_PAD_LEFT) }}</h2>
                        <p class="text-sm text-slate-500">{{ $receta->fecha_emision?->format('d/m/Y') }} · {{ $receta->medico?->nombre_completo ?? 'Médico' }}</p>
                    </div>
                    @include('cliente._estado', ['estado' => $receta->estado])
                </div>

                @if ($receta->diagnostico)
                    <p class="mt-3 text-sm text-slate-600"><span class="font-semibold">Diagnóstico:</span> {{ $receta->diagnostico }}</p>
                @endif

                <ul class="mt-3 space-y-1 text-sm text-slate-600">
                    @foreach ($receta->detalles as $detalle)
                        <li class="flex justify-between gap-3">
                            <span>{{ $detalle->producto->nombre ?? 'Producto' }} <span class="text-slate-400">× {{ $detalle->cantidad }}</span></span>
                            @if ($detalle->comprar)
                                <span class="shrink-0 text-xs font-semibold text-teal-700">🛒 {{ $detalle->cantidad_compra }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                    <span class="text-sm text-slate-500">Tu compra: <strong class="text-teal-700">${{ number_format($receta->total_compra, 2) }}</strong></span>
                    <div class="flex gap-2">
                        <a href="{{ route('cliente.recetas.show', $receta->id_receta) }}" class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Ver receta</a>
                        @if ($receta->esEditablePorPaciente())
                            <a href="{{ route('cliente.recetas.edit', $receta->id_receta) }}" class="rounded-md bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-800">Elegir productos</a>
                        @endif
                    </div>
                </div>
            </section>
        @empty
            <div class="admin-panel col-span-full rounded-xl p-10 text-center text-slate-500">Aún no tienes recetas. Aparecerán aquí cuando tu médico te emita una.</div>
        @endforelse
    </div>
@endsection
