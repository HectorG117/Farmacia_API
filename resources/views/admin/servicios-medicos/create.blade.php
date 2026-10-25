@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Crear Servicio Médico</h1>
            <a href="{{ route('admin.servicios-medicos.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver</a>
        </div>

        <section class="admin-panel rounded-xl p-6">
            <form action="{{ route('admin.servicios-medicos.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nombre del Servicio</label>
                    <input type="text" name="nombre" required placeholder="Ej. Consulta General, Electrocardiograma" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Costo ($)</label>
                    <input type="number" step="0.01" name="costo" required placeholder="0.00" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Descripción breve del servicio" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none"></textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-teal-700 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">Guardar Servicio</button>
            </form>
        </section>
    </div>
@endsection