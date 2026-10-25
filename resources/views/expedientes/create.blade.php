@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-8">
            <a href="{{ $citaSeleccionada ? route('admin.citas.show', $citaSeleccionada) : route('admin.expedientes.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Registrar expediente médico</h1>
            <p class="mt-2 text-slate-500">Registra el resultado clínico de una cita: diagnóstico, tratamiento y observaciones.</p>
        </div>

        <form action="{{ route('admin.expedientes.store') }}" method="POST">
            @csrf
            @include('expedientes._form')

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">Guardar expediente</button>
                <a href="{{ route('admin.expedientes.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
