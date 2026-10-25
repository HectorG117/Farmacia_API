@extends('doctor.layout')

@section('title', 'Expediente médico')

@section('content')
<div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6 flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.18em] text-teal-700">Expediente clínico</p>
            <h1 class="mt-2 text-xl font-bold text-slate-900">{{ $expediente ? 'Actualizar expediente' : 'Crear expediente' }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $cita->paciente->nombre }} {{ $cita->paciente->apellido }} · Cita del {{ $cita->fecha?->format('d/m/Y') }} a las {{ $cita->hora_corta }}
            </p>
        </div>
        <a href="{{ route('doctor.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Mis citas</a>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('doctor.expedientes.update', $cita->id_cita) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        <label class="block">
            <span class="mb-1 block text-sm font-semibold text-slate-700">Diagnóstico</span>
            <textarea name="diagnostico" rows="4" required class="w-full rounded-md border border-slate-300 p-3 text-sm focus:border-teal-500 focus:ring-teal-500">{{ old('diagnostico', $expediente?->diagnostico) }}</textarea>
        </label>
        <label class="block">
            <span class="mb-1 block text-sm font-semibold text-slate-700">Tratamiento</span>
            <textarea name="tratamiento" rows="4" required class="w-full rounded-md border border-slate-300 p-3 text-sm focus:border-teal-500 focus:ring-teal-500">{{ old('tratamiento', $expediente?->tratamiento) }}</textarea>
        </label>
        <label class="block">
            <span class="mb-1 block text-sm font-semibold text-slate-700">Observaciones</span>
            <textarea name="observaciones" rows="3" class="w-full rounded-md border border-slate-300 p-3 text-sm focus:border-teal-500 focus:ring-teal-500">{{ old('observaciones', $expediente?->observaciones) }}</textarea>
        </label>
        @unless ($expediente)
            <p class="rounded-md bg-teal-50 px-3 py-2 text-xs text-teal-800">Al guardar el expediente, la cita se marcará como <strong>Completada</strong>.</p>
        @endunless
        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
            <a href="{{ route('doctor.dashboard') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Cancelar</a>
            <button type="submit" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Guardar expediente</button>
        </div>
    </form>
</div>
@endsection
