{{--
    Formulario compartido de expedientes.
    Crear:  $citas, $citaSeleccionada
    Editar: $expediente (la cita ya no se puede cambiar)
--}}
@php
    $expediente = $expediente ?? null;
    $fecha = old('fecha', ($expediente?->fecha ?? now())->format('Y-m-d\TH:i'));
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

<section class="admin-panel rounded-xl p-6 lg:p-8">
    <div class="grid gap-5 md:grid-cols-[2fr_1fr]">
        @if ($expediente)
            <div>
                <span class="mb-2 block text-sm font-semibold text-slate-700">Cita</span>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">
                    {{ $expediente->cita?->etiqueta ?? 'Cita eliminada' }}
                    <span class="text-slate-500">· {{ $expediente->cita?->medico?->usuario?->nombre_completo }}</span>
                </div>
            </div>
        @else
            <label>
                <span class="mb-2 block text-sm font-semibold text-slate-700">Cita atendida</span>
                <select class="admin-field" name="id_cita" required>
                    <option value="">-- Selecciona la cita --</option>
                    @foreach ($citas as $cita)
                        <option value="{{ $cita->id_cita }}" @selected((string) old('id_cita', $citaSeleccionada) === (string) $cita->id_cita)>
                            {{ $cita->etiqueta }} — {{ $cita->medico?->usuario?->nombre_completo ?? 'Médico' }} ({{ $cita->estado }})
                        </option>
                    @endforeach
                </select>
                <span class="mt-1 block text-xs text-slate-500">Solo aparecen citas no canceladas que aún no tienen expediente. Al guardar, la cita se marcará como "Completada".</span>
            </label>
        @endif

        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Fecha y hora de atención</span>
            <input class="admin-field" type="datetime-local" name="fecha" value="{{ $fecha }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
        </label>

        <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-slate-700">Diagnóstico</span>
            <textarea class="admin-field min-h-28" name="diagnostico" minlength="3" maxlength="2000" required placeholder="Ej. Faringitis aguda de origen bacteriano">{{ old('diagnostico', $expediente?->diagnostico) }}</textarea>
        </label>

        <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-slate-700">Tratamiento</span>
            <textarea class="admin-field min-h-28" name="tratamiento" minlength="3" maxlength="2000" required placeholder="Ej. Amoxicilina 500 mg cada 8 horas por 7 días, paracetamol en caso de fiebre">{{ old('tratamiento', $expediente?->tratamiento) }}</textarea>
        </label>

        <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-slate-700">Observaciones (opcional)</span>
            <textarea class="admin-field min-h-24" name="observaciones" maxlength="2000" placeholder="Ej. Signos vitales, alergias reportadas, próxima revisión...">{{ old('observaciones', $expediente?->observaciones) }}</textarea>
        </label>
    </div>
</section>
