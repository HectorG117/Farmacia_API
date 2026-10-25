{{-- Una fila de producto dentro del formulario de receta. Variables: $i, $fila, $productos --}}
@php $err = fn ($campo) => $errors->has("productos.$i.$campo") ? 'border-red-400' : ''; @endphp
<div data-fila class="rounded-lg border border-slate-200 p-4">
    <div class="mb-3 flex items-center justify-between">
        <span class="text-sm font-bold text-slate-700">Producto <span data-numero>{{ is_numeric($i) ? $i + 1 : '' }}</span></span>
        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-500">Subtotal: <strong data-subtotal class="text-slate-800">$0.00</strong></span>
            <button type="button" data-quitar class="rounded-md border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40">Quitar</button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <label class="md:col-span-3">
            <span class="mb-1 block text-xs font-semibold text-slate-600">Producto</span>
            <select class="admin-field {{ $err('producto_id') }}" name="productos[{{ $i }}][producto_id]" required>
                <option value="" data-precio="0">-- Selecciona un producto --</option>
                @foreach ($productos as $producto)
                    <option value="{{ $producto->id }}" data-precio="{{ $producto->precio }}" @selected((string) ($fila['producto_id'] ?? '') === (string) $producto->id)>
                        {{ $producto->nombre }} — ${{ number_format($producto->precio, 2) }}
                        ({{ $producto->trashed() ? 'descontinuado' : 'stock: ' . $producto->stock }})
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span class="mb-1 block text-xs font-semibold text-slate-600">Cantidad</span>
            <input data-cantidad class="admin-field {{ $err('cantidad') }}" type="number" min="1" max="99" name="productos[{{ $i }}][cantidad]" value="{{ $fila['cantidad'] ?? 1 }}" required>
        </label>

        <label>
            <span class="mb-1 block text-xs font-semibold text-slate-600">Dosis</span>
            <input class="admin-field {{ $err('dosis') }}" type="text" maxlength="100" name="productos[{{ $i }}][dosis]" value="{{ $fila['dosis'] ?? '' }}" placeholder="Ej. 1 tableta de 500 mg" required>
        </label>

        <label>
            <span class="mb-1 block text-xs font-semibold text-slate-600">Frecuencia</span>
            <input class="admin-field {{ $err('frecuencia') }}" type="text" maxlength="100" name="productos[{{ $i }}][frecuencia]" value="{{ $fila['frecuencia'] ?? '' }}" placeholder="Ej. Cada 8 horas" required>
        </label>

        <label class="md:col-span-2">
            <span class="mb-1 block text-xs font-semibold text-slate-600">Duración del tratamiento</span>
            <input class="admin-field {{ $err('duracion') }}" type="text" maxlength="100" name="productos[{{ $i }}][duracion]" value="{{ $fila['duracion'] ?? '' }}" placeholder="Ej. Durante 5 días" required>
        </label>

        <label class="md:col-span-4">
            <span class="mb-1 block text-xs font-semibold text-slate-600">Instrucciones de uso (opcional)</span>
            <textarea class="admin-field {{ $err('instrucciones') }}" rows="2" maxlength="500" name="productos[{{ $i }}][instrucciones]" placeholder="Ej. Tomar después de los alimentos con un vaso de agua">{{ $fila['instrucciones'] ?? '' }}</textarea>
        </label>
    </div>
</div>
