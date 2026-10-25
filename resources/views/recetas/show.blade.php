@extends('layouts.admin')

@section('content')
    @php
        $medico = $receta->medico;
        $paciente = $receta->paciente;
    @endphp

    <div class="mx-auto max-w-5xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('admin.recetas-medicas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a recetas</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Receta #{{ str_pad($receta->id_receta, 4, '0', STR_PAD_LEFT) }}</h1>
                <p class="mt-2 text-slate-500">Emitida el {{ $receta->fecha_emision?->format('d/m/Y') }} · Estado: <strong>{{ $receta->estado }}</strong></p>
            </div>
            <button type="button" onclick="window.print()" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">🖨 Imprimir</button>
        </div>


        {{-- Médico y paciente --}}
        <div class="mb-6 grid gap-6 md:grid-cols-2">
            <section class="admin-panel rounded-xl p-6">
                <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Médico</p>
                <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $medico?->usuario?->nombre_completo ?? 'N/D' }}</h2>
                <dl class="mt-3 space-y-1 text-sm text-slate-600">
                    <div><dt class="inline text-slate-500">Especialidad:</dt> <dd class="inline">{{ $medico?->especialidad?->nombre ?? 'N/D' }}</dd></div>
                    <div><dt class="inline text-slate-500">Cédula profesional:</dt> <dd class="inline">{{ $medico?->cedula ?? 'N/D' }}</dd></div>
                    <div><dt class="inline text-slate-500">Consultorio:</dt> <dd class="inline">{{ $medico?->consultorio ?? 'N/D' }}</dd></div>
                </dl>
            </section>

            <section class="admin-panel rounded-xl p-6">
                <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Paciente</p>
                <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $paciente?->nombre_completo ?? 'N/D' }}</h2>
                <dl class="mt-3 space-y-1 text-sm text-slate-600">
                    <div><dt class="inline text-slate-500">Correo:</dt> <dd class="inline">{{ $paciente?->email ?? 'N/D' }}</dd></div>
                    <div><dt class="inline text-slate-500">Teléfono:</dt> <dd class="inline">{{ $paciente?->telefono ?? 'N/D' }}</dd></div>
                </dl>
            </section>
        </div>

        {{-- Diagnóstico e indicaciones --}}
        @if ($receta->diagnostico || $receta->indicaciones_generales)
            <section class="admin-panel mb-6 rounded-xl p-6">
                @if ($receta->diagnostico)
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Diagnóstico</p>
                    <p class="mt-1 mb-4 text-slate-800">{{ $receta->diagnostico }}</p>
                @endif
                @if ($receta->indicaciones_generales)
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">Indicaciones generales</p>
                    <p class="mt-1 whitespace-pre-line text-slate-800">{{ $receta->indicaciones_generales }}</p>
                @endif
            </section>
        @endif

        {{-- Productos recetados --}}
        <section class="admin-panel overflow-hidden rounded-xl">
            <div class="border-b border-slate-200 p-5">
                <h2 class="text-lg font-bold text-slate-900">Productos recetados</h2>
            </div>
            <div class="divide-y divide-slate-200">
                @foreach ($receta->detalles as $detalle)
                    @php $producto = $detalle->producto; @endphp
                    <div class="flex flex-col gap-4 p-5 md:flex-row">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-slate-50">
                            @if ($producto?->imagen)
                                <img src="{{ asset($producto->imagen) }}" alt="{{ $producto->nombre }}" class="max-h-16 max-w-full object-contain">
                            @else
                                <span class="text-xs text-slate-400">Sin imagen</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-bold text-slate-900">
                                        {{ $producto->nombre ?? 'Producto eliminado' }}
                                        @if ($producto?->trashed())<span class="ml-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600">Descontinuado</span>@endif
                                    </h3>
                                    <p class="text-sm text-slate-500">{{ $detalle->cantidad }} × ${{ number_format($producto->precio ?? 0, 2) }}</p>
                                </div>
                                <p class="font-bold text-teal-700">${{ number_format($detalle->cantidad * ($producto->precio ?? 0), 2) }}</p>
                            </div>
                            <div class="mt-3 grid gap-2 text-sm sm:grid-cols-3">
                                <div class="rounded-md bg-slate-50 px-3 py-2"><span class="block text-xs text-slate-500">Dosis</span><span class="font-semibold text-slate-800">{{ $detalle->dosis }}</span></div>
                                <div class="rounded-md bg-slate-50 px-3 py-2"><span class="block text-xs text-slate-500">Frecuencia</span><span class="font-semibold text-slate-800">{{ $detalle->frecuencia }}</span></div>
                                <div class="rounded-md bg-slate-50 px-3 py-2"><span class="block text-xs text-slate-500">Duración</span><span class="font-semibold text-slate-800">{{ $detalle->duracion }}</span></div>
                            </div>
                            @if ($detalle->instrucciones)
                                <p class="mt-3 rounded-md border-l-4 border-orange-300 bg-orange-50 px-3 py-2 text-sm text-slate-700">{{ $detalle->instrucciones }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-5 py-4">
                <span class="text-sm text-slate-500">Total estimado</span>
                <span class="text-lg font-bold text-teal-700">${{ number_format($receta->total_estimado, 2) }}</span>
            </div>
        </section>

        <div class="mt-6 flex flex-wrap gap-3">
            @can('crear-editar-registros')
            <a href="{{ route('admin.recetas-medicas.edit', $receta->id_receta) }}" class="rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">Editar</a>
            @endcan
            @can('eliminar-registros')
            <form action="{{ route('admin.recetas-medicas.destroy', $receta->id_receta) }}" method="POST" onsubmit="return confirm('¿Eliminar esta receta? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
            </form>
            @endcan
        </div>
    </div>
@endsection
