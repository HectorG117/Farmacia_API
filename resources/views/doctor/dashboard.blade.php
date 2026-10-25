@extends('doctor.layout')

@section('title', 'Mis citas')

@section('content')
<div class="mb-8">
    <p class="text-xs font-bold uppercase tracking-[.18em] text-teal-700">Atención clínica</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Mis citas</h1>
    <p class="mt-2 text-slate-500">Solo se muestran las citas asignadas a tu perfil médico.</p>
</div>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase text-slate-500">
                <th class="p-4">Fecha y hora</th>
                <th class="p-4">Paciente</th>
                <th class="p-4">Motivo</th>
                <th class="p-4">Estado</th>
                <th class="p-4">Atención</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
            @forelse ($citas as $cita)
                <tr>
                    <td class="p-4 whitespace-nowrap">{{ $cita->fecha?->format('d/m/Y') }} {{ $cita->hora_corta }}</td>
                    <td class="p-4">
                        {{ $cita->paciente->nombre }} {{ $cita->paciente->apellido }}
                        <span class="block text-xs text-slate-500">{{ $cita->paciente->email }}</span>
                    </td>
                    <td class="p-4">{{ $cita->motivo ?: 'Sin motivo especificado' }}</td>
                    <td class="p-4">{{ $cita->estado }}</td>
                    <td class="p-4">
                        <div class="flex flex-col items-start gap-2">
                            <form action="{{ route('doctor.citas.update', $cita->id_cita) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="estado" aria-label="Estado de cita {{ $cita->id_cita }}" class="rounded-md border border-slate-300 p-2 text-xs">
                                    @foreach (\App\Models\Cita::ESTADOS as $estado)
                                        <option value="{{ $estado }}" @selected($cita->estado === $estado)>{{ $estado }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="rounded-md bg-teal-700 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-800">Actualizar</button>
                            </form>
                            @if ($cita->expediente || $cita->estado !== 'Cancelada')
                                <a href="{{ route('doctor.expedientes.edit', $cita->id_cita) }}" class="text-xs font-semibold text-teal-700 hover:underline">
                                    {{ $cita->expediente ? 'Ver / editar expediente' : 'Crear expediente' }}
                                </a>
                            @else
                                <span class="text-xs text-slate-400">Cita cancelada, sin expediente</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-500">No tienes citas asignadas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
