@extends('cliente.layout')

@section('title', 'Receta')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('cliente.recetas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Mis recetas</a>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Receta #{{ str_pad($receta->id_receta, 4, '0', STR_PAD_LEFT) }}</h1>
                    @include('cliente._estado', ['estado' => $receta->estado])
                </div>
                <p class="mt-2 text-slate-500">Emitida el {{ $receta->fecha_emision?->format('d/m/Y') }}</p>
            </div>
            <div class="flex gap-3">
                @if ($receta->esEditablePorPaciente())
                    <a href="{{ route('cliente.recetas.edit', $receta->id_receta) }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">🛒 Elegir productos a comprar</a>
                @endif
                <button type="button" onclick="window.print()" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">🖨 Imprimir</button>
            </div>
        </div>

        @include('recetas._detalle')
    </div>
@endsection
