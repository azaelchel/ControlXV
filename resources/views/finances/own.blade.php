@extends('layouts.app')

@section('title', 'Mis aportaciones')
@section('heading', 'Mis aportaciones')
@section('subheading', 'El dinero que tú o la familia ponen para el evento (aparte de los padrinos).')

@section('content')
@php $m = fn ($v) => '$' . number_format((float) $v, 2); @endphp
@include('finances._assets')

    <div class="card ministrip" style="margin-bottom:16px;">
        <div class="it">Total aportado por mí <b class="money" style="color:#3f9e6b;">{{ $m($totals['own']) }}</b></div>
        <div class="it">Reunido en total <b class="money">{{ $m($totals['gathered']) }}</b></div>
        <div class="it">Falta por reunir <b class="money" style="color:#c0392b;">{{ $m($totals['to_gather']) }}</b></div>
    </div>

    <div class="ftoolbar">
        <div class="filters">
            <input type="search" class="search" placeholder="🔍 Buscar concepto…" data-table-filter="own-table">
        </div>
        <button class="btn" type="button" data-modal-open="new-own">＋ Nueva aportación</button>
    </div>

    <div class="ftable-wrap">
        <table class="ftable" id="own-table">
            <thead>
                <tr>
                    <th data-sort="num">Fecha</th>
                    <th data-sort="num" class="num">Monto</th>
                    <th data-sort="text">Concepto</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contributions as $c)
                    <tr>
                        <td data-val="{{ $c->contributed_on?->format('Y-m-d') ?? '' }}">{{ $c->contributed_on?->format('d/m/Y') ?? '—' }}</td>
                        <td class="num money" data-val="{{ $c->amount }}" style="color:#3f9e6b;">{{ $m($c->amount) }}</td>
                        <td>{{ $c->concept ?: '—' }}</td>
                        <td>
                            <div class="rowact">
                                <form method="post" action="{{ route('finances.own.destroy', $c) }}"
                                    data-confirm-title="¿Eliminar esta aportación?" data-confirm-text="Se descontará del total aportado." data-confirm-button="Sí, eliminar" data-confirm-color="#d8527f">
                                    @csrf @method('delete')
                                    <button class="btn small danger" type="submit">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="ftable-empty">Aún no has registrado aportaciones. Agrega la primera con “＋ Nueva aportación”.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal nueva aportación --}}
    <div class="modal" id="new-own">
        <div class="modal-box">
            <div class="modal-head"><h3>Nueva aportación mía</h3><button class="modal-x" type="button" data-modal-close>&times;</button></div>
            <form method="post" action="{{ route('finances.own.store') }}">
                @csrf
                <div class="grid2">
                    <div class="field"><label>Monto</label><input name="amount" type="number" step="0.01" min="0.01" required></div>
                    <div class="field"><label>Fecha</label><input name="contributed_on" type="date" value="{{ now()->toDateString() }}"></div>
                </div>
                <div class="field"><label>Concepto (opcional)</label><input name="concept" placeholder="Ej. Anticipo salón, ahorro…"></div>
                <div class="modal-actions">
                    <button class="btn secondary" type="button" data-modal-close>Cancelar</button>
                    <button class="btn" type="submit">Registrar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
