@php
    $definitions = [
        'usuarios' => ['title' => 'Usuarios', 'description' => 'Administra las cuentas y datos de contacto de los usuarios.', 'fields' => [['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text'], ['name' => 'apellido', 'label' => 'Apellido', 'type' => 'text'], ['name' => 'email', 'label' => 'Correo electronico', 'type' => 'email'], ['name' => 'telefono', 'label' => 'Telefono', 'type' => 'tel'], ['name' => 'activo', 'label' => 'Estado', 'type' => 'select', 'options' => ['Activo', 'Inactivo']]], 'columns' => ['ID', 'Nombre completo', 'Correo', 'Telefono', 'Estado'], 'rows' => [['001', 'Ana Martinez', 'ana.martinez@email.com', '300 555 0182', 'Activo'], ['002', 'Carlos Rojas', 'carlos.rojas@email.com', '310 555 0144', 'Activo'], ['003', 'Laura Gomez', 'laura.gomez@email.com', '315 555 0190', 'Inactivo']]],
        'roles' => ['title' => 'Roles', 'description' => 'Define los perfiles de acceso disponibles para el sistema.', 'fields' => [['name' => 'nombre', 'label' => 'Nombre del rol', 'type' => 'text'], ['name' => 'descripcion', 'label' => 'Descripcion', 'type' => 'textarea']], 'columns' => ['ID', 'Nombre', 'Descripcion'], 'rows' => [['001', 'Administrador', 'Acceso completo al panel'], ['002', 'Medico', 'Gestion de citas y expedientes'], ['003', 'Paciente', 'Consulta de servicios y citas']]],
        'especialidades' => ['title' => 'Especialidades', 'description' => 'Catalogo de especialidades medicas ofrecidas.', 'fields' => [['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text'], ['name' => 'descripcion', 'label' => 'Descripcion', 'type' => 'textarea']], 'columns' => ['ID', 'Nombre', 'Descripcion'], 'rows' => [['001', 'Medicina general', 'Atencion primaria'], ['002', 'Pediatria', 'Atencion integral para ninos'], ['003', 'Dermatologia', 'Cuidado de la piel']]],
        'sesiones-sociales' => ['title' => 'Sesiones sociales', 'description' => 'Consulta las cuentas externas vinculadas a usuarios.', 'fields' => [['name' => 'id_usuario', 'label' => 'Usuario', 'type' => 'number'], ['name' => 'proveedor', 'label' => 'Proveedor', 'type' => 'select', 'options' => ['Google', 'Facebook', 'Apple']], ['name' => 'provider_id', 'label' => 'Identificador del proveedor', 'type' => 'text']], 'columns' => ['ID', 'Usuario', 'Proveedor', 'Fecha de vinculacion'], 'rows' => [['001', 'Ana Martinez', 'Google', '05/09/2026'], ['002', 'Carlos Rojas', 'Apple', '04/09/2026']]],
        'servicios-medicos' => ['title' => 'Servicios medicos', 'description' => 'Administra el catalogo de servicios y sus costos.', 'fields' => [['name' => 'nombre', 'label' => 'Nombre del servicio', 'type' => 'text'], ['name' => 'descripcion', 'label' => 'Descripcion', 'type' => 'textarea'], ['name' => 'costo', 'label' => 'Costo', 'type' => 'number'], ['name' => 'activo', 'label' => 'Estado', 'type' => 'select', 'options' => ['Activo', 'Inactivo']]], 'columns' => ['ID', 'Servicio', 'Descripcion', 'Costo', 'Estado'], 'rows' => [['001', 'Consulta general', 'Valoracion medica inicial', '$ 80.000', 'Activo'], ['002', 'Control pediatrico', 'Seguimiento de pacientes', '$ 65.000', 'Activo'], ['003', 'Teleconsulta', 'Atencion remota', '$ 45.000', 'Inactivo']]],
        'carritos' => ['title' => 'Carritos', 'description' => 'Consulta el estado de los carritos de compra.', 'fields' => [['name' => 'id_usuario', 'label' => 'Usuario', 'type' => 'number'], ['name' => 'fecha', 'label' => 'Fecha', 'type' => 'date'], ['name' => 'estado', 'label' => 'Estado', 'type' => 'select', 'options' => ['Activo', 'Completado', 'Abandonado']]], 'columns' => ['ID', 'Usuario', 'Fecha', 'Estado'], 'rows' => [['001', 'Ana Martinez', '06/09/2026', 'Activo'], ['002', 'Carlos Rojas', '05/09/2026', 'Completado']]],
        'historial-transacciones' => ['title' => 'Historial de transacciones', 'description' => 'Visualiza las compras y su estado de procesamiento.', 'fields' => [['name' => 'id_usuario', 'label' => 'Usuario', 'type' => 'number'], ['name' => 'id_carrito', 'label' => 'Carrito', 'type' => 'number'], ['name' => 'total', 'label' => 'Total', 'type' => 'number'], ['name' => 'estado', 'label' => 'Estado', 'type' => 'select', 'options' => ['Pagada', 'Pendiente', 'Reembolsada']]], 'columns' => ['ID', 'Usuario', 'Total', 'Estado', 'Fecha'], 'rows' => [['001', 'Ana Martinez', '$ 145.000', 'Pagada', '06/09/2026'], ['002', 'Carlos Rojas', '$ 80.000', 'Pendiente', '05/09/2026']]],
        'logs' => ['title' => 'Logs del sistema', 'description' => 'Audita las acciones realizadas por los usuarios.', 'fields' => [['name' => 'id_usuario', 'label' => 'Usuario', 'type' => 'number'], ['name' => 'accion', 'label' => 'Accion', 'type' => 'text'], ['name' => 'modulo', 'label' => 'Modulo', 'type' => 'text'], ['name' => 'ip', 'label' => 'Direccion IP', 'type' => 'text']], 'columns' => ['ID', 'Usuario', 'Accion', 'Modulo', 'IP', 'Fecha'], 'rows' => [['001', 'Ana Martinez', 'Actualizo registro', 'Usuarios', '192.168.1.20', '06/09/2026 09:42'], ['002', 'Admin sistema', 'Creo registro', 'Citas', '192.168.1.10', '06/09/2026 09:18']]],
    ];

    $config = $definitions[$module];
    $isCreate = $mode === 'create';
