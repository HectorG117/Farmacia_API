@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-7xl">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-teal-100 p-4 text-sm font-semibold text-teal-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Historial de Logs</h1>
                <p class="mt-2 max-w-2xl text-slate-500">Registro de auditoría y actividades realizadas en el sistema.</p>
            </div>
        </div>

        <section class="admin-panel rounded-xl p-6">
            <div class="overflow-x-auto">
                <table class="admin-table w-full min-w-[640px] text-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Módulo</th>
                            <th>Acción</th>
                            <th>IP</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="font-semibold text-slate-800">{{ $log->id_log ?? $log->id }}</td>
                                <td class="font-medium text-slate-900">
                                    {{ $log->usuario->nombre ?? $log->usuario->name ?? 'Sistema' }}
                                </td>
                                <td>
                                    <span class="rounded-md bg-teal-50 px-2 py-1 text-xs font-semibold text-teal-700 border border-teal-200">
                                        {{ $log->modulo ?? 'General' }}
                                    </span>
                                </td>
                                <td>{{ $log->accion }}</td>
                                <td class="font-mono text-xs text-slate-500">{{ $log->ip ?? '127.0.0.1' }}</td>
                                <td class="text-slate-500">
                                    {{ $log->fecha ? $log->fecha->format('Y-m-d H:i:s') : 'Sin fecha' }}
                                </td>
                                <td>
                                    <form action="{{ route('admin.logs.destroy', $log->id_log ?? $log->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro de log?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-slate-500">No hay registros de auditoría en la base de datos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection