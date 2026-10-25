@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-8">
            <a href="{{ route('admin.recetas-medicas.show', $receta->id_receta) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a la receta</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Editar receta #{{ $receta->id_receta }}</h1>
            <p class="mt-2 text-slate-500">Modifica los datos generales o el listado de productos e instrucciones.</p>
        </div>

        <form action="{{ route('admin.recetas-medicas.update', $receta->id_receta) }}" method="POST">
            @csrf
            @method('PUT')
            @include('recetas._form')

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">Guardar cambios</button>
                <a href="{{ route('admin.recetas-medicas.show', $receta->id_receta) }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
