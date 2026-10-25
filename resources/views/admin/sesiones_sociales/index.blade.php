@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Sesiones Sociales</h1>
    @can('crear-editar-registros')
        <a href="{{ route('admin.sesiones-sociales.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">+ Vincular cuenta</a>
    @endcan
</div>

<div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                <th class="p-4">ID</th>
                <th class="p-4">Usuario</th>
                <th class="p-4">Proveedor</th>
                <th class="p-4">Identificador del proveedor</th>
                <th class="p-4">Fecha de vinculación</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($sesiones as $sesion)
            <tr>
                <td class="p-4 font-mono">{{ $sesion->id_sesion }}</td>
                <td class="p-4">
                    {{ $sesion->usuario ? trim($sesion->usuario->nombre . ' ' . $sesion->usuario->apellido) : 'Usuario #' . $sesion->id_usuario }}
                </td>
                <td class="p-4">{{ $sesion->proveedor }}</td>
                <td class="p-4 font-mono">{{ $sesion->provider_id }}</td>
                <td class="p-4 text-xs text-slate-500">{{ $sesion->fecha_vinculacion }}</td>
                <td class="p-4">
                    @can('eliminar-registros')
                        <form action="{{ route('admin.sesiones-sociales.destroy', $sesion->id_sesion) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-slate-400">No hay sesiones sociales vinculadas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
