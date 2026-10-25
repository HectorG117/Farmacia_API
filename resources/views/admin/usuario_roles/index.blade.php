@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Asignación de Roles</h1>
    <a href="{{ route('admin.usuario-roles.create') }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">+ Asignar Rol</a>
</div>

<div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                <th class="p-4">Usuario ID</th>
                <th class="p-4">Rol ID</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($usuarioRoles as $item)
            <tr>
                <td class="p-4">{{ $item->id_usuario }}</td>
                <td class="p-4">{{ $item->id_rol }}</td>
                <td class="p-4">
                    <form action="{{ route('admin.usuario-roles.destroy', ['id_usuario' => $item->id_usuario, 'id_rol' => $item->id_rol]) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="p-4 text-center text-slate-400">No hay asignaciones registradas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection