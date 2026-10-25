{{-- Formulario compartido de citas. Variables: $medicos, $pacientes, $cita (opcional) --}}
@php $cita = $cita ?? null; @endphp

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
    <div class="grid gap-5 md:grid-cols-2">
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Paciente</span>
            <select class="admin-field" name="paciente_id" required>
                <option value="">-- Selecciona un paciente --</option>
                @foreach ($pacientes as $paciente)
                    <option value="{{ $paciente->id_usuario }}" @selected((string) old('paciente_id', $cita?->paciente_id) === (string) $paciente->id_usuario)>
                        {{ $paciente->nombre_completo }} ({{ $paciente->email }})
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Médico</span>
            <select class="admin-field" name="medico_id" required>
                <option value="">-- Selecciona un médico --</option>
                @foreach ($medicos as $medico)
                    <option value="{{ $medico->id_medico }}" @selected((string) old('medico_id', $cita?->medico_id) === (string) $medico->id_medico)>
                        {{ $medico->usuario?->nombre_completo ?? 'Médico #' . $medico->id_medico }}
                        — {{ $medico->especialidad->nombre ?? 'Sin especialidad' }} · {{ $medico->consultorio }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Fecha</span>
            <input class="admin-field" type="date" name="fecha"
                   value="{{ old('fecha', $cita?->fecha?->format('Y-m-d')) }}"
                   @if (!$cita) min="{{ now()->format('Y-m-d') }}" @endif required>
        </label>

        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Hora</span>
            <input class="admin-field" type="time" name="hora" step="900"
                   value="{{ old('hora', $cita?->hora_corta) }}" required>
        </label>

        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Estado</span>
            <select class="admin-field" name="estado" required>
                @foreach (\App\Models\Cita::ESTADOS as $estado)
                    <option value="{{ $estado }}" @selected(old('estado', $cita?->estado ?? 'Pendiente') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </label>

        <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-slate-700">Motivo de consulta</span>
            <textarea class="admin-field min-h-28" name="motivo" maxlength="1000" placeholder="Ej. Dolor de garganta y fiebre desde hace dos días">{{ old('motivo', $cita?->motivo) }}</textarea>
        </label>
    </div>
</section>