@endphp
@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Resumen</a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $isCreate ? 'Nuevo registro' : $config['title'] }}</h1>
                <p class="mt-2 text-slate-500">{{ $config['description'] }}</p>
            </div>
            @if (!$isCreate)
                <a href="{{ route('admin.' . $module . '.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">+ Nuevo registro</a>
            @endif
        </div>

        @if ($isCreate)
            <section class="admin-panel rounded-xl p-6 lg:p-8">
                <div class="mb-7 border-b border-slate-200 pb-5">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Formulario estatico</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-900">Informacion del registro</h2>
                    <p class="mt-1 text-sm text-slate-500">Completa los campos de referencia visual. El formulario aun no guarda informacion.</p>
                </div>
                <form class="grid gap-5 md:grid-cols-2" onsubmit="return false;">
                    @foreach ($config['fields'] as $field)
                        <label class="{{ $field['type'] === 'textarea' ? 'md:col-span-2' : '' }}">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">{{ $field['label'] }}</span>
                            @if ($field['type'] === 'textarea')
                                <textarea class="admin-field min-h-28" name="{{ $field['name'] }}" placeholder="Escribe {{ strtolower($field['label']) }}..."></textarea>
                            @elseif ($field['type'] === 'select')
                                <select class="admin-field" name="{{ $field['name'] }}">
                                    <option value="">Selecciona una opcion</option>
                                    @foreach ($field['options'] as $option)<option>{{ $option }}</option>@endforeach
                                </select>
                            @else
                                <input class="admin-field" type="{{ $field['type'] }}" name="{{ $field['name'] }}" placeholder="Ingresa {{ strtolower($field['label']) }}">
                            @endif
                        </label>
                    @endforeach
                    <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-6 md:col-span-2">
                        <button type="button" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Guardar registro</button>
                        <a href="{{ route('admin.' . $module . '.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
                    </div>
                </form>
            </section>
        @else
            <section class="admin-panel overflow-hidden rounded-xl">
                <div class="flex flex-col gap-4 border-b border-slate-200 p-5 md:flex-row md:items-center md:justify-between">
                    <div class="relative w-full md:max-w-xs"><span class="absolute left-3 top-2.5 text-slate-400">⌕</span><input class="admin-field pl-9" type="search" placeholder="Buscar registro..." aria-label="Buscar registro"></div>
                    <span class="text-sm text-slate-500">{{ count($config['rows']) }} registros de ejemplo</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="admin-table w-full min-w-[720px] text-sm">
                        <thead><tr>@foreach ($config['columns'] as $column)<th>{{ $column }}</th>@endforeach<th>Acciones</th></tr></thead>
                        <tbody class="text-slate-600">
                            @foreach ($config['rows'] as $row)
                                <tr class="transition hover:bg-slate-50">
                                    @foreach ($row as $value)<td class="{{ $loop->first ? 'font-semibold text-slate-800' : '' }}">{{ $value }}</td>@endforeach
                                    <td><div class="flex gap-2"><button type="button" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-teal-400 hover:text-teal-700">Ver</button><button type="button" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-orange-400 hover:text-orange-700">Editar</button></div></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 p-4 text-sm text-slate-500"><span>Mostrando datos estaticos</span><div class="flex gap-2"><button type="button" class="rounded-md border border-slate-200 px-3 py-1.5">Anterior</button><button type="button" class="rounded-md bg-teal-700 px-3 py-1.5 text-white">1</button><button type="button" class="rounded-md border border-slate-200 px-3 py-1.5">Siguiente</button></div></div>
            </section>
        @endif
    </div>
@endsection
