@extends('cliente.layout')

@section('title', 'Mis citas')

@section('content')
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-teal-700">Consultas</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Mis citas</h1>
        <p class="mt-2 text-slate-500">Todas tus citas médicas, de la más reciente a la más antigua.</p>
    </div>

    <section class="admin-panel overflow-hidden rounded-xl">
        <div class="overflow-x-auto">
            <table class="admin-table w-full min-w-[720px] text-sm">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Médico</th>
                        <th>Consultorio</th>
                        <th>Estado</th>
                        <th>Expediente</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="text-slate-600">
                    @forelse ($citas as $cita)
                        <tr class="transition hover:bg-slate-50">
                            <td class="font-semibold text-slate-800">{{ $cita->fecha?->format('d/m/Y') }} · {{ $cita->hora_corta }}</td>
                            <td>
                                {{ $cita->medico?->nombre_completo ?? 'N/D' }}
                                <span class="block text-xs text-slate-500">{{ $cita->medico?->especialidad?->nombre }}</span>
                            </td>
                            <td>{{ $cita->medico?->consultorio ?? 'N/D' }}</td>
                            <td>@include('cliente._estado', ['estado' => $cita->estado])</td>
                            <td>
                                @if ($cita->expediente)
                                    <a href="{{ route('cliente.expedientes.show', $cita->expediente->id_expediente) }}" class="text-xs font-semibold text-teal-700 hover:text-teal-900">Ver resultado</a>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td><a href="{{ route('cliente.citas.show', $cita->id_cita) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Ver</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">Aún no tienes citas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
