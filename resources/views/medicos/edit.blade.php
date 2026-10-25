@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-8">
            <a href="{{ route('admin.medicos.show', $medico->id_medico) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver al perfil</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Editar a {{ $medico->nombre_completo }}</h1>
            <p class="mt-2 text-slate-500">Actualiza sus datos personales, de acceso y profesionales.</p>
        </div>

        <form action="{{ route('admin.medicos.update', $medico->id_medico) }}" method="POST">
            @csrf
            @method('PUT')
            @include('medicos._form')

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">Guardar cambios</button>
                <a href="{{ route('admin.medicos.show', $medico->id_medico) }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
