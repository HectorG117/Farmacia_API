{{--
    Formulario compartido para crear y editar recetas.
    Variables: $pacientes, $productos, $receta (opcional)
               $medicos (panel admin) o $medicoFijo (portal médico)
--}}
@php
    $receta = $receta ?? null;

    $vacio = ['producto_id' => '', 'cantidad' => 1, 'dosis' => '', 'frecuencia' => '', 'duracion' => '', 'instrucciones' => ''];

    $filas = old('productos', $receta
        ? $receta->detalles->map(fn ($d) => $d->only(['producto_id', 'cantidad', 'dosis', 'frecuencia', 'duracion', 'instrucciones']))->all()
        : [$vacio]);

    $fechaEmision = old('fecha_emision', $receta?->fecha_emision?->format('Y-m-d') ?? now()->format('Y-m-d'));
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold">Por favor corrige los siguientes errores:</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Datos generales --}}
<section class="admin-panel mb-6 rounded-xl p-6 lg:p-8">
    <div class="mb-6 border-b border-slate-200 pb-4">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Paso 1</p>
        <h2 class="mt-1 text-xl font-bold text-slate-900">Médico y paciente</h2>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        @if (isset($medicoFijo))
            {{-- Portal médico: la receta siempre la emite el médico que inició sesión --}}
            <div>
                <span class="mb-2 block text-sm font-semibold text-slate-700">Médico que emite la receta</span>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">
                    {{ $medicoFijo->nombre_completo }} — {{ $medicoFijo->especialidad->nombre ?? 'Sin especialidad' }} (Céd. {{ $medicoFijo->cedula }})
                </div>
            </div>
        @else
            <label>
                <span class="mb-2 block text-sm font-semibold text-slate-700">Médico que emite la receta</span>
                <select class="admin-field" name="medico_id" required>
                    <option value="">-- Selecciona un médico --</option>
                    @foreach ($medicos as $medico)
                        <option value="{{ $medico->id_medico }}" @selected((string) old('medico_id', $receta?->medico_id ?? request('medico_id')) === (string) $medico->id_medico)>
                            {{ $medico->usuario?->nombre_completo ?? 'Médico #' . $medico->id_medico }}
                            — {{ $medico->especialidad->nombre ?? 'Sin especialidad' }} (Céd. {{ $medico->cedula }})
                        </option>
                    @endforeach
                </select>
            </label>
        @endif

        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Paciente</span>
            <select class="admin-field" name="paciente_id" required>
                <option value="">-- Selecciona un paciente --</option>
                @foreach ($pacientes as $paciente)
                    <option value="{{ $paciente->id_usuario }}" @selected((string) old('paciente_id', $receta?->paciente_id ?? request('paciente_id')) === (string) $paciente->id_usuario)>
                        {{ $paciente->nombre_completo }} ({{ $paciente->email }})
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Fecha de emisión</span>
            <input class="admin-field" type="date" name="fecha_emision" value="{{ $fechaEmision }}" max="{{ now()->format('Y-m-d') }}" required>
        </label>

        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Estado</span>
            <select class="admin-field" name="estado" required>
                @foreach (\App\Models\Receta::ESTADOS as $estado)
                    <option value="{{ $estado }}" @selected(old('estado', $receta?->estado ?? 'Vigente') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </label>

        <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-slate-700">Diagnóstico</span>
            <input class="admin-field" type="text" name="diagnostico" maxlength="255" value="{{ old('diagnostico', $receta?->diagnostico ?? ($diagnosticoSugerido ?? request('diagnostico'))) }}" placeholder="Ej. Infección de vías respiratorias">
        </label>

        <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-slate-700">Indicaciones generales del médico</span>
            <textarea class="admin-field min-h-28" name="indicaciones_generales" maxlength="2000" placeholder="Ej. Reposo relativo, abundantes líquidos, regresar a consulta si persiste la fiebre...">{{ old('indicaciones_generales', $receta?->indicaciones_generales) }}</textarea>
        </label>
    </div>
</section>

{{-- Productos recetados --}}
<section class="admin-panel rounded-xl p-6 lg:p-8">
    <div class="mb-6 flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 md:flex-row md:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Paso 2</p>
            <h2 class="mt-1 text-xl font-bold text-slate-900">Productos e instrucciones de uso</h2>
            <p class="mt-1 text-sm text-slate-500">Agrega cada medicamento o producto con la forma en que el paciente debe usarlo.</p>
        </div>
        <button type="button" id="btn-agregar-producto" class="inline-flex items-center justify-center rounded-lg border border-teal-600 px-4 py-2 text-sm font-semibold text-teal-700 transition hover:bg-teal-50">+ Agregar producto</button>
    </div>

    <div id="lista-productos" class="space-y-4">
        @foreach ($filas as $i => $fila)
            @include('recetas._fila_producto', ['i' => $i, 'fila' => $fila, 'productos' => $productos])
        @endforeach
    </div>

    <div class="mt-6 flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3 text-sm">
        <span class="text-slate-500">Total estimado</span>
        <span id="total-estimado" class="text-lg font-bold text-teal-700">$0.00</span>
    </div>
</section>

{{-- Plantilla de una fila vacía (se clona con JavaScript) --}}
<template id="plantilla-producto">
    @include('recetas._fila_producto', ['i' => '__INDEX__', 'fila' => $vacio, 'productos' => $productos])
</template>

<script>
    (function () {
        const lista = document.getElementById('lista-productos');
        const plantilla = document.getElementById('plantilla-producto');
        const total = document.getElementById('total-estimado');
        // Siguiente índice libre (las filas pueden venir con índices salteados tras un error de validación)
        let siguiente = {{ count($filas) ? max(array_map('intval', array_keys($filas))) + 1 : 0 }};

        function recalcular() {
            let suma = 0;
            const filas = lista.querySelectorAll('[data-fila]');
            filas.forEach((fila, n) => {
                const opcion = fila.querySelector('select').selectedOptions[0];
                const precio = parseFloat(opcion?.dataset.precio || 0);
                const cantidad = parseInt(fila.querySelector('[data-cantidad]').value || 0, 10);
                const subtotal = precio * cantidad;
                suma += subtotal;
                fila.querySelector('[data-subtotal]').textContent = '$' + subtotal.toFixed(2);
                fila.querySelector('[data-numero]').textContent = n + 1;
                // No se permite quitar la última fila: la receta necesita al menos un producto
                fila.querySelector('[data-quitar]').disabled = filas.length === 1;
            });
            total.textContent = '$' + suma.toFixed(2);
        }

        document.getElementById('btn-agregar-producto').addEventListener('click', () => {
            const html = plantilla.innerHTML.replaceAll('__INDEX__', siguiente++);
            lista.insertAdjacentHTML('beforeend', html);
            recalcular();
            lista.lastElementChild.querySelector('select').focus();
        });

        lista.addEventListener('click', (e) => {
            const boton = e.target.closest('[data-quitar]');
            if (boton && !boton.disabled) {
                boton.closest('[data-fila]').remove();
                recalcular();
            }
        });

        lista.addEventListener('input', recalcular);
        lista.addEventListener('change', recalcular);
        recalcular();
    })();
</script>
