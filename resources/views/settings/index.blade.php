@extends('layouts.app')

@section('title', 'Configuración')
@section('heading', 'Configuración')
@section('subheading', 'Valores que se usan en plantillas de mensajes y generación de enlaces')

@section('content')
    <form method="post" action="{{ route('settings.update') }}">
        @csrf
        @method('put')

        @foreach ($groups as $groupKey => $items)
            <div class="card" style="margin-bottom: 18px;">
                <div class="section-kicker">{{ $groupLabels[$groupKey] ?? ucfirst($groupKey) }}</div>
                <div class="form-grid" style="margin-top: 14px;">
                    @foreach ($items as $setting)
                        <div class="full">
                            <label for="setting-{{ $setting->key }}">{{ $setting->label }}</label>
                            <input
                                type="{{ $setting->type === 'number' ? 'number' : 'text' }}"
                                id="setting-{{ $setting->key }}"
                                name="settings[{{ $setting->key }}]"
                                value="{{ old("settings.$setting->key", $setting->value) }}"
                                @if ($setting->type === 'number') min="1" @endif>
                            @if ($setting->helper_text)
                                <div class="small" style="margin-top: 4px;">{{ $setting->helper_text }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="card">
            <button class="btn" type="submit">Guardar configuración</button>
        </div>
    </form>
@endsection
