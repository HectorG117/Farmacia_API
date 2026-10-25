@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-8">
            <a href="{{ route('admin.citas.show', $cita->id_cita) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a la cita</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Editar cita #{{ str_pad($cita->id_cita, 4, '0', STR_PAD_LEFT) }}</h1>
            <p class="mt-2 text-slate-500">Reprograma la cita o cambia su estado.</p>
        </div>

        <form action="{{ route('admin.citas.update', $cita->id_cita) }}" method="POST">
            @csrf
            @method('PUT')
            @include('citas._form')

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">Guardar cambios</button>
                <a href="{{ route('admin.citas.show', $cita->id_cita) }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
