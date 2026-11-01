@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('admin.recetas-medicas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a recetas</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Receta #{{ str_pad($receta->id_receta, 4, '0', STR_PAD_LEFT) }}</h1>
                <p class="mt-2 text-slate-500">Emitida el {{ $receta->fecha_emision?->format('d/m/Y') }} · Estado: <strong>{{ $receta->estado }}</strong></p>
            </div>
            <button type="button" onclick="window.print()" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">🖨 Imprimir</button>
        </div>


        @include('recetas._detalle')

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
