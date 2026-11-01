@extends('cliente.layout')

@section('title', 'Inicio')

@section('content')
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-teal-700">Hola, {{ auth('cliente')->user()->nombre }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Tu resumen de salud</h1>
        <p class="mt-2 text-slate-500">Consulta tus próximas citas, tus recetas vigentes y lo que tu médico registró en tu expediente.</p>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        @foreach ($totales as $etiqueta => $valor)
            <section class="admin-panel rounded-xl p-5">
                <span class="text-sm text-slate-500">{{ $etiqueta }}</span>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $valor }}</p>
            </section>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Próximas citas --}}
        <section class="admin-panel overflow-hidden rounded-xl">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <h2 class="text-lg font-bold text-slate-900">Próximas citas</h2>
                <a href="{{ route('cliente.citas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Ver todas</a>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse ($proximasCitas as $cita)
                    <a href="{{ route('cliente.citas.show', $cita->id_cita) }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm transition hover:bg-slate-50">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $cita->fecha?->format('d/m/Y') }} · {{ $cita->hora_corta }} hrs</p>
                            <p class="text-slate-500">{{ $cita->medico?->nombre_completo ?? 'Médico' }} · {{ $cita->medico?->especialidad?->nombre ?? '' }}</p>
                        </div>
                        @include('cliente._estado', ['estado' => $cita->estado])
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">No tienes citas próximas.</p>
                @endforelse
            </div>
        </section>

        {{-- Recetas vigentes --}}
        <section class="admin-panel overflow-hidden rounded-xl">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <h2 class="text-lg font-bold text-slate-900">Recetas vigentes</h2>
                <a href="{{ route('cliente.recetas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Ver todas</a>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse ($recetasVigentes as $receta)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-sm">
                        <div>
                            <p class="font-semibold text-slate-800">Receta #{{ str_pad($receta->id_receta, 4, '0', STR_PAD_LEFT) }} · {{ $receta->fecha_emision?->format('d/m/Y') }}</p>
                            <p class="text-slate-500">
                                {{ $receta->medico?->nombre_completo ?? 'Médico' }} ·
                                {{ $receta->productos_seleccionados }} de {{ $receta->detalles->count() }} productos elegidos
                                @if ($receta->productos_seleccionados) (${{ number_format($receta->total_compra, 2) }}) @endif
                            </p>
                        </div>
                        <a href="{{ route('cliente.recetas.edit', $receta->id_receta) }}" class="rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-800">Elegir productos</a>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">No tienes recetas vigentes.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Últimos expedientes --}}
    <section class="admin-panel mt-6 overflow-hidden rounded-xl">
        <div class="flex items-center justify-between border-b border-slate-200 p-5">
            <h2 class="text-lg font-bold text-slate-900">Últimas notas de tu expediente</h2>
            <a href="{{ route('cliente.expedientes.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Ver expediente completo</a>
        </div>
        <div class="divide-y divide-slate-200">
            @forelse ($ultimosExpedientes as $expediente)
                <a href="{{ route('cliente.expedientes.show', $expediente->id_expediente) }}" class="block px-5 py-3 text-sm transition hover:bg-slate-50">
                    <p class="font-semibold text-slate-800">{{ $expediente->fecha?->format('d/m/Y') }} · {{ $expediente->cita?->medico?->nombre_completo ?? 'Médico' }}</p>
                    <p class="text-slate-500">{{ \Illuminate\Support\Str::limit($expediente->diagnostico, 140) }}</p>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-500">Aún no hay registros en tu expediente.</p>
            @endforelse
        </div>
    </section>
@endsection
