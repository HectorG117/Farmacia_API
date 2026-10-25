@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-8">
            <a href="{{ route('admin.' . $module . '.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">← {{ $definition['title'] }}</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $record ? 'Editar registro' : 'Nuevo registro' }}</h1>
        </div>

        <section class="admin-panel rounded-xl p-6 lg:p-8">
            <form method="POST" action="{{ $record ? route('admin.' . $module . '.update', $record->getKey()) : route('admin.' . $module . '.store') }}" class="grid gap-5 md:grid-cols-2">
                @csrf
                @if ($record) @method('PUT') @endif

                @foreach ($definition['fields'] as $field)
                    @php
                        $value = old($field['name'], $record?->getAttribute($field['name']));
                        if ($field['type'] === 'datetime-local' && $value) {
                            $value = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i');
                        }
                    @endphp
                    <label class="{{ $field['type'] === 'password' ? 'md:col-span-2' : '' }}">
                        <span class="mb-2 block text-sm font-semibold text-slate-700">{{ $field['label'] }}</span>
                        @if ($field['type'] === 'select')
                            <select class="admin-field" name="{{ $field['name'] }}" required>
                                <option value="">Selecciona una opcion</option>
                                @foreach ($options[$field['name']] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        @else
                            <input class="admin-field" type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ $field['type'] === 'password' ? '' : $value }}" @if (in_array('required', $field['rules'], true) && ($field['type'] !== 'password' || !$record)) required @endif @isset($field['step']) step="{{ $field['step'] }}" @endisset @if ($field['type'] === 'number') min="0" @endif placeholder="{{ $field['type'] === 'password' && $record ? 'Deja vacio para conservar la actual' : '' }}">
                        @endif
                        @error($field['name'])<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                    </label>
                @endforeach

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 md:col-span-2">Revisa los campos marcados e intenta de nuevo.</div>
                @endif
                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-6 md:col-span-2">
                    <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">Guardar</button>
                    <a href="{{ route('admin.' . $module . '.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a>
                </div>
            </form>
        </section>
    </div>
@endsection