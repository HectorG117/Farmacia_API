@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Registrar Nuevo Log</h1>
            <a href="{{ route('admin.logs.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver</a>
        </div>

        <section class="admin-panel rounded-xl p-6">
            <form action="{{ route('admin.logs.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">Usuario (Opcional)</label>
                    <select name="id_usuario" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="">-- Seleccionar usuario --</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id_usuario }}">{{ $usuario->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Acción</label>
                    <input type="text" name="accion" required placeholder="Ej. Inicio de sesión, Actualización de perfil" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Módulo</label>
                    <input type="text" name="modulo" required placeholder="Ej. Autenticación, Productos" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Dirección IP (Opcional)</label>
                    <input type="text" name="ip" placeholder="Ej. 127.0.0.1" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Fecha y Hora</label>
                    <input type="datetime-local" name="fecha" required value="{{ date('Y-m-d\TH:i') }}" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full rounded-lg bg-teal-700 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">Guardar Log</button>
            </form>
        </section>
    </div>
@endsection