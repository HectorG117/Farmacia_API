@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="mb-2 text-sm font-semibold text-teal-700">Domingo, 06 de septiembre de 2026</p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Resumen operativo</h1>
                <p class="mt-2 max-w-2xl text-slate-500">Una vista general de los modulos administrativos de Farmacia Vital.</p>
            </div>
            <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Nuevo usuario</a>
        </div>

        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['label' => 'Usuarios activos', 'value' => '1,248', 'trend' => '+12.4%', 'tone' => 'teal'], ['label' => 'Citas del dia', 'value' => '36', 'trend' => '+5.2%', 'tone' => 'orange'], ['label' => 'Servicios activos', 'value' => '18', 'trend' => 'Estable', 'tone' => 'blue'], ['label' => 'Transacciones', 'value' => '284', 'trend' => '+8.7%', 'tone' => 'violet']] as $stat)
                <section class="admin-panel rounded-xl p-5">
                    <div class="mb-5 flex items-center justify-between">
                        <span class="text-sm text-slate-500">{{ $stat['label'] }}</span>
                        <span class="h-2.5 w-2.5 rounded-full bg-{{ $stat['tone'] }}-400"></span>
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-xs font-semibold text-teal-700">{{ $stat['trend'] }} <span class="font-normal text-slate-400">vs. mes anterior</span></p>
                </section>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
            <section class="admin-panel rounded-xl p-6">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Actividad reciente</h2>
                        <p class="mt-1 text-sm text-slate-500">Ultimos movimientos registrados en el sistema</p>
                    </div>
                    <a href="{{ route('admin.logs.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Ver logs</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="admin-table w-full min-w-[540px] text-sm">
                        <thead><tr><th>Usuario</th><th>Accion</th><th>Modulo</th><th>Fecha</th></tr></thead>
                        <tbody class="text-slate-600">
                            <tr><td class="font-semibold text-slate-800">Ana Martinez</td><td>Actualizo registro</td><td>Usuarios</td><td>09:42</td></tr>
                            <tr><td class="font-semibold text-slate-800">Carlos Rojas</td><td>Creo una cita</td><td>Citas</td><td>09:18</td></tr>
                            <tr><td class="font-semibold text-slate-800">Admin sistema</td><td>Modifico servicio</td><td>Servicios medicos</td><td>08:56</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="admin-panel rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900">Accesos rapidos</h2>
                <p class="mt-1 text-sm text-slate-500">Continua trabajando en un modulo</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    @foreach ([['label' => 'Agendar una cita', 'slug' => 'citas'], ['label' => 'Registrar servicio', 'slug' => 'servicios-medicos'], ['label' => 'Consultar usuarios', 'slug' => 'usuarios']] as $quick)
                        <a href="{{ route('admin.' . $quick['slug'] . '.index') }}" class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-teal-400 hover:bg-teal-50">
                            {{ $quick['label'] }} <span class="text-lg text-teal-700">→</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection
