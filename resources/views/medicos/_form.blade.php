{{--
    Formulario compartido de médicos.
    Crear:  $especialidades, $usuariosDisponibles
    Editar: $especialidades, $medico
--}}
@php
    $medico = $medico ?? null;
    $usuario = $medico?->usuario;
    $esNuevo = !$medico;
    $tipoUsuario = old('tipo_usuario', 'nuevo');
    $activo = (bool) old('activo', $usuario?->activo ?? true);
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

@if ($especialidades->isEmpty())
    <div class="mb-6 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
        No hay especialidades registradas. Agrega al menos una en la tabla <strong>especialidades</strong> antes de registrar médicos.
    </div>
@endif

{{-- Paso 1: cuenta de usuario --}}
<section class="admin-panel mb-6 rounded-xl p-6 lg:p-8">
    <div class="mb-6 border-b border-slate-200 pb-4">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Paso 1</p>
        <h2 class="mt-1 text-xl font-bold text-slate-900">Datos personales y cuenta</h2>
    </div>

    @if ($esNuevo)
        <div class="mb-6 flex flex-wrap gap-3">
            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50 has-[:checked]:text-teal-800">
                <input type="radio" name="tipo_usuario" value="nuevo" @checked($tipoUsuario === 'nuevo') data-tipo-usuario>
                Crear usuario nuevo
            </label>
            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50 has-[:checked]:text-teal-800 {{ $usuariosDisponibles->isEmpty() ? 'opacity-50' : '' }}">
                <input type="radio" name="tipo_usuario" value="existente" @checked($tipoUsuario === 'existente') @disabled($usuariosDisponibles->isEmpty()) data-tipo-usuario>
                Usar un usuario existente
            </label>
        </div>

        {{-- Usuario existente --}}
        <div data-bloque="existente" class="{{ $tipoUsuario === 'existente' ? '' : 'hidden' }}">
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-700">Usuario</span>
                <select class="admin-field" name="id_usuario">
                    <option value="">-- Selecciona un usuario --</option>
                    @foreach ($usuariosDisponibles as $u)
                        <option value="{{ $u->id_usuario }}" @selected((string) old('id_usuario') === (string) $u->id_usuario)>
                            {{ $u->nombre_completo }} ({{ $u->email }}){{ $u->activo ? '' : ' — inactivo' }}
                        </option>
                    @endforeach
                </select>
                <span class="mt-1 block text-xs text-slate-500">Solo aparecen usuarios que todavía no tienen perfil de médico.</span>
            </label>
        </div>
    @endif

    {{-- Usuario nuevo / edición de datos del usuario --}}
    <div data-bloque="nuevo" class="grid gap-5 md:grid-cols-2 {{ $esNuevo && $tipoUsuario === 'existente' ? 'hidden' : '' }}">
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Nombre(s)</span>
            <input class="admin-field" type="text" name="nombre" maxlength="100" value="{{ old('nombre', $usuario?->nombre) }}" data-requerido>
        </label>
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Apellidos</span>
            <input class="admin-field" type="text" name="apellido" maxlength="100" value="{{ old('apellido', $usuario?->apellido) }}" data-requerido>
        </label>
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</span>
            <input class="admin-field" type="email" name="email" maxlength="150" value="{{ old('email', $usuario?->email) }}" data-requerido>
        </label>
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Teléfono (opcional)</span>
            <input class="admin-field" type="tel" name="telefono" maxlength="20" value="{{ old('telefono', $usuario?->telefono) }}" placeholder="Ej. 33 1234 5678">
        </label>
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">{{ $esNuevo ? 'Contraseña' : 'Nueva contraseña (opcional)' }}</span>
            <input class="admin-field" type="password" name="password" minlength="8" autocomplete="new-password" @if ($esNuevo) data-requerido @endif placeholder="{{ $esNuevo ? 'Mínimo 8 caracteres' : 'Déjala vacía para no cambiarla' }}">
        </label>
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contraseña</span>
            <input class="admin-field" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" @if ($esNuevo) data-requerido @endif>
        </label>
        <label class="flex items-center gap-3 md:col-span-2">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" class="h-4 w-4 rounded border-slate-300 text-teal-600" @checked($activo)>
            <span class="text-sm font-semibold text-slate-700">Cuenta activa <span class="font-normal text-slate-500">(un médico inactivo no aparece para agendar nuevas citas)</span></span>
        </label>
    </div>
</section>

{{-- Paso 2: datos profesionales --}}
<section class="admin-panel rounded-xl p-6 lg:p-8">
    <div class="mb-6 border-b border-slate-200 pb-4">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-orange-600">Paso 2</p>
        <h2 class="mt-1 text-xl font-bold text-slate-900">Datos profesionales</h2>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Cédula profesional</span>
            <input class="admin-field" type="text" name="cedula" maxlength="30" value="{{ old('cedula', $medico?->cedula) }}" placeholder="Ej. MED-4587" required>
        </label>
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Especialidad</span>
            <select class="admin-field" name="especialidad_id" required>
                <option value="">-- Selecciona --</option>
                @foreach ($especialidades as $especialidad)
                    <option value="{{ $especialidad->id_especialidad }}" @selected((string) old('especialidad_id', $medico?->especialidad_id) === (string) $especialidad->id_especialidad)>{{ $especialidad->nombre }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="mb-2 block text-sm font-semibold text-slate-700">Consultorio</span>
            <input class="admin-field" type="text" name="consultorio" maxlength="100" value="{{ old('consultorio', $medico?->consultorio) }}" placeholder="Ej. Consultorio 201" required>
        </label>
    </div>
</section>

<script>
    (function () {
        const radios = document.querySelectorAll('[data-tipo-usuario]');
        const bloqueNuevo = document.querySelector('[data-bloque="nuevo"]');
        const bloqueExistente = document.querySelector('[data-bloque="existente"]');
        const esNuevo = @json($esNuevo);

        function actualizar() {
            const tipo = document.querySelector('[data-tipo-usuario]:checked')?.value ?? 'nuevo';
            const usarNuevo = !esNuevo || tipo === 'nuevo';

            bloqueNuevo.classList.toggle('hidden', !usarNuevo);
            bloqueExistente?.classList.toggle('hidden', usarNuevo);

            // Los campos ocultos no deben bloquear el envío del formulario
            bloqueNuevo.querySelectorAll('input, select').forEach(el => el.disabled = !usarNuevo);
            bloqueNuevo.querySelectorAll('[data-requerido]').forEach(el => el.required = usarNuevo);
            if (bloqueExistente) {
                const select = bloqueExistente.querySelector('select');
                select.disabled = usarNuevo;
                select.required = !usarNuevo;
            }
        }

        radios.forEach(r => r.addEventListener('change', actualizar));
        actualizar();
    })();
</script>
