@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-8">
            <a href="{{ route('admin.recetas-medicas.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a recetas</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Nueva receta médica</h1>
            <p class="mt-2 text-slate-500">Selecciona al médico, al paciente y agrega los productos con sus instrucciones de uso.</p>
        </div>

        <form action="{{ route('admin.recetas-medicas.store') }}" method="POST">
            @csrf
            @include('recetas._form')

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">Guardar receta</button>
                <a href="{{ route('admin.recetas-medicas.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
