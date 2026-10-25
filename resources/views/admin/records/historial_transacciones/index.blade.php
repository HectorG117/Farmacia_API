@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Historial de Transacciones</h1>
    <a href="{{ route('admin.historial-transacciones.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">+ Nueva Transacción</a>
</div>

<div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                <th class="p-4">ID</th>
                <th class="p-4">Usuario</th>
                <th class="p-4">Carrito</th>
                <th class="p-4">Total</th>
                <th class="p-4">Estado</th>
                <th class="p-4">Fecha</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($transacciones as $t)
            <tr>
                <td class="p-4 font-mono">{{ $t->id_transaccion }}</td>
                <td class="p-4">{{ $t->id_usuario }}</td>
                <td class="p-4">{{ $t->id_carrito }}</td>
                <td class="p-4 font-semibold text-emerald-600">${{ number_format($t->total, 2) }}</td>
                <td class="p-4"><span class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-xs">{{ $t->estado }}</span></td>
                <td class="p-4 text-xs text-slate-500">{{ $t->fecha }}</td>
                <td class="p-4">
                    <form action="{{ route('admin.historial-transacciones.destroy', $t->id_transaccion) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="p-4 text-center text-slate-400">No hay transacciones registradas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection