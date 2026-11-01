@extends('cliente.layout')

@section('title', 'Elegir productos')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-8">
            <a href="{{ route('cliente.recetas.show', $receta->id_receta) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← Volver a la receta</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Elige qué comprar</h1>
            <p class="mt-2 text-slate-500">
                Receta #{{ str_pad($receta->id_receta, 4, '0', STR_PAD_LEFT) }} de {{ $receta->medico?->nombre_completo ?? 'tu médico' }}.
                Marca los productos que quieres comprar y la cantidad (hasta lo que indicó tu médico).
            </p>
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Revisa tu selección:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('cliente.recetas.update', $receta->id_receta) }}" method="POST">
            @csrf
            @method('PUT')

            <section class="admin-panel overflow-hidden rounded-xl">
                <div class="divide-y divide-slate-200" id="lista-compra">
                    @foreach ($receta->detalles as $detalle)
                        @php
                            $producto = $detalle->producto;
                            $maximo = $detalle->maximo_compra;
                            $id = $detalle->id_detalle;
                            $marcado = (bool) old("detalles.$id.comprar", $detalle->comprar);
                            $cantidad = old("detalles.$id.cantidad_compra", $detalle->comprar ? $detalle->cantidad_compra : min($detalle->cantidad, max($maximo, 1)));
                        @endphp
                        <div data-fila class="flex flex-col gap-4 p-5 md:flex-row md:items-center {{ $maximo < 1 ? 'opacity-60' : '' }}">
                            <label class="flex flex-1 cursor-pointer items-start gap-4">
                                <input type="hidden" name="detalles[{{ $id }}][comprar]" value="0">
                                <input type="checkbox" name="detalles[{{ $id }}][comprar]" value="1" data-comprar
                                       class="mt-1 h-5 w-5 rounded border-slate-300 text-teal-600"
                                       @checked($marcado && $maximo > 0) @disabled($maximo < 1)>
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-slate-50">
                                    @if ($producto?->imagen)
                                        <img src="{{ asset($producto->imagen) }}" alt="{{ $producto->nombre }}" class="max-h-14 max-w-full object-contain">
                                    @else
                                        <span class="text-xs text-slate-400">Sin imagen</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $producto->nombre ?? 'Producto' }}</p>
                                    <p class="text-sm text-slate-500">${{ number_format($producto->precio ?? 0, 2) }} c/u · Recetado: {{ $detalle->cantidad }}</p>
                                    <p class="text-xs text-slate-500">{{ $detalle->dosis }} · {{ $detalle->frecuencia }} · {{ $detalle->duracion }}</p>
                                    @if ($maximo < 1)
                                        <p class="mt-1 text-xs font-semibold text-red-600">No disponible por el momento</p>
                                    @elseif ($maximo < $detalle->cantidad)
                                        <p class="mt-1 text-xs font-semibold text-orange-600">Solo hay {{ $maximo }} en existencia</p>
                                    @endif
                                </div>
                            </label>
                            <div class="flex items-center gap-4 md:w-64 md:justify-end">
                                <label class="text-sm">
                                    <span class="mb-1 block text-xs font-semibold text-slate-600">Cantidad</span>
                                    <input type="number" class="admin-field w-24" name="detalles[{{ $id }}][cantidad_compra]" data-cantidad
                                           data-precio="{{ $producto->precio ?? 0 }}" min="1" max="{{ max($maximo, 1) }}"
                                           value="{{ $cantidad }}" @disabled($maximo < 1)>
                                </label>
                                <div class="text-right">
                                    <span class="block text-xs text-slate-500">Subtotal</span>
                                    <strong data-subtotal class="text-slate-800">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-5 py-4">
                    <span class="text-sm text-slate-500">Total de tu selección</span>
                    <span id="total-compra" class="text-xl font-bold text-teal-700">$0.00</span>
                </div>
            </section>

            <p class="mt-4 text-xs text-slate-500">Las indicaciones de tu médico (dosis, frecuencia y duración) no cambian. Esta selección solo registra lo que deseas comprar.</p>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">Guardar selección</button>
                <a href="{{ route('cliente.recetas.show', $receta->id_receta) }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const filas = document.querySelectorAll('[data-fila]');
            const total = document.getElementById('total-compra');

            function recalcular() {
                let suma = 0;
                filas.forEach(fila => {
                    const check = fila.querySelector('[data-comprar]');
                    const input = fila.querySelector('[data-cantidad]');
                    const activo = check.checked && !check.disabled;
                    input.readOnly = !activo;
                    input.classList.toggle('opacity-50', !activo);
                    const subtotal = activo ? parseFloat(input.dataset.precio) * (parseInt(input.value, 10) || 0) : 0;
                    fila.querySelector('[data-subtotal]').textContent = '$' + subtotal.toFixed(2);
                    suma += subtotal;
                });
                total.textContent = '$' + suma.toFixed(2);
            }

            document.getElementById('lista-compra').addEventListener('input', recalcular);
            document.getElementById('lista-compra').addEventListener('change', recalcular);
            recalcular();
        })();
    </script>
@endsection
