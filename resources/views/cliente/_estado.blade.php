{{-- Etiqueta de color para el estado de una cita o receta. Variable: $estado --}}
@php
    $colores = [
        'Pendiente'  => 'bg-orange-50 text-orange-700',
        'Confirmada' => 'bg-blue-50 text-blue-700',
        'Completada' => 'bg-teal-50 text-teal-700',
        'Vigente'    => 'bg-teal-50 text-teal-700',
        'Surtida'    => 'bg-blue-50 text-blue-700',
        'Cancelada'  => 'bg-red-50 text-red-700',
    ];
@endphp
<span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $colores[$estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $estado }}</span>
