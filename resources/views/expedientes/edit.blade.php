@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-8">
            <a href="{{ route('admin.expedientes.show', $expediente->id_expediente) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver al expediente</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Editar expediente #{{ str_pad($expediente->id_expediente, 4, '0', STR_PAD_LEFT) }}</h1>
            <p class="mt-2 text-slate-500">Paciente: {{ $expediente->cita?->paciente?->nombre_completo ?? 'N/D' }}</p>
        </div>

        <form action="{{ route('admin.expedientes.update', $expediente->id_expediente) }}" method="POST">
            @csrf
            @method('PUT')
            @include('expedientes._form')

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">Guardar cambios</button>
                <a href="{{ route('admin.expedientes.show', $expediente->id_expediente) }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
