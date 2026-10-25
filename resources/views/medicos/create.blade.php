@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-8">
            <a href="{{ route('admin.medicos.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a médicos</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Registrar médico</h1>
            <p class="mt-2 text-slate-500">Crea la cuenta del médico (o vincula una existente) y captura sus datos profesionales.</p>
        </div>

        <form action="{{ route('admin.medicos.store') }}" method="POST">
            @csrf
            @include('medicos._form')

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">Guardar médico</button>
                <a href="{{ route('admin.medicos.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
