@extends('layouts.app')

@section('title', 'Catálogos')
@section('heading', 'Catálogos')
@section('subheading', 'Alta, edición y borrado de listas maestras por tipo de catálogo')

@section('content')
    <div class="grid" style="grid-template-columns: 300px minmax(0, 1fr); align-items: start;">
        <div class="card">
            <div class="section-kicker">Tipos de catálogo</div>
            <div class="subnav">
                @foreach ($catalogs as $catalog)
                    <a class="subnav-link {{ $activeType === $catalog['type'] ? 'active' : '' }}" href="{{ route('catalogs.index', ['type' => $catalog['type']]) }}">
                        <span>{{ $catalog['label'] }}</span>
                        <span class="subnav-count">{{ $catalog['items']->count() }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        @foreach ($catalogs as $catalog)
            @if ($activeType === $catalog['type'])
                <div class="card">
                    <div class="section-kicker">Catálogo activo</div>
                    <div class="inline" style="justify-content: space-between; margin-bottom: 18px;">
                        <div>
                            <h3 class="section-title">{{ $catalog['label'] }}</h3>
                            <div class="small">{{ $catalog['items']->count() }} registros en este bloque</div>
                        </div>
                    </div>

                    <form method="post" action="{{ route('catalogs.store') }}" class="inline" style="margin-bottom: 18px; align-items: end;">
                        @csrf
                        <input type="hidden" name="type" value="{{ $catalog['type'] }}">
                        <div style="flex: 1 1 360px;">
                            <label>Nuevo valor</label>
                            <input type="text" name="value" placeholder="Agregar nuevo valor a {{ strtolower($catalog['label']) }}">
                        </div>
                        <button class="btn" type="submit">Agregar</button>
                    </form>

                    <div class="table-wrap">
                        <table style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th>Valor</th>
                                    <th style="width: 210px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($catalog['items'] as $item)
                                    <tr>
                                        <td>
                                            <form method="post" action="{{ route('catalogs.update', $item) }}" class="inline" style="align-items: end;">
                                                @csrf
                                                @method('put')
                                                <div style="flex: 1 1 auto;">
                                                    <input type="text" name="value" value="{{ $item->value }}">
                                                </div>
                                                <button class="btn secondary" type="submit">Guardar</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="post" action="{{ route('catalogs.destroy', $item) }}"
                                                data-confirm-title="¿Desactivar este valor del catálogo?"
                                                data-confirm-text="Si ya lo usas en registros existentes, conviene revisarlo antes."
                                                data-confirm-button="Sí, desactivar"
                                                data-confirm-color="#d8527f"
                                                data-confirm-icon="warning">
                                                @csrf
                                                @method('delete')
                                                <button class="btn danger" type="submit">Desactivar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="empty">Sin datos todavía.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endsection
