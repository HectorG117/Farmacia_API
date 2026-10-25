@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Resumen</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $definition['title'] }}</h1>
                <p class="mt-2 text-slate-500">Registros almacenados en el sistema.</p>
            </div>
            <a href="{{ route('admin.' . $module . '.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Nuevo registro</a>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <section class="admin-panel overflow-hidden rounded-xl">
            <div class="overflow-x-auto">
                <table class="admin-table w-full min-w-[720px] text-sm">
                    <thead><tr>@foreach ($definition['columns'] as $column)<th>{{ $column['label'] }}</th>@endforeach<th>Acciones</th></tr></thead>
                    <tbody class="text-slate-600">
                        @forelse ($records as $record)
                            <tr class="transition hover:bg-slate-50">
                                @foreach ($definition['columns'] as $column)
                                    <td>{{ ($column['value'])($record) }}</td>
                                @endforeach
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.' . $module . '.edit', $record->getKey()) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Editar</a>
                                        <form method="POST" action="{{ route('admin.' . $module . '.destroy', $record->getKey()) }}" onsubmit="return confirm('¿Eliminar este registro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-red-400 hover:text-red-700">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($definition['columns']) + 1 }}" class="py-10 text-center text-slate-500">No hay registros para mostrar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4">{{ $records->links() }}</div>
        </section>
    </div>
@endsection